<?php

namespace App\Http\Controllers;

use App\Mail\RsvpDecisionAdminMail;
use App\Mail\RsvpDecisionMail;
use App\Models\Rsvp;
use App\Services\AdminNotificationRecipients;
use App\Services\RsvpSmsNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminRsvpController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $query = Rsvp::query()->orderByDesc('created_at');

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $rsvps = $query->paginate(15)->withQueryString();

        return view('admin.rsvps.index', compact('rsvps', 'status'));
    }

    public function export(Request $request): StreamedResponse
    {
        $status = $request->query('status');

        $query = Rsvp::query()->orderByDesc('created_at');

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $filename = 'rsvps_'.now()->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'id', 'name', 'phone', 'email', 'guests_count', 'attendance', 'message', 'status', 'table_number', 'created_at',
            ]);
            foreach ($query->cursor() as $rsvp) {
                fputcsv($out, [
                    $rsvp->id,
                    $rsvp->name,
                    $rsvp->phone,
                    $rsvp->email ?? '',
                    $rsvp->guests_count,
                    $rsvp->attendance,
                    $rsvp->message ?? '',
                    $rsvp->status,
                    $rsvp->table_number ?? '',
                    $rsvp->created_at?->toIso8601String() ?? '',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function approve(int $id): RedirectResponse
    {
        $result = DB::transaction(function () use ($id): array {
            $rsvp = Rsvp::query()->lockForUpdate()->findOrFail($id);

            if ($rsvp->status === Rsvp::STATUS_APPROVED) {
                return ['kind' => 'noop'];
            }

            $usedNumbers = Rsvp::query()
                ->where('status', Rsvp::STATUS_APPROVED)
                ->lockForUpdate()
                ->pluck('table_number')
                ->filter(fn ($n) => $n !== null)
                ->map(fn ($n) => (int) $n)
                ->all();

            if (count($usedNumbers) >= Rsvp::APPROVED_CAPACITY) {
                return ['kind' => 'full'];
            }

            $nextTable = null;
            for ($n = 1; $n <= Rsvp::APPROVED_CAPACITY; $n++) {
                if (! in_array($n, $usedNumbers, true)) {
                    $nextTable = $n;
                    break;
                }
            }

            if ($nextTable === null) {
                return ['kind' => 'full'];
            }

            $rsvp->update([
                'status' => Rsvp::STATUS_APPROVED,
                'table_number' => $nextTable,
            ]);

            return ['kind' => 'approved', 'rsvp' => $rsvp->fresh(), 'table' => $nextTable];
        });

        if ($result['kind'] === 'full') {
            return back()->withErrors([
                'capacity' => 'Guest list is full (100 approved RSVPs). Cannot approve more.',
            ]);
        }

        if ($result['kind'] === 'noop') {
            return back()->with('success', 'RSVP is already approved.');
        }

        $this->notifyDecision($result['rsvp'], Rsvp::STATUS_APPROVED);

        return back()->with('success', 'RSVP approved. Assigned table number '.$result['table'].'.');
    }

    public function reject(int $id): RedirectResponse
    {
        $rsvp = DB::transaction(function () use ($id) {
            $rsvp = Rsvp::query()->lockForUpdate()->findOrFail($id);
            $rsvp->update([
                'status' => Rsvp::STATUS_REJECTED,
                'table_number' => null,
            ]);

            return $rsvp->fresh();
        });

        $this->notifyDecision($rsvp, Rsvp::STATUS_REJECTED);

        return back()->with('success', 'RSVP rejected.');
    }

    private function notifyDecision(Rsvp $rsvp, string $decision): void
    {
        if (filled($rsvp->email)) {
            Mail::to($rsvp->email)->send(new RsvpDecisionMail($rsvp, $decision));
        }

        RsvpSmsNotifier::guestDecision($rsvp, $decision);

        foreach (AdminNotificationRecipients::notificationEmails() as $adminEmail) {
            Mail::to($adminEmail)->send(new RsvpDecisionAdminMail($rsvp, $decision));
        }

        RsvpSmsNotifier::adminDecision($rsvp, $decision);
    }
}
