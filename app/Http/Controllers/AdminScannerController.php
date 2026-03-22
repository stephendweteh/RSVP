<?php

namespace App\Http\Controllers;

use App\Models\Rsvp;
use App\Services\RsvpCheckInQrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminScannerController extends Controller
{
    public function index(): View
    {
        return view('admin.scanner.index');
    }

    public function admit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payload' => ['required', 'string', 'max:2000'],
        ]);

        $token = RsvpCheckInQrService::normalizeTokenFromScan($validated['payload']);
        if ($token === '' || strlen($token) > 64) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid code. Try again or enter the check-in link manually.',
            ], 422);
        }

        $rsvp = Rsvp::query()
            ->where('check_in_token', $token)
            ->where('status', Rsvp::STATUS_APPROVED)
            ->first();

        if ($rsvp === null) {
            return response()->json([
                'ok' => false,
                'message' => 'No approved guest matches this code.',
            ], 404);
        }

        $already = $rsvp->checked_in_at !== null;
        if (! $already) {
            $rsvp->forceFill(['checked_in_at' => now()])->save();
        }

        $table = filled($rsvp->table_number) ? 'Table '.$rsvp->table_number : null;

        return response()->json([
            'ok' => true,
            'already_admitted' => $already,
            'guest_name' => $rsvp->name,
            'guests_count' => $rsvp->guests_count,
            'table' => $table,
            'message' => $already
                ? $rsvp->name.' was already admitted.'
                : $rsvp->name.' admitted successfully.',
        ]);
    }
}
