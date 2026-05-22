<?php
namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExportService
{
    private string $title;
    private array $headers;
    private array $rows;

    public function __construct(string $title, array $headers, array $rows)
    {
        $this->title = $title;
        $this->headers = $headers;
        $this->rows = $rows;
    }

    public function pdf(): void
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $html = $this->buildHtml();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->title) . '.pdf';
        header('Content-Type: application/pdf');
        header("Content-Disposition: inline; filename=\"{$filename}\"");
        echo $dompdf->output();
        exit;
    }

    public function excel(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($this->title, 0, 31));

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a1a2e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];

        $col = 1;
        foreach ($this->headers as $header) {
            $sheet->setCellValueByColumnAndRow($col, 1, $header);
            $col++;
        }
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);

        $rowNum = 2;
        foreach ($this->rows as $row) {
            $col = 1;
            foreach ($row as $value) {
                $cell = $sheet->getCellByColumnAndRow($col, $rowNum);
                $cell->setValue($value);
                $col++;
            }
            $rowNum++;
        }

        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->title) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function buildHtml(): string
    {
        $appName = APP_NAME;
        $date = date('d/m/Y H:i');
        $html = <<<HTML
        <!DOCTYPE html>
        <html><head><meta charset="UTF-8"><title>{$this->title}</title>
        <style>
            body { font-family: 'Helvetica', Arial, sans-serif; font-size: 10pt; margin: 20px; }
            .header { text-align: center; border-bottom: 2px solid #1a1a2e; padding-bottom: 10px; margin-bottom: 20px; }
            .header h2 { color: #1a1a2e; margin: 0; }
            .header p { color: #666; margin: 2px 0; font-size: 9pt; }
            table { width: 100%; border-collapse: collapse; font-size: 9pt; }
            th { background: #1a1a2e; color: white; padding: 6px 8px; text-align: left; }
            td { padding: 4px 8px; border-bottom: 1px solid #ddd; }
            tr:nth-child(even) { background: #f8f9fa; }
            .footer { text-align: center; color: #999; font-size: 8pt; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; }
        </style>
        </head><body>
            <div class="header">
                <h2>{$appName}</h2>
                <p>{$this->title}</p>
                <p>Generado: {$date}</p>
            </div>
            <table>
                <thead><tr>
        HTML;

        foreach ($this->headers as $header) {
            $html .= "<th>" . htmlspecialchars($header) . "</th>";
        }
        $html .= "</tr></thead><tbody>";

        foreach ($this->rows as $row) {
            $html .= "<tr>";
            foreach ($row as $cell) {
                $html .= "<td>" . htmlspecialchars((string)$cell) . "</td>";
            }
            $html .= "</tr>";
        }

        $html .= "</tbody></table>";
        $html .= '<div class="footer">© ' . date('Y') . " {$appName} — Documento generado por el sistema</div>";
        $html .= "</body></html>";
        return $html;
    }
}
