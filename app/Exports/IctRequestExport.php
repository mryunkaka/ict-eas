<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IctRequestExport implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    use RegistersEventListeners;

    /**
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @param  array<int, array<string, string>>  $hyperlinks
     */
    public function __construct(
        protected array $rows,
        protected array $hyperlinks,
    ) {}

    public function headings(): array
    {
        return [
            'No',
            'Jenis Barang',
            'Nama Barang',
            'Merk',
            'Jumlah',
            'Harga',
            'Total',
            'Keterangan',
            'Site',
            'Foto',
            'Vendor1',
            'Vendor2',
            'Vendor3',
        ];
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 18,
            'C' => 36,
            'D' => 24,
            'E' => 10,
            'F' => 16,
            'G' => 18,
            'H' => 48,
            'I' => 18,
            'J' => 18,
            'K' => 22,
            'L' => 22,
            'M' => 22,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
        $lastRow = max(count($this->rows) + 1, 2);

        $sheet->freezePane('A2');
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A5F'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
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
        $sheet->getStyle("E2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    public static function afterSheet(\Maatwebsite\Excel\Events\AfterSheet $event): void
    {
        /** @var self $export */
        $export = $event->getConcernable();
        $sheet = $event->sheet->getDelegate();

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
