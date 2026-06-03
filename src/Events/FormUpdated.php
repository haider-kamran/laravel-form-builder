<?php

namespace Hyderkamran\FormBuilder\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Hyderkamran\FormBuilder\Models\Form;

class FormUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Form $form;

    public function __construct(Form $form)
    {
        $this->form = $form;
    }
}
