<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctors;
use App\Models\Services;
use App\Services\AiAssistantService;
use Illuminate\Support\Facades\Log;
use Exception;

class AiProfileAssistantController extends Controller
{
    protected $aiService;

    public function __construct(AiAssistantService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Generate an SEO-friendly doctor profile copy on the fly.
     */
    public function generateDoctorBio(Request $request)
    {
        try {
            $name = $request->input('name');
            $departmentId = $request->input('department_id');
            $specialty = $request->input('specialty');
            $address = $request->input('address', 'Harare, Zimbabwe');
            $workingTime = $request->input('working_time', '8am to 5pm');
            $existingAbout = $request->input('existing_about', '');
            $existingServices = $request->input('existing_services', '');
            $existingHealthcare = $request->input('existing_healthcare', '');

            if (!$specialty && $departmentId) {
                $dept = Services::find($departmentId);
                if ($dept) {
                    $specialty = $dept->name;
                }
            }

            if (empty($specialty)) {
                $specialty = 'General Practitioner';
            }

            $result = $this->aiService->generateDoctorBio([
                'name' => $name,
                'specialty' => $specialty,
                'address' => $address,
                'working_time' => $workingTime,
                'existing_about' => $existingAbout,
                'existing_services' => $existingServices,
                'existing_healthcare' => $existingHealthcare,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        } catch (Exception $e) {
            Log::error('AI Doctor Bio Generation Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate doctor profile. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate an SEO-friendly pharmacy profile copy on the fly.
     */
    public function generatePharmacyBio(Request $request)
    {
        try {
            $name = $request->input('name');
            $address = $request->input('address', 'Harare, Zimbabwe');
            $workingTime = $request->input('working_time', 'Monday to Saturday 8am - 6pm');
            $existingAbout = $request->input('existing_about', '');
            $existingServices = $request->input('existing_services', '');

            $result = $this->aiService->generatePharmacyBio([
                'name' => $name,
                'address' => $address,
                'working_time' => $workingTime,
                'existing_about' => $existingAbout,
                'existing_services' => $existingServices,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $result
            ]);
        } catch (Exception $e) {
            Log::error('AI Pharmacy Bio Generation Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate pharmacy profile. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-enhance and save a doctor's profile directly in database.
     */
    public function autoEnhanceDoctor(Request $request, $id)
    {
        try {
            $doctor = Doctors::findOrFail($id);
            $specialty = $doctor->departmentls->name ?? 'General Practitioner';

            $result = $this->aiService->generateDoctorBio([
                'name' => $doctor->name,
                'specialty' => $specialty,
                'address' => $doctor->address ?? 'Harare, Zimbabwe',
                'working_time' => $doctor->working_time ?? '8am to 5pm',
                'existing_about' => $doctor->aboutus ?? '',
                'existing_services' => $doctor->services ?? '',
                'existing_healthcare' => $doctor->healthcare ?? '',
            ]);

            if (!empty($result['aboutus'])) {
                $doctor->aboutus = $result['aboutus'];
            }
            if (!empty($result['services']) && (empty($doctor->services) || strlen($doctor->services) < 30)) {
                $doctor->services = $result['services'];
            }
            if (!empty($result['healthcare']) && (empty($doctor->healthcare) || strlen($doctor->healthcare) < 30)) {
                $doctor->healthcare = $result['healthcare'];
            }
            $doctor->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Doctor profile successfully enhanced with AI!',
                'doctor' => $doctor,
                'details' => $result
            ]);
        } catch (Exception $e) {
            Log::error('Auto-enhance doctor error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to enhance doctor profile. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-enhance and save a pharmacy profile directly in database.
     */
    public function autoEnhancePharmacy(Request $request, $id)
    {
        try {
            $pharmacy = Doctors::where('profile_type', '2')->findOrFail($id);

            $result = $this->aiService->generatePharmacyBio([
                'name' => $pharmacy->name,
                'address' => $pharmacy->address ?? 'Harare, Zimbabwe',
                'working_time' => $pharmacy->working_time ?? '8am to 6pm',
                'existing_about' => $pharmacy->aboutus ?? '',
                'existing_services' => $pharmacy->services ?? '',
            ]);

            if (!empty($result['aboutus'])) {
                $pharmacy->aboutus = $result['aboutus'];
            }
            if (!empty($result['services']) && (empty($pharmacy->services) || strlen($pharmacy->services) < 30)) {
                $pharmacy->services = $result['services'];
            }
            $pharmacy->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Pharmacy profile successfully enhanced with AI!',
                'pharmacy' => $pharmacy,
                'details' => $result
            ]);
        } catch (Exception $e) {
            Log::error('Auto-enhance pharmacy error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to enhance pharmacy profile. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Audit incomplete doctor and pharmacy descriptions in system.
     */
    public function auditProfiles()
    {
        try {
            $totalDoctors = Doctors::where('profile_type', '!=', '2')->count();
            $incompleteDoctors = Doctors::where('profile_type', '!=', '2')
                ->where(function ($q) {
                    $q->whereNull('aboutus')
                      ->orWhere('aboutus', '')
                      ->orWhereRaw('LENGTH(aboutus) < 60');
                })
                ->get(['id', 'name', 'department_id', 'aboutus']);

            $totalPharmacies = Doctors::where('profile_type', '2')->count();
            $incompletePharmacies = Doctors::where('profile_type', '2')
                ->where(function ($q) {
                    $q->whereNull('aboutus')
                      ->orWhere('aboutus', '')
                      ->orWhereRaw('LENGTH(aboutus) < 60');
                })
                ->get(['id', 'name', 'aboutus']);

            return response()->json([
                'status' => 'success',
                'doctors' => [
                    'total' => $totalDoctors,
                    'incomplete_count' => $incompleteDoctors->count(),
                    'incomplete_list' => $incompleteDoctors,
                ],
                'pharmacies' => [
                    'total' => $totalPharmacies,
                    'incomplete_count' => $incompletePharmacies->count(),
                    'incomplete_list' => $incompletePharmacies,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk auto-enhance incomplete profiles.
     */
    public function bulkEnhance(Request $request)
    {
        try {
            $type = $request->input('type', 'doctor'); // 'doctor' or 'pharmacy'
            $limit = min((int)$request->input('limit', 10), 25);
            $enhanced = [];

            if ($type === 'doctor') {
                $doctors = Doctors::where('profile_type', '!=', '2')
                    ->where(function ($q) {
                        $q->whereNull('aboutus')
                          ->orWhere('aboutus', '')
                          ->orWhereRaw('LENGTH(aboutus) < 60');
                    })
                    ->with('departmentls')
                    ->take($limit)
                    ->get();

                foreach ($doctors as $doc) {
                    $specialty = $doc->departmentls->name ?? 'General Practitioner';
                    $res = $this->aiService->generateDoctorBio([
                        'name' => $doc->name,
                        'specialty' => $specialty,
                        'address' => $doc->address ?? 'Harare, Zimbabwe',
                        'working_time' => $doc->working_time ?? '8am to 5pm',
                        'existing_about' => $doc->aboutus ?? '',
                        'existing_services' => $doc->services ?? '',
                        'existing_healthcare' => $doc->healthcare ?? '',
                    ]);

                    if (!empty($res['aboutus'])) {
                        $doc->aboutus = $res['aboutus'];
                    }
                    if (!empty($res['services']) && (empty($doc->services) || strlen($doc->services) < 30)) {
                        $doc->services = $res['services'];
                    }
                    if (!empty($res['healthcare']) && (empty($doc->healthcare) || strlen($doc->healthcare) < 30)) {
                        $doc->healthcare = $res['healthcare'];
                    }
                    $doc->save();
                    $enhanced[] = $doc->name;
                }
            } else {
                $pharmacies = Doctors::where('profile_type', '2')
                    ->where(function ($q) {
                        $q->whereNull('aboutus')
                          ->orWhere('aboutus', '')
                          ->orWhereRaw('LENGTH(aboutus) < 60');
                    })
                    ->take($limit)
                    ->get();

                foreach ($pharmacies as $ph) {
                    $res = $this->aiService->generatePharmacyBio([
                        'name' => $ph->name,
                        'address' => $ph->address ?? 'Harare, Zimbabwe',
                        'working_time' => $ph->working_time ?? '8am to 6pm',
                        'existing_about' => $ph->aboutus ?? '',
                        'existing_services' => $ph->services ?? '',
                    ]);

                    if (!empty($res['aboutus'])) {
                        $ph->aboutus = $res['aboutus'];
                    }
                    if (!empty($res['services']) && (empty($ph->services) || strlen($ph->services) < 30)) {
                        $ph->services = $res['services'];
                    }
                    $ph->save();
                    $enhanced[] = $ph->name;
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully enhanced ' . count($enhanced) . ' profiles with AI.',
                'enhanced_count' => count($enhanced),
                'enhanced_names' => $enhanced,
            ]);
        } catch (Exception $e) {
            Log::error('Bulk enhance error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Bulk enhancement failed. ' . $e->getMessage()
            ], 500);
        }
    }
}
