<?php

namespace Hyderkamran\FormBuilder\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Hyderkamran\FormBuilder\Models\Form;
use Hyderkamran\FormBuilder\Models\FormSubmission;

class FormSubmittedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Form $form;
    public FormSubmission $submission;

    public function __construct(Form $form, FormSubmission $submission)
    {
        $this->form = $form;
        $this->submission = $submission;
    }

    public function build()
    {
        return $this->subject(sprintf('New submission for %s', $this->form->title))
            ->view('form-builder::emails.submission')
            ->with([
                'form' => $this->form,
                'submission' => $this->submission,
            ]);
    }
}
