<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Hyderkamran\FormBuilder\Models\Form;
use Hyderkamran\FormBuilder\Models\FormField;

class FormBuilderSeeder extends Seeder
{
    public function run(): void
    {
        $contactForm = Form::create([
            'title' => 'Contact Us',
            'slug' => 'contact-us',
            'description' => 'We would love to hear from you.',
            'is_active' => true,
        ]);

        FormField::insert([
            [
                'form_id' => $contactForm->id,
                'label' => 'Full Name',
                'name' => 'name',
                'type' => 'text',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'form_id' => $contactForm->id,
                'label' => 'Email Address',
                'name' => 'email',
                'type' => 'email',
                'is_required' => true,
                'order' => 2,
            ],
            [
                'form_id' => $contactForm->id,
                'label' => 'Message',
                'name' => 'message',
                'type' => 'textarea',
                'is_required' => true,
                'order' => 3,
            ]
        ]);

        $jobForm = Form::create([
            'title' => 'Job Application',
            'slug' => 'job-application',
            'description' => 'Apply for open positions.',
            'is_active' => true,
        ]);

        FormField::insert([
            [
                'form_id' => $jobForm->id,
                'label' => 'Position',
                'name' => 'position',
                'type' => 'select',
                'is_required' => true,
                'order' => 1,
                'options' => json_encode(['choices' => ['dev' => 'Developer', 'designer' => 'Designer']]),
            ],
            [
                'form_id' => $jobForm->id,
                'label' => 'Resume',
                'name' => 'resume',
                'type' => 'file',
                'is_required' => true,
                'order' => 2,
                'options' => null,
            ]
        ]);

        $surveyForm = Form::create([
            'title' => 'Customer Survey',
            'slug' => 'customer-survey',
            'description' => 'Help us improve our service.',
            'is_active' => true,
        ]);

        FormField::insert([
            [
                'form_id' => $surveyForm->id,
                'label' => 'How would you rate our service?',
                'name' => 'rating',
                'type' => 'rating',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'form_id' => $surveyForm->id,
                'label' => 'Feedback',
                'name' => 'feedback',
                'type' => 'textarea',
                'is_required' => false,
                'order' => 2,
            ]
        ]);
    }
}
