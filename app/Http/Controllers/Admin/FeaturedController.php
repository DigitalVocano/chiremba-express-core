<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctors;
use App\Models\Setting;
use Illuminate\Support\Facades\Session;
use DataTables;
use Carbon\Carbon;

class FeaturedController extends Controller
{
    /**
     * Display a listing of doctors and pharmacies for featured management.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        if (Session::has('admin')) {
            $setting = Setting::find(1);
            return view("admin.featured.index", compact('setting'));
        } else {
            return redirect("admin");
        }
    }

    /**
     * Returns data for DataTables for featured management.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function featuredTable()
    {
        $profiles = Doctors::query(); // Use query builder for efficiency

        return DataTables::of($profiles)
            ->editColumn('id', function ($profile) {
                return $profile->id;
            })
            ->editColumn('name', function ($profile) {
                return $profile->name;
            })
            ->editColumn('profile_type', function ($profile) {
                return $profile->profile_type == 1 ? __('Doctor') : __('Pharmacy');
            })
            ->editColumn('featured_status', function ($profile) {
                if ($profile->featured && $profile->featured_until && Carbon::parse($profile->featured_until)->isFuture()) {
                    return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">' . __('Featured') . '</span>' .
                           '<br><span class="text-xs text-gray-500 dark:text-gray-400">' . __('Until') . ': ' . Carbon::parse($profile->featured_until)->format('Y-m-d') . '</span>';
                } else {
                    return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">' . __('Not Featured') . '</span>';
                }
            })
            ->editColumn('action', function ($profile) {
                $featureBtn = '<button type="button" class="inline-flex items-center px-3 py-1.5 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150" onclick="openFeatureModal(' . $profile->id . ', \'' . $profile->profile_type . '\')">' . __('Feature') . '</button>';
                $unfeatureBtn = '<button type="button" class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150" onclick="unfeatureProfile(' . $profile->id . ', \'' . $profile->profile_type . '\')">' . __('Unfeature') . '</button>';

                if ($profile->featured && $profile->featured_until && Carbon::parse($profile->featured_until)->isFuture()) {
                    return $unfeatureBtn;
                } else {
                    return $featureBtn;
                }
            })
            ->rawColumns(['featured_status', 'action'])
            ->make(true);
    }

    /**
     * Mark a doctor/pharmacy as featured for a specified number of days.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function feature(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:doctors,id',
            'days' => 'required|integer|min:1',
        ]);

        $profile = Doctors::findOrFail($request->id);
        $profile->featured = 1;
        $profile->featured_until = Carbon::now()->addDays($request->days);
        $profile->save();

        Session::flash('message', __('Profile featured successfully!'));
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    /**
     * Remove featured status from a doctor/pharmacy.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unfeature(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:doctors,id',
        ]);

        $profile = Doctors::findOrFail($request->id);
        $profile->update(['featured' => 0, 'featured_until' => null]);

        return response()->json(['success' => true, 'message' => __('Profile unfeatured successfully!')]);
    }
}
