<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        // Ambil input pencarian dari query string (?search=)
        $search = $request->query('search');

        // Ambil semua user kecuali yang sedang login, dan filter jika ada pencarian
        $users = User::where('id', '!=', auth()->id())
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                ];
            });

        return Inertia::render('member/Member', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('member.index')->with('success', 'User berhasil dihapus');
    }
}
