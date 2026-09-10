<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Source;
use App\Models\Service;
use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ExcelLeadImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure permission exists
        Permission::firstOrCreate(['name' => 'view enquiries']);
        Permission::firstOrCreate(['name' => 'create enquiries']);

        $role = Role::firstOrCreate(['name' => 'Admin']);
        $role->givePermissionTo(['view enquiries', 'create enquiries']);
    }

    private function getAdminUser()
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        return $user;
    }

    public function test_guest_cannot_access_excel_import()
    {
        $response = $this->get(route('enquiries.excel.import'));
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_excel_import_page()
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get(route('enquiries.excel.import'));
        $response->assertStatus(200);
        $response->assertSee('Import Excel & CSV Leads', false);
        $response->assertSee('Download Sample Template');
    }

    public function test_sample_template_download()
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get(route('enquiries.excel.sampleTemplate'));
        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_preview_endpoint_parses_csv()
    {
        $user = $this->getAdminUser();
        Storage::fake('local');

        $csvContent = "name,phone\nRAMESH KUMAR,9876543210\nMOHAMMED ALI,9845012345\n";
        $file = UploadedFile::fake()->createWithContent('test_leads.csv', $csvContent);

        $response = $this->actingAs($user)->postJson(route('enquiries.excel.preview'), [
            'file' => $file
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'headers',
            'preview',
            'temp_path',
            'delimiter'
        ]);
        $response->assertJson([
            'status' => 'success',
            'headers' => ['name', 'phone']
        ]);
    }

    public function test_import_minimal_two_column_sheet_scenario_1()
    {
        $user = $this->getAdminUser();
        Storage::fake('local');

        $csvContent = "name,phone\nRAMESH KUMAR,9876543210\nMOHAMMED ALI,9845012345\n";
        $tempPath = 'temp/' . uniqid() . '.csv';
        Storage::put($tempPath, $csvContent);

        $source = Source::firstOrCreate(['name' => 'Telecalling']);

        $response = $this->actingAs($user)->postJson(route('enquiries.excel.process'), [
            'temp_path' => $tempPath,
            'delimiter' => ',',
            'source_id' => $source->id,
            'mapping' => [
                'name' => 'name',
                'mobile' => 'phone'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'imported' => 2,
                'updated' => 0,
            ]
        ]);

        $this->assertDatabaseHas('enquiries', [
            'name' => 'RAMESH KUMAR',
            'mobile' => '9876543210',
            'source_id' => $source->id,
            'status' => 'Open'
        ]);

        $this->assertDatabaseHas('enquiries', [
            'name' => 'MOHAMMED ALI',
            'mobile' => '9845012345',
            'source_id' => $source->id,
            'status' => 'Open'
        ]);
    }

    public function test_import_telecalling_feedback_sheet_scenario_2()
    {
        $user = $this->getAdminUser();
        Storage::fake('local');

        $csvContent = "Contact Person Name,Contact Number,call connect yes /no,feed back,feed back appointment\n"
                    . "Mr. Vipul Kumar,9945210105,no not responding,called twice no answer,2026-03-28\n"
                    . "Mr. Purushotham,7204836548,yes,asking details and brochure,2026-04-02\n";

        $tempPath = 'temp/' . uniqid() . '.csv';
        Storage::put($tempPath, $csvContent);

        $source = Source::firstOrCreate(['name' => 'Cold Calling']);

        $response = $this->actingAs($user)->postJson(route('enquiries.excel.process'), [
            'temp_path' => $tempPath,
            'delimiter' => ',',
            'source_id' => $source->id,
            'mapping' => [
                'name' => 'Contact Person Name',
                'mobile' => 'Contact Number',
                'call_status' => 'call connect yes /no',
                'feedback' => 'feed back',
                'appointment' => 'feed back appointment'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'imported' => 2,
            ]
        ]);

        $purushotham = Enquiry::where('mobile', '7204836548')->first();
        $this->assertNotNull($purushotham);
        $this->assertEquals('Mr. Purushotham', $purushotham->name);
        $this->assertStringContainsString('asking details and brochure', $purushotham->description);
        $this->assertNotNull($purushotham->next_follow_up_at);
        $this->assertCount(1, $purushotham->followUps);
        $this->assertCount(1, $purushotham->comments);
    }

    public function test_deduplication_updates_existing_without_creating_duplicates()
    {
        $user = $this->getAdminUser();
        Storage::fake('local');

        $source = Source::firstOrCreate(['name' => 'Website']);
        $existing = Enquiry::create([
            'name' => 'Existing Customer',
            'mobile' => '9998887776',
            'source_id' => $source->id,
            'status' => 'Open',
        ]);

        $csvContent = "name,phone,feedback\nExisting Customer,9998887776,Customer called back for revision\n";
        $tempPath = 'temp/' . uniqid() . '.csv';
        Storage::put($tempPath, $csvContent);

        $response = $this->actingAs($user)->postJson(route('enquiries.excel.process'), [
            'temp_path' => $tempPath,
            'delimiter' => ',',
            'source_id' => $source->id,
            'mapping' => [
                'name' => 'name',
                'mobile' => 'phone',
                'feedback' => 'feedback'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'imported' => 0,
                'updated' => 1,
            ]
        ]);

        // Total count should still be 1
        $this->assertEquals(1, Enquiry::where('mobile', '9998887776')->count());
        // Should have added a comment
        $this->assertCount(1, $existing->fresh()->comments);
    }

    public function test_enquiry_directory_filtering()
    {
        $user = $this->getAdminUser();

        $sourceA = Source::firstOrCreate(['name' => 'Source Alpha']);
        $sourceB = Source::firstOrCreate(['name' => 'Source Beta']);

        Enquiry::create(['name' => 'Alpha Lead', 'mobile' => '9111111111', 'source_id' => $sourceA->id, 'status' => 'Open']);
        Enquiry::create(['name' => 'Beta Lead', 'mobile' => '9222222222', 'source_id' => $sourceB->id, 'status' => 'Won']);

        // Filter by Source Alpha
        $responseAlpha = $this->actingAs($user)->getJson(route('enquiries.index', ['source_id' => $sourceA->id]));
        $responseAlpha->assertStatus(200);
        $dataAlpha = $responseAlpha->json('data');
        $this->assertTrue(collect($dataAlpha)->contains('mobile', '9111111111'));
        $this->assertFalse(collect($dataAlpha)->contains('mobile', '9222222222'));

        // Filter by Status Won
        $responseWon = $this->actingAs($user)->getJson(route('enquiries.index', ['status' => 'Won']));
        $responseWon->assertStatus(200);
        $dataWon = $responseWon->json('data');
        $this->assertTrue(collect($dataWon)->contains('mobile', '9222222222'));
        $this->assertFalse(collect($dataWon)->contains('mobile', '9111111111'));
    }
}
