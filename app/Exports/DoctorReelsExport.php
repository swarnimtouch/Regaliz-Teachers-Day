<?php

namespace App\Exports;

use App\Models\DoctorReel;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorReelsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters = []) {}

    public function query(): Builder
    {
        return DoctorReel::query()
            ->when(trim($this->filters['search'] ?? ''), function (Builder $query, string $search): void {
                $term = '%'.trim($search).'%';
                $query->where(function (Builder $inner) use ($term): void {
                    $inner->where('doctor_name', 'like', $term)
                        ->orWhere('reference_id', 'like', $term)
                        ->orWhere('speciality', 'like', $term)
                        ->orWhere('city', 'like', $term);
                });
            })
            ->when($this->filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($this->filters['content_type'] ?? null, fn (Builder $query, string $type) => $query->where('content_type', $type))
            ->when($this->filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($this->filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->latest();
    }

    public function headings(): array
    {
        return ['Reference ID', 'Name', 'Speciality', 'City', 'Type', 'Status', 'Downloads', 'Submitted At', 'Completed At'];
    }

    public function map($reel): array
    {
        return [$reel->reference_id, $reel->doctor_name, $reel->speciality, $reel->city, $reel->content_type, $reel->status, $reel->download_count, $reel->created_at, $reel->processing_completed_at];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:I1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF245337');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return [1 => ['font' => ['bold' => true]]];
    }
}
