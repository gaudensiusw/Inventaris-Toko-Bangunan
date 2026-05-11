<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // Require role 'owner' or 'admin'
        if (!in_array(auth()->user()->role, ['owner', 'admin'])) {
            abort(403, 'Unauthorized access.');
        }

        $query = Activity::with('causer');

        if ($request->filled('search')) {
            $query->whereHas('causer', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('causer', function($q) use ($request) {
                $q->where('role', $request->role);
            });
        }

        if ($request->filled('modul')) {
            $query->where('subject_type', 'like', '%' . $request->modul . '%');
        }

        if ($request->filled('aksi')) {
            $query->where(function($q) use ($request) {
                $q->where('event', $request->aksi)
                  ->orWhere('description', $request->aksi);
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

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
