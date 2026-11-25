<?php

namespace Database\Seeders;

use App\Models\LevelingCheckItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class LevelingCheckItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = array (
  0 => 
  array (
    'prinsip' => 'MSK',
    'level' => 1,
    'order_no' => 1,
    'point_check' => 'Apakah ada WI/SOP (QCPS) untuk masing-masing shop dan masing-masing proses?',
    'guidance' => '1. WI sesuai part number dan pekerjaan terdapat di line/mesin yang berjalan
2. WI dalam kondisi updated
3. Jika sudah sesuai isi, di WI tidak ada coretan  (Revisi Manual)
4. Kondisi WI ada approval (atasan)',
  ),
  1 => 
  array (
    'prinsip' => 'MSK',
    'level' => 1,
    'order_no' => 2,
    'point_check' => 'Ada checksheet untuk mencatat hasil pengecekan quality',
    'guidance' => 'Terdapat checksheet terkait jaminan terhadap proses dan kualitas
Contoh : (Checksheet Awal/tengah/akhir; Checksheet Parameter setting; Checksheet Maintanance Mesin)',
  ),
  2 => 
  array (
    'prinsip' => 'MSK',
    'level' => 1,
    'order_no' => 3,
    'point_check' => 'Alat ukur/Kerja yang ditentukan sudah dilengkapi',
    'guidance' => 'Terdapat alat ukur/alat kerja yang digunakan oleh operator berdasarkan proses yang ada di WI
Contoh : Cutting tools, Hummer, pensil marking, Caliper, dll)',
  ),
  3 => 
  array (
    'prinsip' => 'MSK',
    'level' => 1,
    'order_no' => 4,
    'point_check' => 'Ada tempat untuk memajang SOP (WI)',
    'guidance' => 'Terdapat tempat untuk memasang WI dan kondisinya terjaga (rapi dan bersih)',
  ),
  4 => 
  array (
    'prinsip' => 'MSK',
    'level' => 2,
    'order_no' => 1,
    'point_check' => 'Mengenai edukasi WI/SOP (QCPS) kepada operator --> Bukti Edukasi WI/SOP Operator',
    'guidance' => 'Telah dilakukan edukasi WI ke operator
dan ada bukti hadir sampai dengan tanda tangan operator
Metode:
operator bisa menjelaskan poin penting yang ada di WI
(Menanyakan langsung ke operator terkait poin penting --> Menunggu Aktivitas pekerjaan operator sedang berhenti/ selesai 1 cycle)',
  ),
  5 => 
  array (
    'prinsip' => 'MSK',
    'level' => 3,
    'order_no' => 1,
    'point_check' => 'Operator bisa bekerja sesuai dengan WI/SOP (QCPS)',
    'guidance' => 'Urutan kerja operator sesuai dengan WI yang ada 
Metode :
Dilakukan pengecekan dengan membawa WI dan dilihat poin by poin
(Menunggu Aktivitas pekerjaan operator sedang berhenti/ selesai 1 cycle)',
  ),
  6 => 
  array (
    'prinsip' => 'MSK',
    'level' => 3,
    'order_no' => 2,
    'point_check' => 'Apakah tidak ada pekerjaan yang sulit?',
    'guidance' => 'Poin by poin yang ada di WI bisa dikerjakan oleh operator; jika operator tidak bisa  maka bertanya langsung/mendapatkan informasi dari/ke operator.
Metode :
1. Menggunakan kertas dan ditulis sendiri oleh operator
2. Menanyakan langsung ke operator terkait apakah ada pekerjaan yang sulit (Aktivitas pekerjaan operator sedang berhenti/ selesai 1 cycle)',
  ),
  7 => 
  array (
    'prinsip' => 'MSK',
    'level' => 3,
    'order_no' => 3,
    'point_check' => 'Dilakukan pengukuran sesuai dengan frequency yang ditentukan dan ada recordnya.',
    'guidance' => 'Checksheet terkait jaminan terhadap proses dan kualitas dilakukan pengecekan sesuai aktual/standard dan ada recordnya.
Contoh : (Checksheet Awal/tengah/akhir; Checksheet Parameter setting; Checksheet Maintanance Mesin)
Metode : 
1. Melihat record checkseet
2. Melihat operator/PIC pengecekan melakukan sampling pengecekan terhadap checksheet yang ada',
  ),
  8 => 
  array (
    'prinsip' => 'MSK',
    'level' => 3,
    'order_no' => 4,
    'point_check' => 'WI bisa dibaca dari posisi kerja.',
    'guidance' => 'Posisi/tempat meletakan WI:
1. Kondisi WI mudah dan bisa terbaca oleh operator
2. Kondisi tempat memajang dalam kondisi rapi dan bersih
(Jika posisi WI tidak memungkinkan tidak bisa diletakan di area kerja operator, maka perlu ada aktivitas review pemahaman WI kepada operator)',
  ),
  9 => 
  array (
    'prinsip' => 'MSK',
    'level' => 3,
    'order_no' => 5,
    'point_check' => 'Foreman/Atasan melakukan patrol kepatuhan operator terhadap WI dan ada recordnya.',
    'guidance' => 'Terdapat aktivitas pengecekan kepatuhan operator terhadap WI
Metode :
1. Terdapat schedule pengecekan ke operator (jika dalam hari Atasan tidak bisa melakukan pengecekan ke semua operator)
2. Dilihat hasil pengecekan aktual oleh atasan (berdasarkan checksheet yang ada)
3. Apabila ada abnormal/temuan dari hasil pengecekan maka dilakukan action dan record',
  ),
  10 => 
  array (
    'prinsip' => 'MSK',
    'level' => 3,
    'order_no' => 6,
    'point_check' => 'Foreman/Atasan bisa melakukan pengukuran sampling N=1',
    'guidance' => 'Atasan dapat/bisa melakukan verifikasi terhadap hasil kerja operator 
Metode :
1. Melihat atasan melakukan verifikasi langsung hasil kerja operator (1 part)',
  ),
  11 => 
  array (
    'prinsip' => 'MSK',
    'level' => 3,
    'order_no' => 7,
    'point_check' => 'Ada record pengukuran sampling N=1',
    'guidance' => 'Ada record terhadap atasan telah melakukan verifikasi terhadap hasil kerja operator dan apabila ada abnormal terdapat action/perbaikan',
  ),
  12 => 
  array (
    'prinsip' => 'MSK',
    'level' => 3,
    'order_no' => 8,
    'point_check' => 'Apabila  ada problem bisa memberi bimbingan dan edukasi pada saat itu. (cek ada/tidak recordnya)',
    'guidance' => 'Atasan melakukan bimbingan/Edukasi pada:
1. Jika ada temuan terhadap hasil pengecekan operator jika tidak bisa ikut WI maka perlu adanya bimbingan dan edukasi lagi terhadap operator tersebut (disertakan record/bukti dari temuan sampai dengan aktivitas bimbingan/edukasinya)
2. Jika ada feedback problem dari internal/customer maka perlu diberikan informasi atau edukasi lagi ke operator terhadap problem tersebut (disertakan record/bukti dari temuan sampai dengan aktivitas bimbingan/edukasinya)',
  ),
  13 => 
  array (
    'prinsip' => 'KT',
    'level' => 1,
    'order_no' => 1,
    'point_check' => 'Kondisi kerja tuntas sudah ditentukan.',
    'guidance' => 'Disetiap proses sudah ditentukan kondisi kerja tuntasnya
1. Terdapat rule kerja tuntas pada shop/line/mesin yang sedang dilakukan pengecekan',
  ),
  14 => 
  array (
    'prinsip' => 'KT',
    'level' => 1,
    'order_no' => 2,
    'point_check' => 'Penempatan part sudah jelas',
    'guidance' => 'Di dalam rule kerja tuntas penjelasan di rule harus jelas:
1. Apakah harus dalam kondisi 1 cycle atau perlu penempatan tag kerja tuntas
2. Kondisi area kerja harus kosong atau masih boleh ada part',
  ),
  15 => 
  array (
    'prinsip' => 'KT',
    'level' => 2,
    'order_no' => 1,
    'point_check' => 'Edukasi operator pakai tool edukasi (Ada Bukti Edukasi Operator)',
    'guidance' => 'Telah dilakukan edukasi Rule Kerja Tuntas ke operator
dan ada bukti hadir sampai dengan tanda tangan operator',
  ),
  16 => 
  array (
    'prinsip' => 'KT',
    'level' => 2,
    'order_no' => 2,
    'point_check' => 'Operator memahami kondisi kerja tuntas',
    'guidance' => 'Operator bisa menjelaskan rule kerja tuntas
(Menanyakan langsung ke operator terkait rule kerja tuntas --> Menunggu Aktivitas pekerjaan operator sedang berhenti/ selesai 1 cycle)',
  ),
  17 => 
  array (
    'prinsip' => 'KT',
    'level' => 3,
    'order_no' => 1,
    'point_check' => 'Bisa melakukan kerja tuntas',
    'guidance' => 'Implementasi Kerja tuntas dilakukan sesuai dengan rule kerja tuntas
Metode :
1. Dilakukan pengecekan saat kondisi sedang istirahat/Akhir shift/saat sedang ke Toilet',
  ),
  18 => 
  array (
    'prinsip' => 'KT',
    'level' => 3,
    'order_no' => 2,
    'point_check' => 'Foreman melakukan  patrol kondisi pelaksanaan kerja tuntas dan ada recordnya.',
    'guidance' => 'Terdapat aktivitas pengecekan kepatuhan operator terhadap Rule Kerja tuntas
Metode :
1. Terdapat schedule pengecekan ke operator (jika dalam hari Atasan tidak bisa melakukan pengecekan ke semua operator)
2. Dilihat hasil pengecekan aktual oleh atasan (berdasarkan checksheet yang ada)
3. Apabila ada abnormal/temuan dari hasil pengecekan maka dilakukan action dan record',
  ),
  19 => 
  array (
    'prinsip' => 'KT',
    'level' => 3,
    'order_no' => 3,
    'point_check' => 'Mesin (Alat kerja) berhenti dalam kondisi kerja tuntas.',
    'guidance' => 'Implementasi mesin dan alat kerja dilakukan sesuai dengan rule kerja tuntas (5P)
Metode :
Dilakukan pengecekan saat kondisi sedang istirahat/Akhir shift/saat sedang ke Toilet',
  ),
  20 => 
  array (
    'prinsip' => 'KT',
    'level' => 3,
    'order_no' => 4,
    'point_check' => 'Apabila  ada problem bisa memberi bimbingan dan edukasi pada saat itu.',
    'guidance' => 'Atasan melakukan bimbingan/Edukasi pada:
1. Jika ada temuan terhadap hasil pengecekan operator jika tidak bisa ikut rule kerja tuntas maka perlu adanya bimbingan dan edukasi lagi terhadap operator tersebut (disertakan record/bukti dari temuan sampai dengan aktivitas bimbingan/edukasinya)
2. Jika ada feedback problem dari internal/customer maka perlu diberikan informasi atau edukasi lagi ke operator terhadap problem tersebut (disertakan record/bukti dari temuan sampai dengan aktivitas bimbingan/edukasinya)',
  ),
  21 => 
  array (
    'prinsip' => 'PNG',
    'level' => 1,
    'order_no' => 1,
    'point_check' => 'Rule penanganan part NG sudah ditentukan',
    'guidance' => 'Disetiap proses sudah ditentukan kondisi Rule/aturan penanganan part NG dan repair (Jika ada proses repair)
Metode:
1. Terdapat rule penanganan part NG dan repair pada shop/line/mesin yang sedang dilakukan pengecekan
(Rule berisikan dari mulai menemukan, memberi identifikasi, penempatan, record, dan penanganannya --> PIC yang bertanggung jawab)',
  ),
  22 => 
  array (
    'prinsip' => 'PNG',
    'level' => 1,
    'order_no' => 2,
    'point_check' => 'Ada tempat untuk meletakkan part NG  di line dan shop',
    'guidance' => 'Terdapat box/tempat untuk meletakan part NG/Repair pada shop/line/mesin yang sedang dilakukan pengecekan',
  ),
  23 => 
  array (
    'prinsip' => 'PNG',
    'level' => 1,
    'order_no' => 3,
    'point_check' => 'Ada label (tag) untuk ditempel pada part NG',
    'guidance' => 'Terdapat identifikasi (label/marking) pada area part NG/Repair (Sesuai dengan aturan penanganan part NG)',
  ),
  24 => 
  array (
    'prinsip' => 'PNG',
    'level' => 2,
    'order_no' => 1,
    'point_check' => 'Operator ikut edukasi rule  penanganan part NG, dan memahaminya. (Ada Bukti Edukasi Operator)',
    'guidance' => 'Telah dilakukan edukasi Rule Penanganan Part NG ke operator
dan ada bukti hadir sampai dengan tanda tangan operator
Metode:
operator bisa menjelaskan tekait aturan penanganan part NG di prosesnya
(Menanyakan langsung ke operator  --> Menunggu Aktivitas pekerjaan operator sedang berhenti/ selesai 1 cycle)',
  ),
  25 => 
  array (
    'prinsip' => 'PNG',
    'level' => 2,
    'order_no' => 2,
    'point_check' => 'Di tempat part NG tidak ada part yang tidak jelas  (yaitu part yang tanpa label)',
    'guidance' => 'Identifikasi atau status part pada box/tempat NG sudah jelas
Metode :Dilakukan pengecekan di tempat/box NG (apakah ada part yang tidak diberikan identifikasi sesuai denga rule yang ada)',
  ),
  26 => 
  array (
    'prinsip' => 'PNG',
    'level' => 2,
    'order_no' => 3,
    'point_check' => 'Part NG (part yang tidak jelas) tidak diletakkan di line',
    'guidance' => 'Identifikasi atau status part pada area proses sudah jelas
Metode :
Dilakukan pengecekan di area kerja operator (apakah ada part tanpa identifikasi/part NG tidak diletakan di line/proses area kerja operator)',
  ),
  27 => 
  array (
    'prinsip' => 'PNG',
    'level' => 3,
    'order_no' => 1,
    'point_check' => 'Ada pemisahan part repair, part tunda (pending) yang jelas, dan mencantumkan tgl, nama part, quantity, isi, PIC.',
    'guidance' => 'Pemisahan untuk part repair, tunda sudah jelas (Jika terdapat proses repair)
Metode :
Dilakukan pengecekan langsung di area/Genba 
Terdapat box/tempat untuk meletakan part NG/Repair pada shop/line/mesin yang sedang dilakukan pengecekan',
  ),
  28 => 
  array (
    'prinsip' => 'PNG',
    'level' => 3,
    'order_no' => 2,
    'point_check' => 'PIC yang menangani NG setiap hari mengumpulkan  data part NG, merangkum (summary) pada format yang sudah ditentukan, dan mengatasinya.',
    'guidance' => 'Record harian Part NG/Repair jelas dan dilakukan pengisian sesuai dengan Rule
Metode:
Dilakukan pengecekan terhadap checksheet record harian part NG/Repair pada hari H maupun hari sebelumnya (Minimal 1 minggu sebelumnya)',
  ),
  29 => 
  array (
    'prinsip' => 'PNG',
    'level' => 3,
    'order_no' => 3,
    'point_check' => 'Part repair dan part tunda diproses sesuai dengan rule',
    'guidance' => 'Implementasi terkait penanganan part repair sesuai dengan rule (Jika terdapat proses repair)
Metode :
1. Dilakukan pengecekan terhadap record part repair
2. Dilakukan pengecekan di area repair part apakah operator sudah melakukan proses repair sesuai dengan rule',
  ),
  30 => 
  array (
    'prinsip' => 'PNG',
    'level' => 3,
    'order_no' => 4,
    'point_check' => 'Foreman melakukan patrol kepatuhan rule setiap hari',
    'guidance' => 'Terdapat aktivitas pengecekan kepatuhan operator terhadap Rule Penanganan Part NG
Metode :
1. Terdapat schedule pengecekan ke operator (jika dalam hari Atasan tidak bisa melakukan pengecekan ke semua operator)
2. Dilihat hasil pengecekan aktual oleh atasan (berdasarkan checksheet yang ada)
3. Apabila ada abnormal/temuan dari hasil pengecekan maka dilakukan action dan record',
  ),
  31 => 
  array (
    'prinsip' => 'PSSP',
    'level' => 1,
    'order_no' => 1,
    'point_check' => 'Ada rule handling part sebelum dan sesudah proses.(Apakah ada rule membawa keluar produk sebentar dari line, dll?）',
    'guidance' => 'Disetiap proses sudah ditentukan kondisi Rule/aturan membawa part/produk keluar dari line (part untuk pengecekan, untuk analisa, keperluan meeting,dll)
Metode:
1. Terdapat rule penanganan /aturan membawa part/produk keluar dari line pada shop/line/mesin yang sedang dilakukan pengecekan
(Rule berisikan PIC yang boleh memberikan part ke bagian lain, record peminjaman, sampai dengan status part tersebut kembali atau tidak)',
  ),
  32 => 
  array (
    'prinsip' => 'PSSP',
    'level' => 1,
    'order_no' => 2,
    'point_check' => 'Apa ada tempat untuk menempatkan part sebelum dan sesudah proses pada masing-masing proses?',
    'guidance' => 'Disetiap proses tempat penempatan part sebelum dan sudah sudah ditentukan dengan jelas
Metode: 
Dilakukan pengecekan di area genba apakah sudah ada tempat pemisahan part sebelum dan sesudah proses',
  ),
  33 => 
  array (
    'prinsip' => 'PSSP',
    'level' => 1,
    'order_no' => 3,
    'point_check' => 'Apakah part sebelum proses (diberi warna hijau), sesudah proses (diberi warna merah) bisa diketahui oleh siapapun?',
    'guidance' => 'Disetiap proses identifikasi pada tempat penempatan part sebelum dan sudah sudah ditentukan dengan jelas (Bisa dalam bentuk tulisan atau warna)
Metode: 
Dilakukan pengecekan di area genba apakah sudah ada identifikasi pada tempat pemisahan part sebelum dan sesudah proses',
  ),
  34 => 
  array (
    'prinsip' => 'PSSP',
    'level' => 2,
    'order_no' => 1,
    'point_check' => 'Operator ikut edukasi rule identifikasi part sebelum dan sesudah proses.',
    'guidance' => 'Telah dilakukan edukasi Rule/aturan membawa part/produk keluar dari line kepada Operator sampai dengan atasan/PIC yang bertanggung jawab
dan ada bukti hadir sampai dengan tanda tangan',
  ),
  35 => 
  array (
    'prinsip' => 'PSSP',
    'level' => 2,
    'order_no' => 2,
    'point_check' => 'Apakah operator memahami tempat penempatan dan quantitinya sesuai rule?',
    'guidance' => 'Operator bisa menjelaskan area dan identifikasi penempatan part sebelum dan sesudah proses
(Menanyakan langsung ke operator --> Menunggu Aktivitas pekerjaan operator sedang berhenti/ selesai 1 cycle)',
  ),
  36 => 
  array (
    'prinsip' => 'PSSP',
    'level' => 3,
    'order_no' => 1,
    'point_check' => 'Ketika membawa produk keluar line untuk pengukuran, dll, operator menempelkan label (tag) dll yang menujukkan sudah selesai  sampai proses mana.',
    'guidance' => 'Implementasi terkait membawa produk part keluar line sudah dilakukan sesuai dengan rule
Metode:
1. Dilakukan pengecekan record terhadap membawa produk/part keluar line
2. Jika ada kondisi part sedang dibawa dilakukan pengecekan apakah sudah sesuai rule atau belum (identifikasi part)',
  ),
  37 => 
  array (
    'prinsip' => 'PSSP',
    'level' => 3,
    'order_no' => 2,
    'point_check' => 'Produk yang tidak jadi karena rusak, dll, sudah  ditentukan tempatnya, dipisahkan diberi wadah (container) tersendiri, dan diberi identifikasi yang menunjukkan sudah  selesai sampai proses mana.',
    'guidance' => 'Area dari penempatan part dandory, setting, karantina sudah ada dan jelas 
Metode :
dilakukan pengecekan langsung di genba, apakah ada area dari penempatan part dandory, setting, karantina 
(identifikasi area harus ada dan jelas)',
  ),
  38 => 
  array (
    'prinsip' => 'PSSP',
    'level' => 3,
    'order_no' => 3,
    'point_check' => 'Foreman melakukan patrol kepatuhan rule setiap hari',
    'guidance' => 'Terdapat aktivitas pengecekan kepatuhan operator terhadap penempatan part sebelum dan sesudah proses
Metode :
1. Terdapat schedule pengecekan ke operator (jika dalam hari Atasan tidak bisa melakukan pengecekan ke semua operator)
2. Dilihat hasil pengecekan aktual oleh atasan (berdasarkan checksheet yang ada)
3. Apabila ada abnormal/temuan dari hasil pengecekan maka dilakukan action dan record',
  ),
  39 => 
  array (
    'prinsip' => 'KAO',
    'level' => 1,
    'order_no' => 1,
    'point_check' => 'Ada rule management awal untuk operator baru, operator pindahan.',
    'guidance' => 'Terdapat aturan/rule terkait penanganan :
1. Operator Baru
2. Operator Pindahan
3. Operator Pengganti
Aturan/rule harus jelas dari awal penerimaan, Edukasi (inclass dan Genba), Evaluasi (inclass dan Genba), Monitoring, sampai dengan Judgement Akhir',
  ),
  40 => 
  array (
    'prinsip' => 'KAO',
    'level' => 1,
    'order_no' => 2,
    'point_check' => 'Ada materi edukasi pekerjaan (WI/OPL)',
    'guidance' => 'Materi Edukasi untuk operator ada dan jelas
Contoh :
Operator baru : Visi Misi Perusahaan, aturan perusahaan, YPQ dasar, aturan yang ada di proses (Cara kerja, pengisian Checksheet, WI, Kerja tuntas, dll) --> dalam bentuk inclass sampai dengan OJT
Operator Pindahan : YPQ dasar, aturan yang ada di proses (Cara kerja, pengisian Checksheet, WI, Kerja tuntas, dll) --> dalam bentuk inclass sampai dengan OJT
Operator pengganti : aturan yang ada di proses (Cara kerja, pengisian Checksheet, WI, Kerja tuntas, dll) --> dalam bentuk inclass sampai dengan OJT',
  ),
  41 => 
  array (
    'prinsip' => 'KAO',
    'level' => 2,
    'order_no' => 1,
    'point_check' => 'Operator baru sudah ikut edukasi  sesuai dengan rule.',
    'guidance' => 'Implementasi jika ada operator baru/pindahan/pengganti sudah ikut rule
Metode: 
1. Jika saat pengecekan sedang ada operator baru yang sedang bekerja
maka dilakukan pengecekan terhadap operator tersebut apakah sudah dilakukan edukasi sesuai dengan rule atau tidak
2. Jika saat pengecekan sedang tidak ada operator baru, maka dimintakan data untuk operator lain (cari waktu kerja yang paling dekat)',
  ),
  42 => 
  array (
    'prinsip' => 'KAO',
    'level' => 2,
    'order_no' => 2,
    'point_check' => 'Ada record edukasi',
    'guidance' => 'Record dan bukti dari edukasi ada dan jelas 
Metode : 
Sampling check Ada bukti hadir sampai dengan tanda tangan operator dari semua materi/hari dilakukan edukasi',
  ),
  43 => 
  array (
    'prinsip' => 'KAO',
    'level' => 2,
    'order_no' => 3,
    'point_check' => 'Record edukasi disimpan',
    'guidance' => 'Record dan bukti dari edukasi disimpan dengan rapi :
Metode :
Sampling check untuk operator baru/pindahan',
  ),
  44 => 
  array (
    'prinsip' => 'KAO',
    'level' => 3,
    'order_no' => 1,
    'point_check' => 'Ada skillmap operator, skillnya bisa diketahui.',
    'guidance' => 'Skillmap operator ada dan sudah diupdated dan ada approval
Metode :
dilakukan pengecekan saat operator yang sedang bekerja apakah ada skillmap atau tidak (kondisi skill sudah masuk standard masing-masing proses yang sedang dikerjakan)',
  ),
  45 => 
  array (
    'prinsip' => 'KAO',
    'level' => 3,
    'order_no' => 2,
    'point_check' => 'Foreman melakukan patrol setiap hari terhadap kepatuhan operator bekerja sesuai dengan edukasi.',
    'guidance' => 'Terdapat aktivitas pengecekan kepatuhan operator 
Metode :
1. Terdapat schedule pengecekan ke operator (jika dalam hari Atasan tidak bisa melakukan pengecekan ke semua operator)
2. Dilihat hasil pengecekan aktual oleh atasan (berdasarkan checksheet yang ada)
3. Apabila ada abnormal/temuan dari hasil pengecekan maka dilakukan action dan record',
  ),
  46 => 
  array (
    'prinsip' => 'KAO',
    'level' => 3,
    'order_no' => 3,
    'point_check' => 'Apabila ada problem, langsung dibimbing dan edukasi pada saat itu.',
    'guidance' => 'Atasan melakukan bimbingan/Edukasi pada:
1. Jika ada temuan terhadap hasil pengecekan operator misalkan : jika tidak bisa ikut WI maka perlu adanya bimbingan dan edukasi lagi terhadap operator tersebut (disertakan record/bukti dari temuan sampai dengan aktivitas bimbingan/edukasinya)
2. Jika ada feedback problem dari internal/customer maka perlu diberikan informasi atau edukasi lagi ke operator terhadap problem tersebut (disertakan record/bukti dari temuan sampai dengan aktivitas bimbingan/edukasinya)',
  ),
);

        foreach ($items as $item) {
            LevelingCheckItem::updateOrCreate(
                [
                    'prinsip'  => $item['prinsip'],
                    'level'    => $item['level'],
                    'order_no' => $item['order_no'],
                ],
                [
                    'point_check' => $item['point_check'],
                    'guidance'    => $item['guidance'],
                    'is_active'   => true,
                ]
            );
        }

        Cache::forget('leveling_active_check_items_v1');
    }
}
