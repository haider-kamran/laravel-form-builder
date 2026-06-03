<?php

namespace Hyderkamran\FormBuilder\Enums;

enum FieldType: string
{
    // Basic
    case Text     = 'text';
    case Email    = 'email';
    case Number   = 'number';
    case Textarea = 'textarea';
    case Password = 'password';
    case Hidden   = 'hidden';
    case Url      = 'url';
    case Tel      = 'tel';

    // Choice
    case Select   = 'select';
    case Radio    = 'radio';
    case Checkbox = 'checkbox';
    case Toggle   = 'toggle';

    // Date / Time
    case Date     = 'date';
    case Datetime = 'datetime';
    case Time     = 'time';

    // Media
    case File     = 'file';
    case Image    = 'image';

    // Special
    case Range    = 'range';
    case Color    = 'color';
    case Rating   = 'rating';

    // PRO
    case Repeater = 'repeater';

    /**
     * Human-readable label for admin UI.
     */
    public function label(): string
    {
        return match($this) {
            self::Text     => 'Text',
            self::Email    => 'Email',
            self::Number   => 'Number',
            self::Textarea => 'Textarea',
            self::Password => 'Password',
            self::Hidden   => 'Hidden',
            self::Url      => 'URL',
            self::Tel      => 'Phone / Tel',
            self::Select   => 'Dropdown (Select)',
            self::Radio    => 'Radio Buttons',
            self::Checkbox => 'Checkbox',
            self::Toggle   => 'Toggle Switch',
            self::Date     => 'Date',
            self::Datetime => 'Date & Time',
            self::Time     => 'Time',
            self::File     => 'File Upload',
            self::Image    => 'Image Upload',
            self::Range    => 'Range Slider',
            self::Color    => 'Color Picker',
            self::Rating   => 'Star Rating',
            self::Repeater => 'Repeater (Pro)',
        };
    }

    /**
     * Icon class (Bootstrap Icons) for admin UI.
     */
    public function icon(): string
    {
        return match($this) {
            self::Text     => 'bi-input-cursor-text',
            self::Email    => 'bi-envelope',
            self::Number   => 'bi-hash',
            self::Textarea => 'bi-text-paragraph',
            self::Password => 'bi-shield-lock',
            self::Hidden   => 'bi-eye-slash',
            self::Url      => 'bi-link-45deg',
            self::Tel      => 'bi-telephone',
            self::Select   => 'bi-chevron-down',
            self::Radio    => 'bi-record-circle',
            self::Checkbox => 'bi-check2-square',
            self::Toggle   => 'bi-toggle-on',
            self::Date     => 'bi-calendar',
            self::Datetime => 'bi-calendar-event',
            self::Time     => 'bi-clock',
            self::File     => 'bi-paperclip',
            self::Image    => 'bi-image',
            self::Range    => 'bi-sliders',
            self::Color    => 'bi-palette',
            self::Rating   => 'bi-star',
            self::Repeater => 'bi-table',
        };
    }

    /**
     * Group for the admin field palette.
     */
    public function group(): string
    {
        return match($this) {
            self::Text, self::Email, self::Number, self::Textarea,
            self::Password, self::Hidden, self::Url, self::Tel => 'Basic',

            self::Select, self::Radio, self::Checkbox, self::Toggle => 'Choice',

            self::Date, self::Datetime, self::Time => 'Date & Time',

            self::File, self::Image => 'Media',

            self::Range, self::Color, self::Rating => 'Special',

            self::Repeater => 'Pro',
        };
    }

    /**
     * All cases grouped by their group label.
     *
     * @return array<string, FieldType[]>
     */
    public static function grouped(): array
    {
        $groups = [];
        foreach (self::cases() as $case) {
            $groups[$case->group()][] = $case;
        }
        return $groups;
    }
}
