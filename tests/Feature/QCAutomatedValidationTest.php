<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\Plant;
use App\Models\InProcessChecksheet;
use App\Models\FirstPieceApproval;
use App\Models\CrossCutChecksheet;
use App\Models\CrossCutPaintingChecksheet;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class QCAutomatedValidationTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $plant;
    protected $categories;
    protected $inprocessItem;
    protected $platingItem;
    protected $paintingItem;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->plant = Plant::first() ?: Plant::create(['name' => 'Karawang', 'code' => 'KRW']);
        $this->admin = User::where('role', 'admin')->first() ?: User::create([
            'name' => 'Admin Test',
            'email' => 'admin_test@qc.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'plant_id' => $this->plant->id
        ]);

        // Get or Create Standard Categories
        $categoryNames = ['INPROSES', 'Cross Cut Plating', 'Cross Cut Painting'];
        $this->categories = [];
        foreach ($categoryNames as $name) {
            $cat = Category::where('name', $name)->first() ?: Category::create(['name' => $name]);
            $this->categories[$name] = $cat;
        }

        $this->inprocessItem = Item::create([
            'name' => 'Test Item Part',
            'part_number' => 'PN-TEST-001',
            'category_id' => $this->categories['INPROSES']->id,
            'plant_id' => $this->plant->id,
            'customer' => 'Test Customer'
        ]);
        
        // Mocking item for Cross Cut 
        $this->platingItem = Item::create([
            'name' => 'Test Item Cross Cut',
            'part_number' => 'PN-CC-001',
            'category_id' => $this->categories['Cross Cut Plating']->id,
            'plant_id' => $this->plant->id
        ]);

        $this->paintingItem = Item::create([
            'name' => 'Test Item Painting',
            'part_number' => 'PN-PT-001',
            'category_id' => $this->categories['Cross Cut Painting']->id,
            'plant_id' => $this->plant->id
        ]);
    }

    /** @test */
    public function it_can_access_all_qc_indexes()
    {
        $this->actingAs($this->admin);

        $routes = [
            'in_process.index',
            'first_piece_approval.index',
            'cross_cut.index',
            'cross_cut_painting.index',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route, ['plant' => $this->plant->id]));
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function it_can_create_in_process_checksheet_ok()
    {
        $this->actingAs($this->admin);
        $item = $this->inprocessItem;

        $data = [
            'item_id' => $item->id,
            'plant' => $this->plant->id,
            'date' => now()->toDateString(),
            'shift' => 'Shift 1',
            'code_machine' => '1',
            'total_qty' => 100,
            'sampling_qty' => 5,
            'total_ok' => 100,
            'total_ng' => 0,
            'judgment' => 'OK',
            'operator_initials' => 'TST',
        ];

        $response = $this->post(route('in_process.store'), $data);
        
        // Assert redirect or JSON success
        if ($response->status() === 302) {
            $response->assertRedirect();
            $this->assertDatabaseHas('in_process_checksheets', ['item_id' => $item->id]);
        } else {
            $response->assertJson(['success' => true]);
        }
    }

    /** @test */
    public function it_requires_next_proses_if_in_process_is_ng()
    {
        $this->actingAs($this->admin);
        $item = $this->inprocessItem;

        $data = [
            'item_id' => $item->id,
            'plant' => $this->plant->id,
            'date' => now()->toDateString(),
            'shift' => 'Shift 1',
            'code_machine' => '1',
            'total_qty' => 100,
            'sampling_qty' => 5,
            'total_ok' => 90,
            'total_ng' => 10,
            'judgment' => 'NG',
            // 'next_proses' => '', // Missing
        ];

        $response = $this->post(route('in_process.store'), $data);
        $response->assertSessionHasErrors('next_proses');
    }

    /** @test */
    public function it_can_create_cross_cut_plating_entry()
    {
        $this->actingAs($this->admin);
        
        $item = $this->platingItem;

        $data = [
            'item_id' => $item->id,
            'plant' => $this->plant->id,
            'production_date' => now()->toDateString(),
            'production_shift' => 'Shift 1',
            'qc_date' => now()->toDateString(),
            'qc_shift' => 'Shift 1',
            'position_remark_judgment' => 'OK',
            'position_remark_no_lot' => 'LOT123',
            'operator_initials' => 'TST',
            'check_type' => 'Standard',
            'image' => UploadedFile::fake()->image('test.jpg'),
        ];

        $response = $this->post(route('cross_cut.store'), $data);
        if ($response->status() === 302) {
            $response->assertRedirect();
        } else {
            $response->assertStatus(200);
        }
        $this->assertDatabaseHas('cross_cut_checksheets', ['item_id' => $item->id]);
    }

    /** @test */
    public function it_can_create_cross_cut_painting_entry()
    {
        $this->actingAs($this->admin);
        
        $item = $this->paintingItem;

        $data = [
            'item_id' => $item->id,
            'plant' => $this->plant->id,
            'production_date' => now()->toDateString(),
            'production_shift' => 'Shift 1',
            'qc_date' => now()->toDateString(),
            'qc_shift' => 'Shift 1',
            'position_remark_judgment' => 'OK',
            'operator_initials' => 'TST',
            'tap_test' => 'OK',
            'image' => UploadedFile::fake()->image('test.jpg'),
        ];

        $response = $this->post(route('cross_cut_painting.store'), $data);
        if ($response->status() === 302) {
            $response->assertRedirect();
        } else {
            $response->assertStatus(200);
        }
        $this->assertDatabaseHas('cross_cut_painting_checksheets', ['item_id' => $item->id]);
    }

    /** @test */
    public function it_formats_and_parses_plating_cabut_qr_code_correctly()
    {
        $plant = Plant::first() ?: Plant::create(['name' => 'Karawang', 'code' => 'KRW']);
        
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $admin->is_active = true;
            $admin->save();
        } else {
            $admin = User::create([
                'name' => 'Admin Test',
                'email' => 'admin_test@qc.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
                'plant_id' => $plant->id
            ]);
        }
        
        $this->actingAs($admin);
        $this->withoutExceptionHandling();
        
        // 1. Create a dummy Pasang record with a unique PO and date
        $po = 'PO' . rand(100000, 999999);
        $date = '2029-06-04';
        $dateFormatted = '04062029';
        $pasangRecord = \App\Models\PlatingPasangRecord::create([
            'wip_qrcode' => "53102-K0L-D002|$po|100|{$dateFormatted}IP1|JIG-010",
            'tanggal_pasang' => $date,
            'shift' => '1',
            'customer_part' => '53102-K0L-D002',
            'no_po' => $po,
            'qty' => 100,
            'lot_id' => "{$dateFormatted}IP1",
            'unique_code' => 'JIG-010',
            'inisial_pasang' => 'IP',
            'generated_qrcode' => "53102-K0L-D002|$po|100|{$dateFormatted}IP1|JIG-010",
            'plant_id' => $plant->id,
            'user_id' => $admin->id
        ]);

        // 2. Mock request data for Cabut process
        $data = [
            'pasang_qrcode' => "53102-K0L-D002|$po|100|{$dateFormatted}IP1|JIG-010",
            'tanggal_cabut' => $date,
            'shift' => '2',
            'no_po' => $po,
            'no_lot_original' => "{$dateFormatted}IP1|100|JIG-010",
            'qty_original' => 100,
            'inisial_cabut' => 'AJ',
            'splits' => [
                1 => ['qty_split' => 20, 'no_lot_split' => "{$dateFormatted}IP1|100|JIG-010"],
                2 => ['qty_split' => 30, 'no_lot_split' => "{$dateFormatted}IP1|100|JIG-010"],
            ]
        ];

        // 3. Post to store route using relative path
        $response = $this->post(route('plating_scan.cabut.store', [], false), $data);
        $response->assertRedirect(route('plating_scan.cabut.create', [], false));

        // 4. Assert that Cabut and Split records were created
        $cabut = \App\Models\PlatingCabutRecord::where('pasang_qrcode', $data['pasang_qrcode'])->first();
        $this->assertNotNull($cabut);

        $splits = \App\Models\PlatingCabutSplit::where('plating_cabut_record_id', $cabut->id)->get();
        $this->assertCount(2, $splits);

        // First split QR format should be: PartCode|NoPO|TglCabutInisialCabutShift|QtySplit|CBT-001
        // e.g. 53102-K0L-D002|POXXXXXX|04062029AJ2|20|CBT-001
        $this->assertEquals(
            "53102-K0L-D002|$po|{$dateFormatted}AJ2|20|CBT-001",
            $splits[0]->generated_qrcode
        );

        $this->assertEquals(
            "53102-K0L-D002|$po|{$dateFormatted}AJ2|30|CBT-002",
            $splits[1]->generated_qrcode
        );

        // 5. Test uniqueness checking endpoint for the new QR format
        $checkResponse = $this->get(route('items.check-qr-unique', ['qrcode' => $splits[0]->generated_qrcode], false));
        $checkResponse->assertStatus(200);
        $checkResponse->assertJson(['success' => true]);
    }

    /** @test */
    public function it_allows_same_unique_code_id_with_different_sap_code()
    {
        $plant = \App\Models\Plant::first() ?: \App\Models\Plant::create(['name' => 'Karawang', 'code' => 'KRW']);
        $admin = \App\Models\User::where('role', 'admin')->first() ?: \App\Models\User::create([
            'name' => 'Admin Test',
            'email' => 'admin_test2@qc.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
            'plant_id' => $plant->id
        ]);
        $this->actingAs($admin);

        $item = \App\Models\Item::first() ?: \App\Models\Item::create([
            'plant_id' => $plant->id,
            'part_number' => 'PN121225',
            'part_name' => 'Test Part',
            'sap_code' => '6-03-0020-03',
        ]);

        \Illuminate\Support\Facades\DB::table('in_process_checksheets')->insert([
            'item_id' => $item->id,
            'plant' => $plant->code,
            'date' => now()->toDateString(),
            'shift' => '1',
            'code_machine' => 'M1',
            'total_qty' => 10,
            'sampling_qty' => 1,
            'total_ok' => 10,
            'total_ng' => 0,
            'judgment' => 'OK',
            'part_code' => 'DATA01',
            'supplier_id' => '1200044',
            'quantity' => 880,
            'unique_code_id' => 'PN121225SHDM1A-001-202',
            'sap_code' => '6-03-0020-03',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Scanning QR with SAME unique_code_id but DIFFERENT sap_code should be UNIQUE (true)
        $qrData2 = "DATA02|1200044|880|PN121225SHDM1A-001-202|6-03-0020-04";
        $response1 = $this->get(route('items.check-qr-unique', ['qrcode' => $qrData2]));
        $response1->assertStatus(200);
        $response1->assertJson(['success' => true, 'unique' => true]);

        // Scanning QR with SAME unique_code_id AND SAME sap_code should be DUPLICATE (unique: false)
        $qrData1 = "DATA01|1200044|880|PN121225SHDM1A-001-202|6-03-0020-03";
        $response2 = $this->get(route('items.check-qr-unique', ['qrcode' => $qrData1]));
        $response2->assertStatus(200);
        $response2->assertJson(['success' => true, 'unique' => false]);

        // Additional Test with User's Exact Raw Barcode Examples:
        // QR 1: B74-F414B-10-00-80|76B5|40|22062026HRSN1-002-002|7-03-0131-40
        // QR 2: B74-F414B-10-00-80|76B5|100|22062026HRSN1-002-002|7-03-0131-100
        $userQr1 = "B74-F414B-10-00-80|76B5|40|22062026HRSN1-002-002|7-03-0131-40";
        $userQr2 = "B74-F414B-10-00-80|76B5|100|22062026HRSN1-002-002|7-03-0131-100";

        // Insert QR 1 into DB
        \Illuminate\Support\Facades\DB::table('in_process_checksheets')->insert([
            'item_id' => $item->id,
            'plant' => $plant->code,
            'date' => now()->toDateString(),
            'shift' => '1',
            'code_machine' => 'M1',
            'total_qty' => 40,
            'sampling_qty' => 1,
            'total_ok' => 40,
            'total_ng' => 0,
            'judgment' => 'OK',
            'part_code' => 'B74-F414B-10-00-80',
            'supplier_id' => '76B5',
            'quantity' => 40,
            'unique_code_id' => '22062026HRSN1-002-002',
            'sap_code' => '7-03-0131-40',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // QR 2 has same unique_code_id (22062026HRSN1-002-002) but different sap_code (7-03-0131-100) -> MUST be UNIQUE
        $userCheckResponse = $this->get(route('items.check-qr-unique', ['qrcode' => $userQr2]));
        $userCheckResponse->assertStatus(200);
        $userCheckResponse->assertJson(['success' => true, 'unique' => true]);
    }

    /** @test */
    public function it_resets_jig_code_when_no_po_changes()
    {
        $plant = Plant::first() ?: Plant::create(['name' => 'Karawang', 'code' => 'KRW']);
        $admin = User::where('role', 'admin')->first() ?: User::create([
            'name' => 'Admin Test',
            'email' => 'admin_test@qc.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
            'plant_id' => $plant->id
        ]);
        
        $this->actingAs($admin);

        // 1. First Pasang with PO-12345
        $data1 = [
            'wip_qrcode' => 'B74-F4786-00|PO-12345|20|1|7-02-0347',
            'no_po' => 'PO-12345',
            'no_lot' => '1',
            'qty' => 20,
            'tanggal_pasang' => '2026-06-05',
            'shift' => '1',
            'inisial_pasang' => 'AJ'
        ];
        $response1 = $this->post(route('plating_scan.pasang.store', [], false), $data1);
        $response1->assertStatus(302);
        $record1 = \App\Models\PlatingPasangRecord::where('no_po', 'PO-12345')->orderBy('id', 'desc')->first();
        $this->assertStringEndsWith('JIG-001', $record1->generated_qrcode);

        // 2. Second Pasang with PO-12345 (JIG should increment to JIG-002)
        $data2 = [
            'wip_qrcode' => 'B74-F4786-00|PO-12345|20|2|7-02-0348',
            'no_po' => 'PO-12345',
            'no_lot' => '2',
            'qty' => 20,
            'tanggal_pasang' => '2026-06-05',
            'shift' => '1',
            'inisial_pasang' => 'AJ'
        ];
        $response2 = $this->post(route('plating_scan.pasang.store', [], false), $data2);
        $response2->assertStatus(302);
        $record2 = \App\Models\PlatingPasangRecord::where('no_po', 'PO-12345')->orderBy('id', 'desc')->first();
        $this->assertStringEndsWith('JIG-002', $record2->generated_qrcode);

        // 3. Third Pasang with a different PO-54321 (JIG should reset to JIG-001)
        $data3 = [
            'wip_qrcode' => 'B74-F4786-00|PO-54321|20|1|7-02-0349',
            'no_po' => 'PO-54321',
            'no_lot' => '1',
            'qty' => 20,
            'tanggal_pasang' => '2026-06-05',
            'shift' => '1',
            'inisial_pasang' => 'AJ'
        ];
        $response3 = $this->post(route('plating_scan.pasang.store', [], false), $data3);
        $response3->assertStatus(302);
        $record3 = \App\Models\PlatingPasangRecord::where('no_po', 'PO-54321')->orderBy('id', 'desc')->first();
        $this->assertStringEndsWith('JIG-001', $record3->generated_qrcode);
    }

    /** @test */
    public function it_resets_cabut_cbt_code_when_no_po_changes()
    {
        $plant = Plant::first() ?: Plant::create(['name' => 'Karawang', 'code' => 'KRW']);
        $admin = User::where('role', 'admin')->first() ?: User::create([
            'name' => 'Admin Test',
            'email' => 'admin_test@qc.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
            'plant_id' => $plant->id
        ]);
        
        $this->actingAs($admin);

        // Create dummy Pasang records for two different POs
        $pasang1 = \App\Models\PlatingPasangRecord::create([
            'wip_qrcode' => '53102-K0L-D002|PO-111|100|04062026IP1|JIG-010',
            'tanggal_pasang' => '2026-06-04',
            'shift' => '1',
            'customer_part' => '53102-K0L-D002',
            'no_po' => 'PO-111',
            'qty' => 100,
            'lot_id' => '04062026IP1',
            'unique_code' => 'JIG-010',
            'inisial_pasang' => 'IP',
            'generated_qrcode' => '53102-K0L-D002|PO-111|100|04062026IP1|JIG-010',
            'plant_id' => $plant->id,
            'user_id' => $admin->id
        ]);

        $pasang2 = \App\Models\PlatingPasangRecord::create([
            'wip_qrcode' => '53102-K0L-D002|PO-222|100|04062026IP2|JIG-011',
            'tanggal_pasang' => '2026-06-04',
            'shift' => '1',
            'customer_part' => '53102-K0L-D002',
            'no_po' => 'PO-222',
            'qty' => 100,
            'lot_id' => '04062026IP2',
            'unique_code' => 'JIG-011',
            'inisial_pasang' => 'IP',
            'generated_qrcode' => '53102-K0L-D002|PO-222|100|04062026IP2|JIG-011',
            'plant_id' => $plant->id,
            'user_id' => $admin->id
        ]);

        // 1. Cabut first split for PO-111
        $data1 = [
            'pasang_qrcode' => '53102-K0L-D002|PO-111|100|04062026IP1|JIG-010',
            'tanggal_cabut' => '2026-06-04',
            'shift' => '2',
            'no_po' => 'PO-111',
            'no_lot_original' => '04062026IP1|100|JIG-010',
            'qty_original' => 100,
            'inisial_cabut' => 'AJ',
            'splits' => [
                1 => ['qty_split' => 20, 'no_lot_split' => '04062026IP1|100|JIG-010'],
            ]
        ];
        $response1 = $this->post(route('plating_scan.cabut.store', [], false), $data1);
        $response1->assertStatus(302);
        
        $cabut1 = \App\Models\PlatingCabutRecord::where('no_po', 'PO-111')->orderBy('id', 'desc')->first();
        $split1 = \App\Models\PlatingCabutSplit::where('plating_cabut_record_id', $cabut1->id)->first();
        $this->assertStringEndsWith('CBT-001', $split1->generated_qrcode);

        // 2. Cabut second split for PO-111 (CBT should continue to CBT-002)
        $data2 = [
            'pasang_qrcode' => '53102-K0L-D002|PO-111|100|04062026IP1|JIG-010',
            'tanggal_cabut' => '2026-06-04',
            'shift' => '2',
            'no_po' => 'PO-111',
            'no_lot_original' => '04062026IP1|100|JIG-010',
            'qty_original' => 100,
            'inisial_cabut' => 'AJ',
            'splits' => [
                1 => ['qty_split' => 30, 'no_lot_split' => '04062026IP1|100|JIG-010'],
            ]
        ];
        $response2 = $this->post(route('plating_scan.cabut.store', [], false), $data2);
        $response2->assertStatus(302);

        $cabut2 = \App\Models\PlatingCabutRecord::where('no_po', 'PO-111')->orderBy('id', 'desc')->first();
        $split2 = \App\Models\PlatingCabutSplit::where('plating_cabut_record_id', $cabut2->id)->first();
        $this->assertStringEndsWith('CBT-002', $split2->generated_qrcode);

        // 3. Cabut third split for PO-222 (CBT should reset to CBT-001)
        $data3 = [
            'pasang_qrcode' => '53102-K0L-D002|PO-222|100|04062026IP2|JIG-011',
            'tanggal_cabut' => '2026-06-04',
            'shift' => '2',
            'no_po' => 'PO-222',
            'no_lot_original' => '04062026IP2|100|JIG-011',
            'qty_original' => 100,
            'inisial_cabut' => 'AJ',
            'splits' => [
                1 => ['qty_split' => 40, 'no_lot_split' => '04062026IP2|100|JIG-011'],
            ]
        ];
        $response3 = $this->post(route('plating_scan.cabut.store', [], false), $data3);
        $response3->assertStatus(302);

        $cabut3 = \App\Models\PlatingCabutRecord::where('no_po', 'PO-222')->orderBy('id', 'desc')->first();
        $split3 = \App\Models\PlatingCabutSplit::where('plating_cabut_record_id', $cabut3->id)->first();
        $this->assertStringEndsWith('CBT-001', $split3->generated_qrcode);
    }

    /** @test */
    public function it_deletes_notifications_on_checksheet_deletion()
    {
        $this->actingAs($this->admin);

        // Create a checksheet
        $checksheet = CrossCutPaintingChecksheet::create([
            'plant_id' => $this->plant->id,
            'item_id' => $this->paintingItem->id,
            'production_shift' => '1',
            'qc_shift' => '1',
            'production_datetime' => now(),
            'qc_datetime' => now(),
            'position_remark_judgment' => 'OK',
            'operator_initials' => 'AJ',
            'image_path' => 'test.png'
        ]);

        // Create a mock notification linked to it
        $notification = \App\Models\Notification::create([
            'user_id' => $this->admin->id,
            'type' => 'approval',
            'title' => 'Test Approval Request',
            'message' => 'Laporan membutuhkan approval',
            'data' => [
                'checksheet_id' => $checksheet->id,
                'checksheet_type' => 'Cross Cut Painting',
                'plant_id' => $this->plant->id
            ]
        ]);

        // Verify the notification exists
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id
        ]);

        // Delete the checksheet
        $checksheet->delete();

        // Verify the notification is deleted
        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id
        ]);
    }

    /** @test */
    public function it_can_clear_all_notifications()
    {
        $this->actingAs($this->admin);

        // Create a mock notification
        $notification = \App\Models\Notification::create([
            'user_id' => $this->admin->id,
            'type' => 'approval',
            'title' => 'Test Notification',
            'message' => 'Laporan membutuhkan approval',
            'data' => [
                'plant_id' => $this->plant->id
            ]
        ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id
        ]);

        $response = $this->delete(route('notifications.clear-all'));
        $response->assertStatus(200);

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id
        ]);
    }

    /** @test */
    public function it_creates_notifications_with_correct_module_routes()
    {
        $this->actingAs($this->admin);

        // Instatiate NotificationService
        $service = new \App\Services\NotificationService();

        // Create a checksheet
        $checksheet = CrossCutPaintingChecksheet::create([
            'plant_id' => $this->plant->id,
            'item_id' => $this->paintingItem->id,
            'production_shift' => '1',
            'qc_shift' => '1',
            'production_datetime' => now(),
            'qc_datetime' => now(),
            'position_remark_judgment' => 'OK',
            'operator_initials' => 'AJ',
            'image_path' => 'test.png'
        ]);

        // Trigger notification
        $service->notifyApprovalRequest($checksheet, 'Cross Cut Painting');

        // Verify the created notification has the correct URL
        $notification = \App\Models\Notification::where('user_id', $this->admin->id)
            ->where('type', 'approval')
            ->orderBy('id', 'desc')
            ->first();

        $this->assertNotNull($notification);
        
        // Ensure the data array is decoded properly
        $data = $notification->data;
        $expectedUrl = route('cross_cut_painting.index', ['id' => $checksheet->id]);
        $this->assertEquals($expectedUrl, $data['url']);
    }

    /** @test */
    public function it_filters_checksheets_by_id_query_parameter()
    {
        $this->actingAs($this->admin);

        // Find or create a plant with name 'Karawang' to match resolvePlantId('karawang')
        $krwPlant = \App\Models\Plant::where('code', 'karawang')
            ->orWhere('name', 'Karawang')
            ->first() ?: \App\Models\Plant::create(['name' => 'Karawang', 'code' => 'karawang']);

        $category = \App\Models\Category::where('name', 'Double Tape')->first() ?: \App\Models\Category::create(['name' => 'Double Tape']);

        // Create double tape checksheet
        $item = \App\Models\Item::create([
            'name' => 'Double Tape Item',
            'part_number' => 'DT-123',
            'category_id' => $category->id,
            'plant_id' => $krwPlant->id,
            'min_sampling' => 1
        ]);

        $checksheet1 = \App\Models\DoubleTapeChecksheet::create([
            'plant_id' => $krwPlant->id,
            'item_id' => $item->id,
            'date' => now(),
            'shift' => '1',
            'total_qty' => 10,
            'total_ok' => 10,
            'total_ng' => 0,
            'sampling_qty' => 5,
            'judgment' => 'OK',
            'operator_initials' => 'OP',
            'check_type' => 'regular'
        ]);

        $checksheet2 = \App\Models\DoubleTapeChecksheet::create([
            'plant_id' => $krwPlant->id,
            'item_id' => $item->id,
            'date' => now(),
            'shift' => '1',
            'total_qty' => 20,
            'total_ok' => 20,
            'total_ng' => 0,
            'sampling_qty' => 5,
            'judgment' => 'OK',
            'operator_initials' => 'OP',
            'check_type' => 'regular'
        ]);

        // Request with id filter for checksheet1
        $response = $this->get(route('double_tape.index', ['id' => $checksheet1->id]));
        $response->assertStatus(200);

        // Verify checksheet1 is present
        $response->assertSee('Double Tape Item');
        
        // Let's assert database filter works through the service
        $service = app(\App\Services\DoubleTapeChecksheetService::class);
        $results = $service->getFilteredChecksheets(['id' => $checksheet1->id, 'plant' => 'karawang']);
        $this->assertEquals(1, $results->count());
        $this->assertEquals($checksheet1->id, $results->first()->id);
    }
}
