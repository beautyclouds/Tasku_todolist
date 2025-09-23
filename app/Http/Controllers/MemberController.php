<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function index()
    {
        // Ambil semua user KECUALI yang sedang login
        $users = User::where('id', '!=', auth()->id())
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status, // otomatis "Aktif" / "Tidak Aktif"
                ];
            });

        return Inertia::render('member/Member', [
            'users' => $users,
        ]);
    }


    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('member.index')->with('success', 'User berhasil dihapus');
    }
}
