<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonitoringPpExcelExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithStyles
{
    use RegistersEventListeners;

    /**
     * @param  array<int, string>  $headings
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @param  array<int, array<string, string>>  $hyperlinks
     */
    public function __construct(
        protected array $headings,
        protected array $rows,
        protected array $hyperlinks = [],
    ) {}

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings));
        $lastRow = max(count($this->rows) + 1, 2);

        $sheet->freezePane('A2');
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F3D5E'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);

        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    public static function afterSheet(\Maatwebsite\Excel\Events\AfterSheet $event): void
    {
        /** @var self $export */
        $export = $event->getConcernable();
        $sheet = $event->sheet->getDelegate();
        $headerWidthMap = [
            'Nama Barang' => 34,
            'Keterangan' => 32,
            'Model/Spesifikasi' => 34,
        ];

        foreach ($export->headings as $index => $heading) {
            $column = Coordinate::stringFromColumnIndex($index + 1);

            if (isset($headerWidthMap[$heading])) {
                $sheet->getColumnDimension($column)->setAutoSize(false);
                $sheet->getColumnDimension($column)->setWidth($headerWidthMap[$heading]);
            }
        }

        foreach ($export->hyperlinks as $rowNumber => $links) {
            foreach ($links as $column => $url) {
                if ($url === '') {
                    continue;
                }

                $cell = "{$column}{$rowNumber}";
                $sheet->getCell($cell)->getHyperlink()->setUrl($url);
                $sheet->getStyle($cell)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => '2563EB'],
                        'underline' => true,
                    ],
                ]);
            }
        }
    }
}
