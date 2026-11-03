<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReferralSetting;
use App\Models\ReferralReward;
use App\Models\RedeemedReward;
use App\Models\Setting;
use Illuminate\Support\Facades\Session;

class AdminReferralController extends Controller
{
    public function settings()
    {
        $settingsData = ReferralSetting::all()->keyBy('key');
        $setting = Setting::first(); // General settings for layout
        return view('admin.referrals.settings', compact('settingsData', 'setting'));
    }

    public function updateSettings(Request $request)
    {
        $inputs = $request->except('_token');

        foreach ($inputs as $key => $value) {
            ReferralSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Session::flash('message', 'Referral settings updated successfully!');
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function rewardsIndex()
    {
        $rewards = ReferralReward::latest()->get();
        $setting = Setting::first();
        return view('admin.referrals.rewards.index', compact('rewards', 'setting'));
    }

    public function rewardsCreate()
    {
        return view('admin.referrals.rewards.create');
    }

    public function rewardsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_required' => 'required|integer|min:1',
            'type' => 'required|in:wallet,custom',
            'value' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        ReferralReward::create([
            'name' => $request->name,
            'description' => $request->description,
            'points_required' => $request->points_required,
            'type' => $request->type,
            'value' => $request->type == 'wallet' ? $request->value : null,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        Session::flash('message', 'Reward created successfully!');
        Session::flash('alert-class', 'alert-success');
        return redirect()->route('admin.referrals.rewards.index');
    }

    public function rewardsEdit(ReferralReward $reward)
    {
        return view('admin.referrals.rewards.edit', compact('reward'));
    }

    public function rewardsUpdate(Request $request, ReferralReward $reward)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_required' => 'required|integer|min:1',
            'type' => 'required|in:wallet,custom',
            'value' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $reward->update([
            'name' => $request->name,
            'description' => $request->description,
            'points_required' => $request->points_required,
            'type' => $request->type,
            'value' => $request->type == 'wallet' ? $request->value : null,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        Session::flash('message', 'Reward updated successfully!');
        Session::flash('alert-class', 'alert-success');
        return redirect()->route('admin.referrals.rewards.index');
    }

    public function rewardsDestroy(ReferralReward $reward)
    {
        $reward->delete();
        Session::flash('message', 'Reward deleted successfully!');
        Session::flash('alert-class', 'alert-success');
        return redirect()->route('admin.referrals.rewards.index');
    }

    public function history()
    {
        $redeemed = RedeemedReward::with(['user', 'reward'])->latest()->paginate(20);
        $setting = Setting::first();
        return view('admin.referrals.history', compact('redeemed', 'setting'));
    }
}
