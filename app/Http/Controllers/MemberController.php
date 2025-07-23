<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Member;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::all();

        return Inertia::render('member/Member', [
            'members' => $members,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('photos', 'public');
        }

        Member::create($validated);

        return redirect()->back();
    }

    public function destroy($id)
    {
        $member = Member::findOrFail($id);

        // Hapus file foto dari storage jika ada
        if ($member->photo && Storage::disk('public')->exists($member->photo)) {
            Storage::disk('public')->delete($member->photo);
        }

        $member->delete();

        return redirect()->route('member.index');
    }

}
