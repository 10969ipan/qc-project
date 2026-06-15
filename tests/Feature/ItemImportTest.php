<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Plant;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ItemImportTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $plant;

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
    }

    /** @test */
    public function it_can_download_import_template()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.items.import-template'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename="template_master_data_item.xlsx"');
    }

    /** @test */
    public function it_can_import_new_items_and_create_categories()
    {
        $this->actingAs($this->admin);

        // Create temporary Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Nama Item', 'Kategori', 'Customer', 'Nomor Part', 'Kode SAP', 'Cavity', 'Standar Berat', 'SCT Plating', 'Defects'
        ];
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        // Add 1 new item row
        $row = [
            'NEW IMPORTED ITEM', 'NEW CATEGORY', 'AHM', 'PN-NEW-999', 'SAP999', 2, '120.5', '', 'Kotor, Scratch'
        ];
        foreach ($row as $colIndex => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $val);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'import_test');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $file = new UploadedFile(
            $tempPath,
            'test_import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->post(route('admin.items.import'), [
            'file' => $file,
            'plant' => $this->plant->code
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert Category was created
        $category = Category::withoutGlobalScope('plant')
            ->where('plant_id', $this->plant->id)
            ->where('name', 'NEW CATEGORY')
            ->first();
        $this->assertNotNull($category);

        // Assert Item was created
        $item = Item::withoutGlobalScope('plant')
            ->where('plant_id', $this->plant->id)
            ->where('name', 'NEW IMPORTED ITEM')
            ->first();
        $this->assertNotNull($item);
        $this->assertEquals('PN-NEW-999', $item->part_number);
        $this->assertEquals('SAP999', $item->sap_code);
        $this->assertEquals(2, $item->cavity);
        $this->assertEquals(['Kotor', 'Scratch'], $item->defects);

        @unlink($tempPath);
    }

    /** @test */
    public function it_updates_existing_items_on_match()
    {
        $this->actingAs($this->admin);

        // Create existing item
        $category = Category::create(['plant_id' => $this->plant->id, 'name' => 'EXISTING CAT']);
        $item = Item::create([
            'plant_id' => $this->plant->id,
            'name' => 'OLD NAME',
            'category_id' => $category->id,
            'part_number' => 'PN-MATCH-123',
            'sap_code' => 'SAP-MATCH-123',
            'cavity' => 1,
            'weight_standard' => '50.0'
        ]);

        // Excel file matching on part_number, but modifying other fields
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Nama Item', 'Kategori', 'Customer', 'Nomor Part', 'Kode SAP', 'Cavity', 'Standar Berat', 'SCT Plating', 'Defects'
        ];
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        // Row matches 'PN-MATCH-123', updates name to 'UPDATED NAME' and cavity to 4
        $row = [
            'UPDATED NAME', 'EXISTING CAT', 'YIMM', 'PN-MATCH-123', 'SAP-MATCH-123', 4, '55.5', '', 'Silver'
        ];
        foreach ($row as $colIndex => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $val);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'import_test');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $file = new UploadedFile(
            $tempPath,
            'test_import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->post(route('admin.items.import'), [
            'file' => $file,
            'plant' => $this->plant->code
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert existing item was updated
        $updatedItem = Item::withoutGlobalScope('plant')->find($item->id);
        $this->assertEquals('UPDATED NAME', $updatedItem->name);
        $this->assertEquals(4, $updatedItem->cavity);
        $this->assertEquals('55.5', $updatedItem->weight_standard);
        $this->assertEquals(['Silver'], $updatedItem->defects);

        @unlink($tempPath);
    }
}
