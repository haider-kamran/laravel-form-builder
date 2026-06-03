<?php

namespace Hyderkamran\FormBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Hyderkamran\FormBuilder\Models\Form;
use Hyderkamran\FormBuilder\Services\FormSubmissionService;
use Illuminate\Support\Facades\Cache;

class FormController extends Controller
{
    protected FormSubmissionService $submissionService;

    public function __construct(FormSubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    /**
     * API: Return form schema as JSON.
     */
    public function show(string $slug)
    {
        $form = Cache::remember("form:{$slug}", 3600, fn () =>
            Form::with('fields')->where('slug', $slug)->first()
        );

        if (!$form || !$form->is_active) {
            return response()->json(['message' => 'Form not found or inactive.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $form,
        ]);
    }

    /**
     * Web + API: Handle form submission.
     */
    public function submit(Request $request, string $slug)
    {
        $form = Cache::remember("form:{$slug}", 3600, fn () =>
            Form::with('fields')->where('slug', $slug)->first()
        );

        if (!$form || !$form->is_active) {
            $msg = 'This form is not available.';
            return $request->wantsJson()
                ? response()->json(['message' => $msg], 404)
                : back()->withErrors(['form' => $msg]);
        }

        $submission = $this->submissionService->handleSubmission($form, $request);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Form submitted successfully.',
                'data'    => $submission,
            ]);
        }

        return back()->with('form_success', 'Your submission has been received. Thank you!');
    }

    /**
     * Embeddable iframe view.
     */
    public function embed(string $slug)
    {
        $form = Cache::remember("form:{$slug}", 3600, fn () =>
            Form::with('fields')->where('slug', $slug)->first()
        );

        if (!$form || !$form->is_active) {
            return response('<p style="font-family:sans-serif;color:#999;text-align:center;padding:40px">Form unavailable.</p>', 404);
        }

        return view('form-builder::form-embed', compact('form'));
    }
}
