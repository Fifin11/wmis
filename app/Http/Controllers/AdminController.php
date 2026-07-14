<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CitizenReport;
use App\Models\RecyclingSubmission;
use App\Models\RecyclingLeaderboard;
use App\Models\SystemLog;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateReportStatusRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ─────────────────────────────────────────────
    // DRIVER MANAGEMENT
    // ─────────────────────────────────────────────

    /**
     * Store a new driver account (FR-ADM: Driver Registration).
     * Only admin can create driver accounts from the portal.
     */
    public function storeDriver(StoreDriverRequest $request)
    {
        $driver = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => 'Driver',
            'language' => 'en',
        ]);

        SystemLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Create Driver Account',
            'entity_type' => 'User',
            'entity_id'   => $driver->id,
            'new_values'  => ['name' => $driver->name, 'email' => $driver->email, 'role' => 'Driver'],
        ]);

        return redirect()->back()->with('success', "Driver account for '{$driver->name}' created successfully.");
    }

    /**
     * Delete a driver account.
     * Cascades will handle route_assignments + pickup_logs via FK constraints.
     */
    public function destroyDriver($id)
    {
        $driver = User::where('id', $id)->where('role', 'Driver')->firstOrFail();

        SystemLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Delete Driver Account',
            'entity_type' => 'User',
            'entity_id'   => $driver->id,
            'old_values'  => ['name' => $driver->name, 'email' => $driver->email],
        ]);

        $driver->delete();

        return redirect()->back()->with('success', "Driver '{$driver->name}' has been archived.");
    }

    /**
     * Restore a deleted driver account.
     */
    public function restoreDriver($id)
    {
        $driver = User::onlyTrashed()->where('id', $id)->where('role', 'Driver')->firstOrFail();

        $driver->restore();

        SystemLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Restore Driver Account',
            'entity_type' => 'User',
            'entity_id'   => $driver->id,
            'new_values'  => ['name' => $driver->name, 'email' => $driver->email],
        ]);

        return redirect()->back()->with('success', "Driver '{$driver->name}' has been restored successfully.");
    }

    // ─────────────────────────────────────────────
    // RECYCLING SUBMISSION MODERATION
    // ─────────────────────────────────────────────

    /**
     * Approve a pending recycling submission.
     * On approval, points are committed to the leaderboard.
     */
    public function approveRecycling(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $submission = RecyclingSubmission::where('id', $id)
            ->where('status', 'Pending')
            ->firstOrFail();

        // Mark submission as approved
        $submission->update([
            'status'      => 'Approved',
            'admin_note'  => $request->admin_note ?? 'Verified and approved by municipal officer.',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Commit points to leaderboard
        $leaderboard = RecyclingLeaderboard::firstOrCreate(
            [
                'citizen_id' => $submission->citizen_id,
                'month'      => date('F'),
                'year'       => intval(date('Y')),
            ],
            ['points' => 0]
        );
        $leaderboard->points += $submission->claimed_points;
        $leaderboard->save();

        // Recalculate ranks for all participants this month
        $all = RecyclingLeaderboard::where('month', date('F'))
            ->where('year', date('Y'))
            ->orderBy('points', 'desc')
            ->get();
        foreach ($all as $index => $row) {
            $row->rank = $index + 1;
            $row->save();
        }

        SystemLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Approve Recycling Submission',
            'entity_type' => 'RecyclingSubmission',
            'entity_id'   => $submission->id,
            'new_values'  => ['status' => 'Approved', 'points_awarded' => $submission->claimed_points],
        ]);

        return redirect()->back()->with('success', "Recycling submission #{$id} approved. {$submission->claimed_points} eco-points awarded to {$submission->citizen->name}.");
    }

    /**
     * Reject a pending recycling submission with a mandatory reason.
     */
    public function rejectRecycling(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        $submission = RecyclingSubmission::where('id', $id)
            ->where('status', 'Pending')
            ->firstOrFail();

        $submission->update([
            'status'      => 'Rejected',
            'admin_note'  => $request->admin_note,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        SystemLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Reject Recycling Submission',
            'entity_type' => 'RecyclingSubmission',
            'entity_id'   => $submission->id,
            'new_values'  => ['status' => 'Rejected', 'reason' => $request->admin_note],
        ]);

        return redirect()->back()->with('success', "Recycling submission #{$id} has been rejected.");
    }

    // ─────────────────────────────────────────────
    // INCIDENT REPORT MANAGEMENT
    // ─────────────────────────────────────────────

    /**
     * Update the status of a citizen incident report.
     */
    public function updateReportStatus(UpdateReportStatusRequest $request, $id)
    {
        $report = CitizenReport::findOrFail($id);
        $oldStatus = $report->status;

        $report->update(['status' => $request->status]);

        SystemLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Update Incident Report Status',
            'entity_type' => 'CitizenReport',
            'entity_id'   => $report->id,
            'old_values'  => ['status' => $oldStatus],
            'new_values'  => ['status' => $request->status],
        ]);

        return redirect()->back()->with('success', "Incident Report #$id status updated to '{$request->status}'.");
    }

    // Export Incident Reports to CSV
    public function exportReportsCsv()
    {
        $fileName = 'citizen_reports_export_' . date('Y_m_d_His') . '.csv';
        $reports = CitizenReport::with('citizen')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Citizen Name', 'Issue Type', 'Status', 'Date Reported'];

        $callback = function() use($reports, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($reports as $report) {
                $row['ID']  = $report->id;
                $row['Citizen Name'] = $report->citizen ? $report->citizen->name : 'Anonymous';
                $row['Issue Type'] = $report->issue_type;
                $row['Status']  = $report->status;
                $row['Date Reported']  = $report->created_at->format('Y-m-d H:i');

                fputcsv($file, array($row['ID'], $row['Citizen Name'], $row['Issue Type'], $row['Status'], $row['Date Reported']));
            }

            fclose($file);
        };

        // Audit log action
        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'Export Reports CSV',
            'entity_type' => 'CitizenReport',
            'entity_id' => 0,
        ]);

        return response()->stream($callback, 200, $headers);
    }

    // Export Incident Reports to Print (PDF alternative)
    public function exportReportsPrint()
    {
        $reports = CitizenReport::with('citizen')->latest()->get();

        // Audit log action
        SystemLog::create([
            'user_id' => Auth::id(),
            'action' => 'Print Reports',
            'entity_type' => 'CitizenReport',
            'entity_id' => 0,
        ]);

        return view('admin.exports.reports_print', compact('reports'));
    }
}
