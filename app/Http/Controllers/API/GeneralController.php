<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Services;
use App\Models\Doctors;

class GeneralController extends Controller
{
    public function getspeciality()
    {
        $data = Services::select('id', 'name', 'icon')->get();
        if (count($data) > 0) {
            foreach ($data as $d) {
                // Count doctors matching this department/specialty ID
                $d->total_doctors = Doctors::where("department_id", $d->id)->count();
                $d->icon = asset("upload/services") . '/' . ($d->icon ?? 'default.png');
            }
            $response['success'] = "1";
            $response['register'] = "Speciality List";
            $response['data'] = $data;
        } else {
            $response['success'] = "0";
            $response['register'] = "Speciality Not Found";
            $response['data'] = [];
        }

        return response()->json($response, 200);
    }
}