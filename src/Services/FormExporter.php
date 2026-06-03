<?php

namespace Hyderkamran\FormBuilder\Services;

use Dompdf\Dompdf;
use Hyderkamran\FormBuilder\Models\Form;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FormExporter
{
    public static function toCSV(int|string $formIdentifier)
    {
        $form = static::resolveForm($formIdentifier);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$form->slug}-submissions.csv",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, post-check=0',
            'Expires' => '0',
        ];

        $columns = collect($form->fields)->pluck('name')->toArray();
        array_unshift($columns, 'Submission ID', 'Created At');

        $callback = function () use ($form, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($form->submissions as $submission) {
                $row = [
                    $submission->id,
                    $submission->created_at->format('Y-m-d H:i:s'),
                ];

                foreach ($form->fields as $field) {
                    $row[] = static::formatValue($submission->data[$field->name] ?? '');
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public static function toExcel(int|string $formIdentifier)
    {
        $form = static::resolveForm($formIdentifier);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columns = collect($form->fields)->pluck('name')->toArray();
        array_unshift($columns, 'Submission ID', 'Created At');

        $sheet->fromArray($columns, null, 'A1');

        $rowIndex = 2;
        foreach ($form->submissions as $submission) {
            $row = [
                $submission->id,
                $submission->created_at->format('Y-m-d H:i:s'),
            ];

            foreach ($form->fields as $field) {
                $row[] = static::formatValue($submission->data[$field->name] ?? '');
            }

            $sheet->fromArray($row, null, "A{$rowIndex}");
            $rowIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = "{$form->slug}-submissions.xlsx";
        $tempFile = tempnam(sys_get_temp_dir(), 'form-export-');
        $writer->save($tempFile);

        return Response::download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    public static function toPdf(int|string $formIdentifier)
    {
        $form = static::resolveForm($formIdentifier);

        $html = view('form-builder::exports.pdf', compact('form'))->render();

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return Response::make($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename={$form->slug}-submissions.pdf",
        ]);
    }

    protected static function resolveForm(int|string $formIdentifier): Form
    {
        return is_numeric($formIdentifier)
            ? Form::with('submissions', 'fields')->findOrFail($formIdentifier)
            : Form::with('submissions', 'fields')->where('slug', $formIdentifier)->firstOrFail();
    }

    protected static function formatValue(mixed $value): string
    {
        if (is_array($value)) {
            return collect($value)->flatten()->implode(', ');
        }

        return (string) $value;
    }
}
