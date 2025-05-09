<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // Make sure this import is present
use App\Models\Locker;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\LockerReservation;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;


class AdminLockerController extends Controller // Make sure it extends Controller
{
    /**
     * Constructor to ensure admin authentication
     */
    public function __construct()
    {
        // This line is causing the error
        // $this->middleware('auth:admin');
        
        // Since you're already using auth:admin in your routes, you don't need this line
        // Just remove it completely
    }
    
    /**
     * Display the admin dashboard with lockers
     */
    public function dashboard()
    {
        $lockers = Locker::orderBy('number')->get();
        return view('admin.dashboard', compact('lockers'));
    }
    
    public function show(Locker $locker)
{
    $locker->load('user');

    $reservation = LockerReservation::where('locker_id', $locker->id)
        ->latest()
        ->first(); // Get latest reservation (active OR pending)

        return response()->json([
            'id' => $locker->id,
            'number' => $locker->number,
            'user' => $locker->user ? [
                'id' => $locker->user->id,
                'name' => $locker->user->name,
                'email' => $locker->user->email,
            ] : null,
            'status' => optional($reservation)->status,
            'reserved_at' => optional($reservation)->reserved_at,
            'reserved_until' => optional($reservation)->reserved_until,
            'time_remaining' => $reservation && $reservation->reserved_until
                ? now()->diffInSeconds($reservation->reserved_until, false)
                : 0,
            // 🧠 FIXED THIS LINE 👇
           'price' => $reservation && $reservation->payment ? $reservation->payment : (
    $reservation && $reservation->reserved_at && $reservation->reserved_until
        ? max(ceil(Carbon::parse($reservation->reserved_at)->floatDiffInHours(Carbon::parse($reservation->reserved_until))) * 10, 0)
        : 0
),
'payment_status' => $reservation->payment_status ?? null, // ✅ Add this line
            'note' => $locker->note ,
        ]);        
}

public function approve(Request $request, Locker $locker)
{
    $request->validate([
        'hours' => 'required|numeric|min:1|max:24',
    ]);

    // ✅ Explicitly cast to integer
    $hours = (int) $request->input('hours');

    // ✅ Create Carbon instance first, then use addHours on it
    $reservedAt = Carbon::now();
    $reservedUntil = $reservedAt->copy()->addHours($hours);

    $reservation = LockerReservation::where('locker_id', $locker->id)
        ->where('status', 'pending')
        ->latest()
        ->first();

    if (!$reservation) {
        return back()->with('error', 'No pending reservation found.');
    }

    $reservation->update([
        'reserved_at' => $reservedAt,
        'reserved_until' => $reservedUntil,
        'status' => 'active',
        'payment_status' => 'Unpaid',
        'payment' => $hours * 10,
    ]);

    $locker->update([
        'is_reserved' => true,
    ]);

    return back()->with('success', 'Reservation approved successfully.');
}
public function decline(Request $request, Locker $locker)
{
    $reservation = LockerReservation::where('locker_id', $locker->id)
        ->where('status', 'pending')
        ->latest()
        ->first();

    if (!$reservation) {
        return back()->with('error', 'No pending reservation to decline.');
    }

    // Update reservation status
    $reservation->update([
        'status' => 'declined',
        'reserved_at' => null,
        'reserved_until' => null,
    ]);

    // Reset locker
    $locker->update([
        'user_id' => null,
        'name' => 'Locker #' . $locker->number,
        'note' => null,
        'is_reserved' => false,
    ]);

    return back()->with('success', 'Reservation declined successfully.');
}

public function index()
{
    $lockers = Locker::with('user')->get();
    $pendingLockers = Locker::where('status', 'pending')->with('user')->get();

    return view('admin.dashboard', compact('lockers', 'pendingLockers'));
}
public function markAsPaid(Locker $locker)
{
    $reservation = LockerReservation::where('locker_id', $locker->id)
                    ->where('status', 'active')
                    ->latest('reserved_at')
                    ->first();

    if ($reservation) {
        $reservation->update(['payment_status' => 'Paid']);

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Reservation marked as paid.');
    }

    if (request()->ajax()) {
        return response()->json(['success' => false, 'message' => 'No active reservation found.']);
    }

    return redirect()->route('admin.dashboard')->with('error', 'No active reservation found.');
}
public function endReservation(Locker $locker)
{
    $reservation = LockerReservation::where('locker_id', $locker->id)
        ->where('status', 'active')
        ->latest('reserved_at')
        ->first();

    if (!$reservation) {
        return response()->json(['success' => false, 'message' => 'No active reservation found.']);
    }

    // Mark the reservation as ended
    $reservation->update([
        'status' => 'ended',
        'reserved_until' => now(), // end it now
    ]);

    // Free up the locker
    $locker->update([
        'user_id' => null,
        'name' => 'Locker #' . $locker->number,
        'note' => null,
        'is_reserved' => false,
    ]);

    return response()->json(['success' => true, 'message' => 'Reservation ended.']);
}
public function reservationHistory(Request $request)
{
    $query = LockerReservation::with(['locker', 'user'])->orderByDesc('reserved_at');

    // Filters
    if ($request->filled('user')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->user . '%');
        });
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('from') && $request->filled('to')) {
        $query->whereBetween('reserved_at', [$request->from, $request->to]);
    }

    $reservations = $query->paginate(10);

    return view('admin.reservations.index', compact('reservations'));
}
public function exportPDF()
{
    $reservations = \App\Models\LockerReservation::with('user', 'locker')->get();

    $pdf = PDF::loadView('admin.reservations_pdf', compact('reservations'))
              ->setPaper('A4', 'landscape');

    return $pdf->download('reservation-history.pdf');
}
}