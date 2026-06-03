<?php

namespace Hyderkamran\FormBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Hyderkamran\FormBuilder\Models\Form;
use Hyderkamran\FormBuilder\Models\FormField;
use Hyderkamran\FormBuilder\Models\FormSubmission;
use Hyderkamran\FormBuilder\Services\FormExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // Forms CRUD
    // ─────────────────────────────────────────────────────────────

    public function index()
    {
        $forms = Form::withCount(['fields', 'submissions'])
                     ->orderByDesc('created_at')
                     ->get();

        return view('form-builder::admin.index', compact('forms'));
    }

    public function create()
    {
        $form = new Form();
        $form->setRelation('fields', collect());

        return view('form-builder::admin.form_edit', compact('form'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'nullable',
            'fields'      => 'nullable|string',
            'settings'    => 'nullable|string',
        ]);

        $slug = filled($data['slug'] ?? null)
            ? Str::slug($data['slug'])
            : Str::slug($data['title']);

        $form = Form::create([
            'title'       => $data['title'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'is_active'   => !empty($data['is_active']),
            'settings'    => filled($data['settings'] ?? null)
                ? json_decode($data['settings'], true)
                : null,
        ]);

        $this->syncFields($form, $data['fields'] ?? null);

        return redirect()
            ->route('form-builder.admin.edit', $form->id)
            ->with('status', 'Form created successfully.');
    }

    public function edit(int $id)
    {
        $form = Form::with('fields')->findOrFail($id);

        return view('form-builder::admin.form_edit', compact('form'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $form = Form::findOrFail($id);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'nullable',
            'fields'      => 'nullable|string',
            'settings'    => 'nullable|string',
        ]);

        $slug = filled($data['slug'] ?? null)
            ? Str::slug($data['slug'])
            : Str::slug($data['title']);

        $form->update([
            'title'       => $data['title'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'is_active'   => !empty($data['is_active']),
        ]);

        if (filled($data['settings'] ?? null)) {
            $form->settings = json_decode($data['settings'], true);
            $form->save();
        }

        $this->syncFields($form, $data['fields'] ?? null);

        return redirect()
            ->route('form-builder.admin.edit', $form->id)
            ->with('status', 'Form updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Form::findOrFail($id)->delete();

        return redirect()
            ->route('form-builder.admin.index')
            ->with('status', 'Form deleted.');
    }

    // ─────────────────────────────────────────────────────────────
    // Submissions
    // ─────────────────────────────────────────────────────────────

    public function submissions(int $id)
    {
        $form = Form::with(['fields', 'submissions'])->findOrFail($id);

        return view('form-builder::admin.submissions', compact('form'));
    }

    public function destroySubmission(int $submissionId): RedirectResponse
    {
        $submission = FormSubmission::findOrFail($submissionId);
        $formId     = $submission->form_id;
        $submission->delete();

        return redirect()
            ->route('form-builder.admin.submissions', $formId)
            ->with('status', 'Submission deleted.');
    }

    // ─────────────────────────────────────────────────────────────
    // Export
    // ─────────────────────────────────────────────────────────────

    public function export(int $id, string $format)
    {
        return match ($format) {
            'csv'  => FormExporter::toCSV($id),
            'xlsx' => FormExporter::toExcel($id),
            'pdf'  => FormExporter::toPdf($id),
            default => abort(404),
        };
    }

    // ─────────────────────────────────────────────────────────────
    // Notifications & Webhooks
    // ─────────────────────────────────────────────────────────────

    public function testWebhook(Request $request, int $id)
    {
        $form = Form::findOrFail($id);

        $url = $request->input('webhook_url')
            ?? ($form->settings['webhook_url'] ?? null);

        if (!filled($url)) {
            return response()->json(['status' => 'error', 'message' => 'Webhook URL not provided'], 422);
        }

        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(5)->post($url, [
                'event' => 'test',
                'form'  => $form->only('id', 'title', 'slug'),
            ]);

            return response()->json(['status' => 'ok', 'code' => $resp->status(), 'body' => $resp->body()]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function testEmail(Request $request, int $id)
    {
        $form       = Form::findOrFail($id);
        $recipients = $request->input('recipients')
            ? array_filter(array_map('trim', explode(',', $request->input('recipients'))))
            : (array) ($form->settings['notification_recipients'] ?? []);

        if (empty($recipients)) {
            return response()->json(['status' => 'error', 'message' => 'No recipients configured.'], 422);
        }

        $submission = new FormSubmission([
            'form_id' => $form->id,
            'data'    => ['test' => 'This is a test notification from Laravel Form Builder.'],
        ]);
        $submission->created_at = now();

        try {
            \Illuminate\Support\Facades\Mail::to($recipients)
                ->send(new \Hyderkamran\FormBuilder\Mail\FormSubmittedNotification($form, $submission));

            return response()->json(['status' => 'ok', 'message' => 'Test email sent.']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Internal
    // ─────────────────────────────────────────────────────────────

    protected function syncFields(Form $form, ?string $fieldsJson): void
    {
        if (!filled($fieldsJson)) {
            return;
        }

        $fields = json_decode($fieldsJson, true) ?? [];

        // Delete all existing and recreate in order
        $form->fields()->delete();

        foreach ($fields as $order => $f) {
            FormField::create([
                'form_id'          => $form->id,
                'label'            => $f['label'] ?? 'Field ' . ($order + 1),
                'name'             => $f['name']  ?? 'field_' . ($order + 1),
                'type'             => $f['type']  ?? 'text',
                'options'          => $f['options'] ?? null,
                'validation_rules' => $f['validation'] ?? null,
                'is_required'      => !empty($f['required']),
                'order'            => $order,
            ]);
        }
    }
}
