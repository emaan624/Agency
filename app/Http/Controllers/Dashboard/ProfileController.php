<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\KycVerification;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $referralLink = route('register') . '?ref=' . $user->referral_code;
        return view('dashboard.profile.edit', compact('user', 'referralLink'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'timezone' => 'nullable|string|max:50',
        ]);

        auth()->user()->update($request->only('name', 'phone', 'timezone'));
        return back()->with('success', 'Profile updated.');
    }

    public function uploadKyc(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:passport,id_card,driver_license',
            'front' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $user = auth()->user();
        $frontPath = $request->file('front')->store('kyc/' . $user->id, 'local');
        $backPath = $request->hasFile('back') ? $request->file('back')->store('kyc/' . $user->id, 'local') : null;

        KycVerification::create([
            'user_id' => $user->id,
            'document_type' => $request->document_type,
            'front_path' => $frontPath,
            'back_path' => $backPath,
        ]);

        $user->update(['kyc_status' => 'pending']);
        return back()->with('success', 'KYC documents submitted for review.');
    }
}
