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
        return DoctorReel::query()->with(['doctor', 'audioMessage', 'greetingCard'])
            ->when(trim($this->filters['search'] ?? ''), function (Builder $query, string $search): void {
                $term = '%'.trim($search).'%';
                $query->where(function (Builder $inner) use ($term): void {
                    $inner->where('reference_id', 'like', $term)
                        ->orWhereHas('doctor', fn (Builder $doctor) => $doctor
                            ->where('name', 'like', $term)
                            ->orWhere('speciality', 'like', $term)
                            ->orWhere('city', 'like', $term));
                });
            })
            ->when($this->filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($this->filters['media_type'] ?? null, function (Builder $query, string $type): void {
                match ($type) {
                    'video' => $query->where('content_type', 'video')->whereNotNull('original_video'),
                    'audio' => $query->where('content_type', 'audio')->whereHas('audioMessage'),
                    'card' => $query->where('content_type', 'card')->whereHas('greetingCard'),
                    default => null,
                };
            })
            ->when(
                ! ($this->filters['media_type'] ?? null) && ($this->filters['content_type'] ?? null),
                fn (Builder $query) => $query->where('content_type', $this->filters['content_type'])
            )
            ->when($this->filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($this->filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('created_at', '<=', $to))
            ->latest();
    }

    public function headings(): array
    {
        $type = $this->filters['media_type'] ?? $this->filters['content_type'] ?? null;
        $mediaHeadings = match ($type) {
            'video' => ['Original Video S3 URL', 'Generated Video S3 URL'],
            'audio' => ['Original Audio S3 URL', 'Generated Audio S3 URL'],
            'card' => ['Original S3 URL', 'Generated Card S3 URL'],
            default => ['Original Media S3 URL', 'Generated Media S3 URL'],
        };

        return array_merge(['Reference ID', 'Name', 'City', 'Type', 'Status', 'Downloads', 'Submitted At', 'Completed At'], $mediaHeadings);
    }

    public function map($reel): array
    {
        $type = $this->filters['media_type'] ?? $reel->content_type;
        $audioUrl = $reel->audioMessage?->original_audio_url ?? $reel->original_audio_url;
        $generatedAudioUrl = $reel->audioMessage?->generated_video_url;
        $cardUrl = $reel->greetingCard?->generated_card_url ?? $reel->generated_card_url;
        [$originalUrl, $generatedUrl] = match ($type) {
            'audio' => [$audioUrl, $generatedAudioUrl],
            'card' => [null, $cardUrl],
            default => [$reel->original_video_url, $reel->generated_video_url],
        };

        $createdAt = $reel->created_at?->copy()->timezone('Asia/Kolkata')->format('d M Y, h:i A');
        $completedAt = $reel->processing_completed_at?->copy()->timezone('Asia/Kolkata')->format('d M Y, h:i A');

        return [$reel->reference_id, $reel->doctor_name, $reel->city, $type, $reel->status, $reel->download_count, $createdAt, $completedAt, $originalUrl, $generatedUrl];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:J1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF245337');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());

        return [1 => ['font' => ['bold' => true]]];
    }
}
