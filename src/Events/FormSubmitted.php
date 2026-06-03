<?php

namespace Hyderkamran\FormBuilder\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Hyderkamran\FormBuilder\Models\FormSubmission;
use Hyderkamran\FormBuilder\Models\Form;

class FormSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Form $form;
    public FormSubmission $submission;

    public function __construct(Form $form, FormSubmission $submission)
    {
        $this->form = $form;
        $this->submission = $submission;
    }
}
