<?php

namespace App\Http\Controllers\Warehouse\Kitchen;

use App\Http\Controllers\Controller;
use App\Models\KitchenShift;
use App\Models\KitchenTimeLog;
use App\Models\KitchenLocation;
use App\Models\WareUser;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KitchenStaffScheduleController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        
        $shifts = KitchenShift::with(['staff', 'kitchenLocation'])
            ->whereDate('shift_date', $selectedDate)
            ->orderBy('start_time')
            ->get();

        $timeLogs = KitchenTimeLog::with(['staff', 'kitchenLocation'])
            ->whereDate('clock_in_at', $selectedDate)
            ->orderByDesc('clock_in_at')
            ->get();

        $activeClockIns = KitchenTimeLog::with(['staff', 'kitchenLocation'])
            ->whereNull('clock_out_at')
            ->get();

        $staffMembers = WareUser::orderBy('name')->get();
        $kitchenLocations = KitchenLocation::all();

        // Calculate summary stats
        $stats = [
            'total_scheduled_today' => KitchenShift::whereDate('shift_date', $selectedDate)->count(),
            'currently_clocked_in' => $activeClockIns->count(),
            'completed_shifts_today' => KitchenShift::whereDate('shift_date', $selectedDate)->where('status', 'completed')->count(),
            'total_hours_today' => KitchenTimeLog::whereDate('clock_in_at', $selectedDate)->sum('total_hours'),
        ];

        return view('warehouse.kitchen.staff.index', compact(
            'shifts',
            'timeLogs',
            'activeClockIns',
            'staffMembers',
            'kitchenLocations',
            'selectedDate',
            'stats'
        ));
    }

    public function storeShift(Request $request)
    {
        $validated = $request->validate([
            'ware_user_id'        => 'required|exists:ware_users,id',
            'kitchen_location_id' => 'required|exists:kitchen_locations,id',
            'shift_date'          => 'required|date',
            'start_time'          => 'required',
            'end_time'            => 'required',
            'station'             => 'required|string|max:100',
            'notes'               => 'nullable|string',
        ]);

        KitchenShift::create($validated);

        return redirect()->back()->with('success', 'Shift scheduled successfully.');
    }

    public function updateShift(Request $request, KitchenShift $shift)
    {
        $validated = $request->validate([
            'status'     => 'required|in:scheduled,completed,absent,cancelled',
            'station'    => 'nullable|string|max:100',
            'start_time' => 'nullable',
            'end_time'   => 'nullable',
            'notes'      => 'nullable|string',
        ]);

        $shift->update($validated);

        return redirect()->back()->with('success', 'Shift updated successfully.');
    }

    public function destroyShift(KitchenShift $shift)
    {
        $shift->delete();
        return redirect()->back()->with('success', 'Shift removed.');
    }

    public function clockIn(Request $request)
    {
        $validated = $request->validate([
            'ware_user_id'        => 'required|exists:ware_users,id',
            'kitchen_location_id' => 'nullable|exists:kitchen_locations,id',
            'notes'               => 'nullable|string',
        ]);

        // Check if user is already clocked in without a clock out
        $existing = KitchenTimeLog::where('ware_user_id', $validated['ware_user_id'])
            ->whereNull('clock_out_at')
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'This staff member is already clocked in.');
        }

        KitchenTimeLog::create([
            'ware_user_id'        => $validated['ware_user_id'],
            'kitchen_location_id' => $validated['kitchen_location_id'] ?? null,
            'clock_in_at'         => Carbon::now(),
            'status'              => 'clocked_in',
            'notes'               => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Staff clocked in successfully.');
    }

    public function clockOut(Request $request, KitchenTimeLog $timeLog)
    {
        $now = Carbon::now();
        $clockIn = Carbon::parse($timeLog->clock_in_at);
        $diffMinutes = max(0, $clockIn->diffInMinutes($now) - ($request->input('break_minutes', 0)));
        $totalHours = round($diffMinutes / 60, 2);

        $timeLog->update([
            'clock_out_at'  => $now,
            'break_minutes' => $request->input('break_minutes', 0),
            'total_hours'   => $totalHours,
            'status'        => 'clocked_out',
            'notes'         => $request->input('notes', $timeLog->notes),
        ]);

        return redirect()->back()->with('success', "Clocked out successfully. Total recorded hours: {$totalHours}h.");
    }
}
