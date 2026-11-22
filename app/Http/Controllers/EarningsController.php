<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EarningsCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EarningsController extends Controller
{
    public function __construct(private EarningsCalculator $calculator)
    {
    }

    /**
     * Earnings for the signed-in person; admins also see every earner.
     */
    public function index(Request $request)
    {
        [$from, $to, $month] = $this->period($request);
        $user = $request->user();

        $staff = null;
        if ($user->isAdmin()) {
            $totals = $this->calculator->totalsByUser($from, $to);
            $staff = User::with('role')
                ->whereHas('role', fn ($q) => $q->whereIn('name', config('earnings.earning_roles')))
                ->orderBy('name')
                ->get()
                ->map(fn (User $u) => [
                    'user' => $u,
                    'summary' => $this->calculator->summarise($totals->get($u->id, 0)),
                ]);
        }

        return view('earnings.index', $this->personData($user, $from, $to, $month) + [
            'staff' => $staff,
            'viewingOther' => false,
        ]);
    }

    /**
     * Admin view of one person's earnings.
     */
    public function show(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin() || $request->user()->id === $user->id, 403);

        [$from, $to, $month] = $this->period($request);

        return view('earnings.index', $this->personData($user, $from, $to, $month) + [
            'staff' => null,
            'viewingOther' => $request->user()->id !== $user->id,
        ]);
    }

    private function personData(User $user, Carbon $from, Carbon $to, string $month): array
    {
        $daily = $this->calculator->dailyCounts($user->id, $from, $to);

        return [
            'person' => $user,
            'month' => $month,
            'monthLabel' => $from->format('F Y'),
            'summary' => $this->calculator->summarise($daily->sum()),
            'today' => $this->calculator->summarise(
                $daily->get(now()->toDateString(), 0)
            ),
            'daily' => $daily,
            'entriesPerDay' => $this->calculator->entriesPerDay(),
            'ratePerDay' => $this->calculator->ratePerDay(),
            'currency' => config('earnings.currency'),
        ];
    }

    /**
     * Selected month (?month=YYYY-MM), defaulting to the current month.
     */
    private function period(Request $request): array
    {
        $month = $request->query('month');
        if (! is_string($month) || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $from = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
        $to = $from->copy()->endOfMonth();

        return [$from, $to, $month];
    }
}
