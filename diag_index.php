<?php
// Jalankan di root project:  php diag_index.php karawang
// (aman: read-only, hanya SELECT / EXPLAIN)
$base = getcwd();
require $base.'/vendor/autoload.php';
$app = require_once $base.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$plantName = $argv[1] ?? 'karawang';
$plant = \App\Models\Plant::resolveId($plantName);
$T = 'in_process_checksheets';
function t(callable $f){ $s=microtime(true); $r=$f(); return [round((microtime(true)-$s)*1000), $r]; }

echo "== ENV ==\n";
echo "cache=".config('cache.default')." session=".config('session.driver')." db_host=".config('database.connections.mysql.host')." mysql=".DB::selectOne('select version() v')->v."\n";
[$ping] = t(fn()=>DB::select('select 1')); echo "ping 1 query: {$ping} ms\n";

echo "\n== UKURAN TABEL ==\n";
$s = DB::selectOne("select table_rows r, round(data_length/1048576) data_mb, round(index_length/1048576) idx_mb from information_schema.tables where table_schema=database() and table_name=?", [$T]);
echo "rows~{$s->r} data={$s->data_mb}MB index={$s->idx_mb}MB\n";
echo "plant rows: ".DB::table($T)->where('plant_id',$plant)->count()." | manual: ".DB::table($T)->where('plant_id',$plant)->where('scan_method','manual')->count()."\n";
echo "avg bytes dimension_check/defects: ".round(DB::table($T)->where('plant_id',$plant)->avg(DB::raw('LENGTH(dimension_check)'))).' / '.round(DB::table($T)->where('plant_id',$plant)->avg(DB::raw('LENGTH(defects)')))."\n";

echo "\n== WAKTU QUERY UTAMA (tanpa cache) ==\n";
$list = "select * from $T where plant_id = ? and scan_method = 'manual' order by `date` desc, created_at desc limit 10";
$cnt  = "select count(*) c from $T where plant_id = ? and scan_method = 'manual'";
[$ms]=t(fn()=>DB::select($cnt,[$plant]));  echo "count(*)            : {$ms} ms\n";
[$ms]=t(fn()=>DB::select($list,[$plant])); echo "list (optimizer)    : {$ms} ms\n";
foreach (['idx_inproc_plant_scan_date','idx_inproc_plant_date_created','idx_inproc_plant_scan_method'] as $ix) {
  try { [$ms]=t(fn()=>DB::select(str_replace("from $T","from $T force index ($ix)",$list),[$plant])); echo "list FORCE $ix : {$ms} ms\n"; } catch(\Throwable $e){ echo "list FORCE $ix : (index tidak ada)\n"; }
}
echo "\nEXPLAIN list: "; foreach (DB::select("explain $list",[$plant]) as $r) echo "key={$r->key} rows={$r->rows} extra={$r->Extra}\n";

echo "\n== QUERY FILTER (yang di-cache) ==\n";
[$ms]=t(fn()=>DB::select("select i.id from items i where exists (select 1 from $T c where c.item_id=i.id and c.plant_id=?)",[$plant])); echo "items whereExists   : {$ms} ms\n";
[$ms]=t(fn()=>DB::select("select distinct code_machine from $T where plant_id=? and code_machine is not null and code_machine!=''",[$plant])); echo "distinct machine    : {$ms} ms\n";
[$ms]=t(fn()=>DB::select("select distinct operator_initials from $T where plant_id=? and `date`>=? and operator_initials is not null and operator_initials!=''",[$plant,now()->subDays(90)])); echo "distinct initials   : {$ms} ms\n";
[$ms]=t(fn()=>app(\App\Services\InProcessChecksheetService::class)->getConsolidatedStandards()); echo "consolidatedStandards: {$ms} ms\n";

echo "\n== CONTROLLER + RENDER (end-to-end, cache hangat) ==\n";
auth()->login(\App\Models\User::where('role','admin')->first() ?? \App\Models\User::first());
DB::enableQueryLog();
$req = \Illuminate\Http\Request::create('/in-process-checksheets','GET',['plant'=>$plantName]);
$c = app(\App\Http\Controllers\InProcessChecksheetController::class);
[$ms,$resp]=t(fn()=>$c->index($req)); echo "controller->index(): {$ms} ms\n";
[$ms2,$html]=t(fn()=>$resp->render()); echo "view->render()     : {$ms2} ms\n";
$log=DB::getQueryLog(); usort($log,fn($a,$b)=>$b['time']<=>$a['time']);
echo "total queries: ".count($log)." | 5 terlambat:\n";
foreach(array_slice($log,0,5) as $q) echo "  ".round($q['time'])." ms  ".substr($q['query'],0,120)."\n";
