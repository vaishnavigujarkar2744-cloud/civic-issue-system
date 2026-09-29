<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Department;

class AnalyticsService
{
    public function summary(): array
    {
        $avgHours = Complaint::whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as h')
            ->value('h');

        return [
            'total' => Complaint::count(),
            'pending' => Complaint::where('status', 'pending')->count(),
            'in_progress' => Complaint::whereIn('status', ['assigned', 'in_progress'])->count(),
            'resolved' => Complaint::where('status', 'resolved')->count(),
            'rejected' => Complaint::where('status', 'rejected')->count(),
            'avg_resolution_hours' => round((float) $avgHours, 1),
        ];
    }

    public function byDepartment(): array
    {
        return Department::withCount([
            'complaints',
            'complaints as resolved_count' => fn ($q) => $q->where('status', 'resolved'),
        ])->orderBy('name')->get()->map(fn ($d) => [
            'department' => $d->name,
            'total' => $d->complaints_count,
            'resolved' => $d->resolved_count,
            'resolution_rate' => $d->complaints_count
                ? round($d->resolved_count / $d->complaints_count * 100, 1) : 0,
        ])->all();
    }

    public function byStatus(): array
    {
        $counts = Complaint::selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');

        return collect(Complaint::STATUS_LABELS)->map(fn ($label, $key) => [
            'status' => $label,
            'count' => (int) ($counts[$key] ?? 0),
        ])->values()->all();
    }

    public function monthly(int $months = 6): array
    {
        $rows = Complaint::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as m, COUNT(*) as c")
            ->where('created_at', '>=', now()->subMonths($months - 1)->startOfMonth())
            ->groupBy('m')->orderBy('m')->pluck('c', 'm');

        $out = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $out[] = ['month' => now()->subMonths($i)->format('M Y'), 'count' => (int) ($rows[$key] ?? 0)];
        }
        return $out;
    }

    public function all(): array
    {
        return [
            'summary' => $this->summary(),
            'by_department' => $this->byDepartment(),
            'by_status' => $this->byStatus(),
            'monthly' => $this->monthly(),
        ];
    }
}
