<?php

namespace App\Services;

use App\Models\Marriage;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class EarningsCalculator
{
    public function entriesPerDay(): int
    {
        return max(1, (int) config('earnings.entries_per_day', 50));
    }

    public function ratePerDay(): int
    {
        return (int) config('earnings.rate_per_day', 1000);
    }

    /**
     * Turn an entry count into paid days and earnings.
     */
    public function summarise(int $entries): array
    {
        $perDay = $this->entriesPerDay();
        $days = intdiv($entries, $perDay);
        $remainder = $entries % $perDay;

        return [
            'entries' => $entries,
            'days' => $days,
            'earnings' => $days * $this->ratePerDay(),
            'towards_next_day' => $remainder,
            'needed_for_next_day' => $perDay - $remainder,
        ];
    }

    private function countableEntries(CarbonInterface $from, CarbonInterface $to)
    {
        return Marriage::query()
            ->whereNotNull('created_by')
            ->whereBetween('created_at', [$from, $to])
            ->where(function ($q) {
                $q->whereNull('system_status')
                    ->orWhereNotIn('system_status', config('earnings.excluded_statuses', []));
            });
    }

    /**
     * Entry counts for one person, per calendar date, newest first.
     */
    public function dailyCounts(int $userId, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $this->countableEntries($from, $to)
            ->where('created_by', $userId)
            ->selectRaw('DATE(created_at) as entry_date, COUNT(*) as total')
            ->groupBy('entry_date')
            ->orderByDesc('entry_date')
            ->pluck('total', 'entry_date')
            ->map(fn ($n) => (int) $n);
    }

    /**
     * Total entry counts per person in the period, keyed by user id.
     */
    public function totalsByUser(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return $this->countableEntries($from, $to)
            ->selectRaw('created_by, COUNT(*) as total')
            ->groupBy('created_by')
            ->pluck('total', 'created_by')
            ->map(fn ($n) => (int) $n);
    }
}
