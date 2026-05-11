<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index()
    {
        // Require role 'owner' or 'admin'
        if (!in_array(auth()->user()->role, ['owner', 'admin'])) {
            abort(403, 'Unauthorized access.');
        }

        $logs = Activity::with('causer')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('audit_log.index', compact('logs'));
    }

    public function destroy($id)
    {
        // Only owner can delete
        if (auth()->user()->role !== 'owner') {
            abort(403, 'Unauthorized action.');
        }

        $log = Activity::findOrFail($id);
        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Audit log berhasil dihapus'
        ]);
    }
}
