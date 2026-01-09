<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Export Service
 * 
 * Prompt 304: Add Report Export Endpoints with Filters
 * 
 * Handles exporting data to various formats (PDF, Excel, CSV).
 */
class ExportService
{
    /**
     * Export data to the specified format.
     * 
     * @param Collection $data
     * @param string $format
     * @param string $filename
     * @param string $title
     * @return Response|StreamedResponse
     */
    public function export(Collection $data, string $format, string $filename, string $title = 'Report'): Response|StreamedResponse
    {
        return match ($format) {
            'pdf' => $this->exportToPdf($data, $filename, $title),
            'xlsx', 'excel' => $this->exportToExcel($data, $filename),
            'csv' => $this->exportToCsv($data, $filename),
            default => throw new \InvalidArgumentException("Unsupported export format: {$format}"),
        };
    }

    /**
     * Export data to PDF format.
     * 
     * @param Collection $data
     * @param string $filename
     * @param string $title
     * @return Response
     */
    public function exportToPdf(Collection $data, string $filename, string $title = 'Report'): Response
    {
        $headers = $data->isNotEmpty() ? array_keys($data->first()) : [];
        
        $html = $this->generatePdfHtml($data, $headers, $title);
        
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        return $pdf->download("{$filename}.pdf");
    }

    /**
     * Export data to Excel format.
     * 
     * @param Collection $data
     * @param string $filename
     * @return StreamedResponse
     */
    public function exportToExcel(Collection $data, string $filename): StreamedResponse
    {
        $headers = $data->isNotEmpty() ? array_keys($data->first()) : [];

        return response()->streamDownload(function () use ($data, $headers) {
            $output = fopen('php://output', 'w');
            
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            fputcsv($output, $headers, "\t");
            
            foreach ($data as $row) {
                fputcsv($output, array_values($row), "\t");
            }
            
            fclose($output);
        }, "{$filename}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}.xlsx\"",
        ]);
    }

    /**
     * Export data to CSV format.
     * 
     * @param Collection $data
     * @param string $filename
     * @return StreamedResponse
     */
    public function exportToCsv(Collection $data, string $filename): StreamedResponse
    {
        $headers = $data->isNotEmpty() ? array_keys($data->first()) : [];

        return response()->streamDownload(function () use ($data, $headers) {
            $output = fopen('php://output', 'w');
            
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            fputcsv($output, $headers);
            
            foreach ($data as $row) {
                fputcsv($output, array_values($row));
            }
            
            fclose($output);
        }, "{$filename}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }

    /**
     * Generate HTML for PDF export.
     * 
     * @param Collection $data
     * @param array $headers
     * @param string $title
     * @return string
     */
    private function generatePdfHtml(Collection $data, array $headers, string $title): string
    {
        $schoolName = config('app.name', 'Smart School');
        $generatedAt = now()->format('F j, Y \a\t g:i A');
        $totalRecords = $data->count();

        $headerRow = '';
        foreach ($headers as $header) {
            $headerRow .= "<th>{$header}</th>";
        }

        $bodyRows = '';
        foreach ($data as $row) {
            $bodyRows .= '<tr>';
            foreach ($row as $value) {
                $bodyRows .= "<td>" . htmlspecialchars((string) $value) . "</td>";
            }
            $bodyRows .= '</tr>';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #4f46e5;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            color: #4f46e5;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 14px;
            color: #666;
            font-weight: normal;
        }
        .meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 9px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #4f46e5;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid #4f46e5;
        }
        td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #e9ecef;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{$schoolName}</h1>
        <h2>{$title}</h2>
    </div>
    
    <div class="meta">
        <span>Generated: {$generatedAt}</span>
        <span>Total Records: {$totalRecords}</span>
    </div>
    
    <table>
        <thead>
            <tr>{$headerRow}</tr>
        </thead>
        <tbody>
            {$bodyRows}
        </tbody>
    </table>
    
    <div class="footer">
        <p>This report was generated by {$schoolName} Management System</p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get supported export formats.
     * 
     * @return array
     */
    public function getSupportedFormats(): array
    {
        return [
            'pdf' => 'PDF Document',
            'xlsx' => 'Excel Spreadsheet',
            'csv' => 'CSV File',
        ];
    }
}
