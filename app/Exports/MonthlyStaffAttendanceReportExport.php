<?php

namespace App\Exports;

use App\Models\Staff;
use App\Models\StaffAttendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MonthlyStaffAttendanceReportExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithEvents
{
    protected string $month;
    protected array $weekdays = [];
    protected array $rows = [];
    protected array $roleRows = [];

    public function __construct(string $month)
    {
        $this->month = $month;
        $this->buildData();
    }

    protected function buildData(): void
    {
        $startOfMonth = Carbon::parse($this->month . '-01');
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = now()->startOfDay();

        for ($day = $startOfMonth->copy(); $day->lte($endOfMonth); $day->addDay()) {
            if (!in_array($day->dayOfWeek, [0, 6])) {
                $this->weekdays[] = $day->copy();
            }
        }

        $staffMembers = Staff::orderBy('role')->orderBy('name')->get();
        $grouped = $staffMembers->groupBy('role');

        $attendances = StaffAttendance::whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->groupBy('staff_id')
            ->map(fn($items) => $items->keyBy(fn($a) => Carbon::parse($a->date)->format('Y-m-d')));

        $rowIndex = 2;
        foreach ($grouped as $role => $membersInRole) {
            $roleRow = array_fill(0, 1 + count($this->weekdays), '');
            $roleRow[0] = '▸ ' . ucfirst($role);
            $this->rows[] = $roleRow;
            $this->roleRows[] = $rowIndex;
            $rowIndex++;

            foreach ($membersInRole as $member) {
                $row = [$member->name];
                $memberAttendances = $attendances->get($member->id, collect());

                foreach ($this->weekdays as $day) {
                    $dateStr = $day->format('Y-m-d');
                    $isFuture = $day->gt($today);

                    if ($isFuture) {
                        $row[] = '';
                    } elseif ($memberAttendances && $memberAttendances->has($dateStr)) {
                        $status = $memberAttendances->get($dateStr)->status;
                        $row[] = $status === 'presente' ? 'Presente' : 'Ausente';
                    } else {
                        $row[] = 'Sin registro';
                    }
                }

                $this->rows[] = $row;
                $rowIndex++;
            }
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        $heads = ['Nombre'];
        foreach ($this->weekdays as $day) {
            $heads[] = $day->locale('es')->isoFormat('ddd D');
        }
        return $heads;
    }

    public function title(): string
    {
        return 'Reporte ' . Carbon::parse($this->month . '-01')->locale('es')->isoFormat('MMMM YYYY');
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setAutoSize(true);

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1 + count($this->weekdays));
        foreach (range('B', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setWidth(10);
        }

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalRows = 1 + count($this->rows);
                $lastColIndex = 1 + count($this->weekdays);
                $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIndex);

                // Style role header rows
                foreach ($this->roleRows as $row) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '4338CA'],
                        ],
                    ]);
                }

                // Color-code status cells
                for ($row = 2; $row <= $totalRows; $row++) {
                    if (in_array($row, $this->roleRows)) continue;

                    for ($col = 2; $col <= $lastColIndex; $col++) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $cell = $sheet->getCell("{$colLetter}{$row}");
                        $value = $cell->getValue();

                        $color = match ($value) {
                            'Presente' => '16A34A',
                            'Ausente' => 'DC2626',
                            'Sin registro' => '9CA3AF',
                            default => null,
                        };

                        if ($color) {
                            $sheet->getStyle("{$colLetter}{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => $color],
                                ],
                                'font' => ['color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                            ]);
                        }
                    }
                }

                // Borders
                $sheet->getStyle("A1:{$lastCol}{$totalRows}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
