<?php

namespace Hyderkamran\FormBuilder\Tests\Feature;

use Hyderkamran\FormBuilder\Models\Form;
use Hyderkamran\FormBuilder\Models\FormField;
use Hyderkamran\FormBuilder\Models\FormSubmission;
use Hyderkamran\FormBuilder\Services\FormExporter;
use Hyderkamran\FormBuilder\Enums\FieldType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase;

class FormBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [\Hyderkamran\FormBuilder\FormBuilderServiceProvider::class];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    // ─────────────────────────────────────────────────────────────
    // Model Tests
    // ─────────────────────────────────────────────────────────────

    public function test_can_create_a_form(): void
    {
        $form = Form::create([
            'title'     => 'Contact Us',
            'slug'      => 'contact-us',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('forms', ['slug' => 'contact-us']);
        $this->assertTrue($form->is_active);
    }

    public function test_can_create_form_fields(): void
    {
        $form = Form::create(['title' => 'Test Form', 'slug' => 'test-form']);

        FormField::create([
            'form_id'    => $form->id,
            'label'      => 'Full Name',
            'name'       => 'name',
            'type'       => FieldType::Text->value,
            'is_required'=> true,
            'order'      => 0,
        ]);

        $this->assertDatabaseHas('form_fields', [
            'form_id' => $form->id,
            'name'    => 'name',
            'type'    => 'text',
        ]);
    }

    public function test_form_has_fields_relation(): void
    {
        $form = Form::create(['title' => 'Test Form', 'slug' => 'test-form']);
        FormField::create(['form_id' => $form->id, 'label' => 'A', 'name' => 'a', 'type' => 'text', 'order' => 0]);
        FormField::create(['form_id' => $form->id, 'label' => 'B', 'name' => 'b', 'type' => 'text', 'order' => 1]);
        FormField::create(['form_id' => $form->id, 'label' => 'C', 'name' => 'c', 'type' => 'text', 'order' => 2]);

        $this->assertCount(3, $form->fresh()->fields);
    }

    // ─────────────────────────────────────────────────────────────
    // Submission Tests
    // ─────────────────────────────────────────────────────────────

    public function test_can_submit_form_via_api(): void
    {
        $form = Form::create(['title' => 'Test', 'slug' => 'test-form', 'is_active' => true]);
        FormField::create([
            'form_id' => $form->id, 'label' => 'Name', 'name' => 'name',
            'type' => 'text', 'is_required' => true, 'order' => 0,
        ]);

        $response = $this->postJson("/api/form-builder/forms/{$form->slug}/submit", [
            'name' => 'John Doe',
        ]);

        $response->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('form_submissions', ['form_id' => $form->id]);
    }

    public function test_submission_fails_validation_for_required_fields(): void
    {
        $form = Form::create(['title' => 'Test', 'slug' => 'required-test', 'is_active' => true]);
        FormField::create([
            'form_id' => $form->id, 'label' => 'Email', 'name' => 'email',
            'type' => 'email', 'is_required' => true, 'order' => 0,
        ]);

        $response = $this->postJson("/api/form-builder/forms/{$form->slug}/submit", []);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_inactive_form_rejects_submissions(): void
    {
        $form = Form::create(['title' => 'Inactive', 'slug' => 'inactive-form', 'is_active' => false]);

        $response = $this->postJson("/api/form-builder/forms/{$form->slug}/submit", []);

        $response->assertStatus(404);
    }

    // ─────────────────────────────────────────────────────────────
    // File Upload Tests
    // ─────────────────────────────────────────────────────────────

    public function test_can_upload_file_via_form_submission(): void
    {
        Storage::fake('public');

        $form = Form::create(['title' => 'Upload', 'slug' => 'upload-form', 'is_active' => true]);
        FormField::create([
            'form_id' => $form->id, 'label' => 'CV', 'name' => 'cv',
            'type' => 'file', 'is_required' => true, 'order' => 0,
        ]);

        $response = $this->postJson("/api/form-builder/forms/{$form->slug}/submit", [
            'cv' => UploadedFile::fake()->create('cv.pdf', 500, 'application/pdf'),
        ]);

        $response->assertStatus(200);
        $submission = FormSubmission::first();
        $this->assertNotNull($submission->data['cv']);
        Storage::disk('public')->assertExists($submission->data['cv']);
    }

    // ─────────────────────────────────────────────────────────────
    // Rendering Tests
    // ─────────────────────────────────────────────────────────────

    public function test_form_renders_html(): void
    {
        $form = Form::create(['title' => 'Contact', 'slug' => 'contact', 'is_active' => true]);
        FormField::create([
            'form_id' => $form->id, 'label' => 'Name', 'name' => 'name',
            'type' => 'text', 'is_required' => true, 'order' => 0,
        ]);

        $html = \Hyderkamran\FormBuilder\Facades\FormBuilder::render('contact');

        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('name="name"', $html);
    }

    public function test_inactive_form_renders_empty_string(): void
    {
        Form::create(['title' => 'Hidden', 'slug' => 'hidden-form', 'is_active' => false]);

        $html = \Hyderkamran\FormBuilder\Facades\FormBuilder::render('hidden-form');

        $this->assertSame('', $html);
    }

    // ─────────────────────────────────────────────────────────────
    // Export Tests
    // ─────────────────────────────────────────────────────────────

    public function test_csv_export_returns_correct_headers(): void
    {
        $form = Form::create(['title' => 'Export', 'slug' => 'export-form', 'is_active' => true]);
        FormField::create([
            'form_id' => $form->id, 'label' => 'Name', 'name' => 'name',
            'type' => 'text', 'is_required' => false, 'order' => 0,
        ]);
        FormSubmission::create(['form_id' => $form->id, 'data' => ['name' => 'Alice']]);

        $response = FormExporter::toCSV($form->id);

        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
    }

    // ─────────────────────────────────────────────────────────────
    // FieldType Enum Tests
    // ─────────────────────────────────────────────────────────────

    public function test_field_type_enum_has_all_types(): void
    {
        $cases = FieldType::cases();

        $this->assertNotEmpty($cases);
        $this->assertContains('text',     array_column($cases, 'value'));
        $this->assertContains('repeater', array_column($cases, 'value'));
        $this->assertContains('rating',   array_column($cases, 'value'));
    }

    public function test_field_type_grouped_returns_array(): void
    {
        $groups = FieldType::grouped();

        $this->assertArrayHasKey('Basic',   $groups);
        $this->assertArrayHasKey('Choice',  $groups);
        $this->assertArrayHasKey('Pro',     $groups);
    }

    // ─────────────────────────────────────────────────────────────
    // Admin API Tests
    // ─────────────────────────────────────────────────────────────

    public function test_admin_index_returns_200(): void
    {
        $response = $this->get('/forms/admin');
        $response->assertStatus(200);
    }

    public function test_admin_can_create_form(): void
    {
        $response = $this->post('/forms/admin', [
            'title'     => 'New Test Form',
            'is_active' => '1',
            'fields'    => json_encode([
                ['label' => 'Email', 'name' => 'email', 'type' => 'email', 'required' => true],
            ]),
        ]);

        $this->assertDatabaseHas('forms', ['title' => 'New Test Form']);
        $response->assertRedirect();
    }
}
