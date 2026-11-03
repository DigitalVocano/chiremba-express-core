<?php


namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookAppointment;
use App\Models\Setting;
use App\Models\Patient;
use Redirect,Response;

class CalendarController extends Controller
{
    public function index()
    {
        if(request()->ajax()) 
        {
 
         $start = (!empty($_GET["start"])) ? ($_GET["start"]) : ('');
         $end = (!empty($_GET["end"])) ? ($_GET["end"]) : ('');
 
         $data = BookAppointment::whereDate('date', '>=', $start)->whereDate('date',   '<=', $end)->get(['id','slot_name as title','date as start', 'date as end']);
         return Response::json($data);
        }
        return view('fullcalendarDates');
    }
    
}