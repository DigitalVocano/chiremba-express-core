<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AiCreditPackage;
use App\Models\Setting;
use Illuminate\Support\Facades\Session;
use DataTables;

class AiCreditController extends Controller
{
    /**
     * Display the management page for AI credit packages.
     */
    public function index()
    {
        if (Session::has('admin')) {
            $setting = Setting::first();
            return view("admin.ai_credits.index", compact('setting'));
        }
        return redirect("admin");
    }

    /**
     * Process DataTables ajax request for credit packages.
     */
    public function table()
    {
        $packages = AiCreditPackage::query(); 
        $setting = Setting::first();
        $currency = $setting->currency ?? '$';

        return DataTables::of($packages)
            ->editColumn('price', function ($package) use ($currency) {
                return $currency . number_format($package->price, 2);
            })
            ->editColumn('is_active', function ($package) {
                if ($package->is_active) {
                    return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100">' . __("Active") . '</span>';
                }
                return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-700 dark:text-red-100">' . __("Inactive") . '</span>';
            })
            ->addColumn('action', function ($package) {
                $editButton = '<button type="button" @click=\'$dispatch("open-modal", ' . json_encode($package) . ')\' class="p-2 rounded-full text-blue-600 hover:bg-blue-100 dark:hover:bg-gray-700 text-xl" title="Edit"><i class="uil uil-pen"></i></button>';
                $deleteUrl = route('admin.ai_credits.destroy', $package->id);
                $deleteButton = '<button type="button" data-url="' . $deleteUrl . '" class="delete-btn p-2 rounded-full text-red-600 hover:bg-red-100 dark:hover:bg-gray-700 text-xl" title="Delete"><i class="uil uil-trash-alt"></i></button>';
                return '<div class="flex items-center space-x-2">' . $editButton . $deleteButton . '</div>';
            })
            ->rawColumns(['is_active', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created credit package.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'credits' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        AiCreditPackage::create([
            'name' => $request->name,
            'credits' => $request->credits,
            'price' => $request->price,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return response()->json(['success' => true, 'message' => __('AI Credit Package created successfully.')]);
    }

    /**
     * Update an existing credit package.
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:ai_credit_packages,id',
            'name' => 'required|string|max:255',
            'credits' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $package = AiCreditPackage::findOrFail($request->id);
        $package->update([
            'name' => $request->name,
            'credits' => $request->credits,
            'price' => $request->price,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return response()->json(['success' => true, 'message' => __('AI Credit Package updated successfully.')]);
    }

    /**
     * Delete a credit package.
     */
    public function destroy(AiCreditPackage $package)
    {
        try {
            $package->delete();
            return response()->json(['success' => true, 'message' => __('AI Credit Package deleted successfully.')]);
        } catch (\Exception $e) {
            \Log::error('AI Credit Package deletion failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => __('Failed to delete the package.')], 500);
        }
    }
}