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

        $item = Item::create([
            'name' => 'Test Item Part',
            'part_number' => 'PN-TEST-001',
            'category_id' => $this->categories['INPROSES']->id,
            'plant_id' => $this->plant->id,
            'customer' => 'Test Customer'
        ]);
        
        // Mocking item for Cross Cut 
        Item::create([
            'name' => 'Test Item Cross Cut',
            'part_number' => 'PN-CC-001',
            'category_id' => $this->categories['Cross Cut Plating']->id,
            'plant_id' => $this->plant->id
        ]);

        Item::create([
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
        $item = Item::where('category_id', $this->categories['INPROSES']->id)->first();

        $data = [
            'item_id' => $item->id,
            'plant' => $this->plant->id,
            'date' => now()->toDateString(),
            'shift' => 'Shift 1',
            'code_machine' => 'MC-01',
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
            if (session()->has('errors')) {
                dump(session('errors')->getBag('default')->all());
            }
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
        $item = Item::where('category_id', $this->categories['INPROSES']->id)->first();

        $data = [
            'item_id' => $item->id,
            'plant' => $this->plant->id,
            'date' => now()->toDateString(),
            'shift' => 'Shift 1',
            'code_machine' => 'MC-01',
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
        
        $item = Item::where('category_id', $this->categories['Cross Cut Plating']->id)->first();

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
        
        $item = Item::where('category_id', $this->categories['Cross Cut Painting']->id)->first();

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
}
