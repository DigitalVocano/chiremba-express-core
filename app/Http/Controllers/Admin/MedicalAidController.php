<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MedicalAidSociety;
use Illuminate\Support\Facades\Session;

class MedicalAidController extends Controller
{
    public function index()
    {
        $societies = MedicalAidSociety::orderBy('name', 'ASC')->get();
        return view('admin.medical_aid.default', compact('societies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
        ]);

        $society = new MedicalAidSociety();
        $society->name = $request->name;
        $society->code = strtoupper($request->code ?: substr($request->name, 0, 8));
        $society->description = $request->description;
        $society->is_active = $request->has('is_active') ? 1 : 0;

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/medical_aid/'), $filename);
            $society->logo = $filename;
        }

        $society->save();

        Session::flash('message', 'Medical Aid Society added successfully.');
        Session::flash('alert-class', 'alert-success');

        return redirect()->back();
    }

    public function edit($id)
    {
        $society = MedicalAidSociety::findOrFail($id);
        return response()->json(['success' => true, 'data' => $society]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:medical_aid_societies,id',
            'name' => 'required|string|max:255',
        ]);

        $society = MedicalAidSociety::findOrFail($request->id);
        $society->name = $request->name;
        $society->code = strtoupper($request->code ?: substr($request->name, 0, 8));
        $society->description = $request->description;
        $society->is_active = $request->has('is_active') ? 1 : 0;

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('upload/medical_aid/'), $filename);
            $society->logo = $filename;
        }

        $society->save();

        Session::flash('message', 'Medical Aid Society updated successfully.');
        Session::flash('alert-class', 'alert-success');

        return redirect()->back();
    }

    public function destroy($id)
    {
        $society = MedicalAidSociety::findOrFail($id);
        $society->delete();

        Session::flash('message', 'Medical Aid Society deleted successfully.');
        Session::flash('alert-class', 'alert-success');

        return redirect()->back();
    }
}