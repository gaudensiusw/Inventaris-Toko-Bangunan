<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Optional filtering by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Optional search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->get();

        $stats = [
            'total' => User::count(),
            'active' => User::where('aktif', 1)->count(),
            'supervisor' => User::where('role', 'supervisor')->count(),
            'operator' => User::where('role', 'operator')->count(),
        ];

        return view('account.index', compact('users', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'in:owner,supervisor,operator,gudang'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $baseUsername = explode('@', $request->email)[0];
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $username,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'aktif' => 1,
        ]);

        return response()->json(['success' => true, 'message' => 'Akun berhasil ditambahkan.']);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (auth()->user()->role === 'supervisor' && $user->role === 'owner') {
            abort(403, 'Supervisor tidak dapat mengubah/menghapus akun Owner');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:owner,supervisor,operator,gudang'],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['string', 'min:6'];
        }

        $request->validate($rules);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['success' => true, 'message' => 'Akun berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'], 403);
        }

        if ($user->role === 'owner' && auth()->user()->role !== 'owner') {
            abort(403, 'Supervisor tidak dapat mengubah/menghapus akun Owner');
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'Akun berhasil dihapus.']);
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.'], 403);
        }

        if ($user->role === 'owner' && auth()->user()->role !== 'owner') {
            abort(403, 'Supervisor tidak dapat mengubah/menghapus akun Owner');
        }

        $user->aktif = !$user->aktif;
        $user->save();

        return response()->json(['success' => true, 'aktif' => $user->aktif]);
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        
        if (auth()->user()->role === 'supervisor' && $user->role === 'owner') {
            abort(403, 'Supervisor tidak dapat mengubah/menghapus akun Owner');
        }

        // Generate an 8-character random alphanumeric string
        $newPassword = Str::random(8);

        $user->password = Hash::make($newPassword);
        $user->save();

        return response()->json([
            'success' => true, 
            'message' => 'Password berhasil direset.', 
            'password' => $newPassword
        ]);
    }


}
