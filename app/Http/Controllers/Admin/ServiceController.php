<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Session;
use validate;
use Sentinel;
use DB;
use App\Models\User;
use App\Models\Services;
use App\Models\Review;
use App\Models\BookAppointment;
use App\Models\Schedule;
use App\Models\Doctors;
use DataTables;
class ServiceController extends Controller
{
    
     public function showservice(){
        return view("admin.service.default");
     }
    
     public function saveservices($id){
        $data=Services::find($id);
        return view("admin.service.saveservices")->with("data",$data)->with("id",$id);
     }

     public function updateservice(Request $request){
     	if($request->get("id")==0){
           $store=new Services(); 	
           $msg=__("Specialities Add Successfully");  
           $rel_url="";
     	}else{
     	   $store=Services::find($request->get("id"));
     	   $msg=__("Specialities Update Successfully");
     	   $rel_url=$store->icon;
           $img_url=$rel_url;
     	}
     	if ($request->hasFile('icon')) 
              {
                 $file = $request->file('icon');
                 $filename = $file->getClientOriginalName();
                 $extension = $file->getClientOriginalExtension() ?: 'png';
                 $folderName = '/upload/services/';
                 $picture = time() . '.' . $extension;
                 $destinationPath = public_path() . $folderName;
                 $request->file('icon')->move($destinationPath, $picture);
                 $img_url =$picture;                
                  $image_path = public_path() ."/upload/services/".$rel_url;
                    if(file_exists($image_path)&&$rel_url!="") {
                        try {
                             unlink($image_path);
                        }
                        catch(Exception $e) {
                          
                        }                        
                  }
             }
     	$store->name=$request->get("name");
     	$store->description=$request->get("description");
     	$store->icon=$img_url;
     	$store->save();
     	Session::flash('message',$msg); 
        Session::flash('alert-class', 'alert-success');
        return redirect("admin/services");
     }

	 public function servicestable(){
	 	$services =Services::query();

        return DataTables::of($services)
            ->editColumn('id', function ($services) {
                return $services->id;
            })
            ->editColumn('icon', function ($services) {
                return asset("upload/services").'/'.$services->icon;
            })
            ->editColumn('name', function ($services) {
                return $services->name;
            }) 
            ->editColumn('description', function ($services) {
                return $services->description;
            })
            ->addColumn('recommendable', function ($service) {
                $checked = $service->recommendable ? 'checked' : '';
                $route = route('admin.services.toggle_recommendable', $service->id);
                $csrf = csrf_token();
                return '<label class="relative inline-flex items-center cursor-pointer">
                          <input type="checkbox" value="" class="sr-only peer" onchange="toggleRecommendable(\''.$route.'\', \''.$csrf.'\')" '.$checked.'>
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[\'\'] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>';
            })
            ->editColumn('action', function ($services) {
                $editUrl = route('admin.services.save', ['id' => $services->id]);
                $deleteUrl = route('admin.services.delete', ['id' => $services->id]);
                $editBtn = '<a href="' . $editUrl . '" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Edit</a>';
                $deleteBtn = '<a href="javascript:void(0)" onclick="delete_record(\'' . $deleteUrl . '\')" class="ml-2 inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">Delete</a>';
                return '<div class="flex items-center">' . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['action', 'recommendable'])
            ->make(true);
	 }


     public function showreviews(){
         return view("admin.product.review");
     }

     public function reviewtable(){
        $review =Review::all();

        return DataTables::of($review)
            ->editColumn('id', function ($review) {
                return $review->id;
            })
            ->editColumn('product_name', function ($review) {
                 $data=Products::find($review->product_id);
                return isset($data)?$data->name:"";
            })
            ->editColumn('username', function ($review) {
                return isset($review->name)?$review->name:"";
            }) 
             ->editColumn('ratting', function ($review) {
                return isset($review->rating)?$review->rating:"";
            })
             ->editColumn('email', function ($review) {
                return isset($review->email)?$review->email:"";
            })
             ->editColumn('comment', function ($review) {
                return isset($review->comment)?$review->comment:"";
            })            
            ->editColumn('action', function ($review) {   
               
               $return = '';
              return $return;               
            })
           
            ->make(true);
     }

     public function deleteservices($id){
        $service=Services::find($id);
        if($service){
            $data=Doctors::where("department_id",$id)->get();
            if(count($data)>0){
                foreach ($data as $doctor) {                    
                        if($doctor){
                            $deletsolt=Schedule::with('getslotls')->where("doctor_id",$doctor->id)->get();
                            foreach ($deletsolt as $de) {
                                foreach ($de->getslotls as $k) {
                                   $k->delete();
                                }
                                $de->delete();
                            }
                            $bookappointment=BookAppointment::where("doctor_id",$doctor->id)->delete();
                            $review=Review::where("doc_id",$doctor->id)->delete();
                            if ($doctor->image) {
                                $image_path = public_path('upload/doctors/' . $doctor->image);
                                if (file_exists($image_path)) {
                                    try {
                                        unlink($image_path);
                                    } catch (\Exception $e) {
                                        // Optionally log the error
                                    }
                                }
                            }
                            $doctor->delete();
                        }
                }
            }
            if ($service->icon) {
                $image_path = public_path('upload/services/' . $service->icon);
                if (file_exists($image_path)) {
                    try {
                        unlink($image_path);
                    } catch (\Exception $e) {
                        // Optionally log the error
                    }
                }
            }
            $service->delete();
        }
        Session::flash('message',__("Specialities Delete Successfully")); 
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
     }

    public function toggleRecommendable(Services $service)
    {
        $service->recommendable = !$service->recommendable;
        $service->save();
        return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
    }
    
}
