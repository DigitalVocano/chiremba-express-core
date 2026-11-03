<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Package;
use App\Models\PackageBenefit;
use App\Models\Speciality;
use App\Models\Document;
use App\Models\Schedule;
use App\Models\Family;
use App\Models\Subscription;
use App\Models\Subscriber;
use App\Models\Slot;
use App\Models\Review;
use App\Models\Appointment;
use App\Models\About;
use App\Models\Notification;
use App\Models\Prescription;
use App\Models\ReportSpam;
use App\Models\Medicines;
use App\Models\AppointmentMedicines;
use App\Models\Token;
use App\Models\ServiceOrder;

use Auth;
use Session;
use validate;
use DB;
use DataTables;
use DateTimeZone;
use DateTime;
use Hash;
use Mail;
use Illuminate\Http\Request;

class MedicineController extends Controller
{

    public function showmedicines(){
        return view("admin.medicines.default");
    }



    public function index()
    {
    	$data = Medicines::with('speciality')->get();
    	return view("admin.medicines.default",compact('data'));
    }

    public function medicinesadd1()
    {
    	return view("admin.medicines.add-medicines");
    }

    public function addmedicinessave(Request $request)
    {
        foreach($request->input('type') as $new){
            $types[]=$new;
         }
         $alltype = implode(',',$types);

	   	$data = new Medicines();
	   	// $data->image = $img_url;
	   	$data->name = $request->get("name");
	   	$data->description = $request->get("description");
	   	$data->dosage = $request->get("dosage");
        $data->medicine_type = $alltype;
	   	$data->save();

	   	$msg = __("Successfull Medicine Added");
	   	Session::flash('message',$msg);
        Session::flash('alert-class', 'alert-success');
        return redirect()->route('admin.medicines.index');
        // return $request;

    // 	return view("admin.medicines.default");
    }


     public function deletemedicines($id)
    {
    	$data = Medicines::find($id);
        $data->delete();
        $msg = __("Successfull Medicine Delete");
	   	Session::flash('message',$msg);
        Session::flash('alert-class', 'alert-success');
      return redirect()->route('admin.medicines.index');
    }

     public function medicine(Request $request){
         $query = Medicines::query();

         // Search filter
         if ($request->has('search_term') && !empty($request->search_term)) {
             $searchTerm = $request->search_term;
             $query->where(function ($q) use ($searchTerm) {
                 $q->where('name', 'like', '%' . $searchTerm . '%')
                   ->orWhere('description', 'like', '%' . $searchTerm . '%');
             });
         }

         // Sorting logic
         $sortBy = $request->get('sort_by', 'newest');
         switch ($sortBy) {
             case 'oldest':
                 $query->orderBy('created_at', 'asc');
                 break;
             case 'name_asc':
                 $query->orderBy('name', 'asc');
                 break;
             case 'name_desc':
                 $query->orderBy('name', 'desc');
                 break;
             case 'newest':
             default:
                 $query->orderBy('created_at', 'desc');
                 break;
         }

         return DataTables::of($query)
            ->editColumn('action', function ($data) {
                $editUrl = route('admin.medicines.edit', ['id' => $data->id]);
                $deleteUrl = route('admin.medicines.destroy', ['id' => $data->id]);
                $editBtn = '<a href="' . $editUrl . '" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Edit</a>';
                $deleteBtn = '<a href="javascript:void(0)" onclick="delete_record(\'' . $deleteUrl . '\')" class="ml-2 inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">Delete</a>';
                return '<div class="flex items-center">' . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
     }


    public function editmedicines($id)
    {
    	$data = Medicines::find($id);
     	return view("admin.medicines.edit-medicines",compact('data'));
    }

    public function editmedicinessave(Request $request)
    {
        // return $request;
    	foreach($request->input('type') as $new){
            $types[]=$new;
         }
         $alltype = implode(',',$types);

         $data=Medicines::find($request->get("id"));
	   	$data->name = $request->get("name");
	   	$data->dosage = $request->get("dosage");
	   	$data->description = $request->get("description");
           $data->medicine_type = $alltype;
	   	$data->save();

	   	$msg = __("Successfull Medicine Update");
	   	Session::flash('message',$msg);
        Session::flash('alert-class', 'alert-success');
       return redirect()->route('admin.medicines.index');
    }


}
