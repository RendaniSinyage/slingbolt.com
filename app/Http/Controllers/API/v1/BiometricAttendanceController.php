<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceEmployeeResource;
use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class BiometricAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->can('manage biometric attendance')) {
            $company_setting = Utility::settings();
            $api_urls = !empty($company_setting['zkteco_api_url']) ? $company_setting['zkteco_api_url'] : '';
            $token = !empty($company_setting['auth_token']) ? $company_setting['auth_token'] : '';

            $start_date = $request->input('start_date', date('Y-m-d', strtotime('-7 days')));
            $end_date = $request->input('end_date', date('Y-m-d'));

            $api_url = rtrim($api_urls, '/');

            $url = $api_url . '/iclock/api/transactions/?' . http_build_query([
                'start_time' => $start_date . ' 00:00:00',
                'end_time' => $end_date . ' 23:59:59',
                'page_size' => 10000,
            ]);

            if (empty($token)) {
                return response()->json(['error' => __('Auth token is missing.')], 400);
            }

            try {
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT' => 0,
                    CURLOPT_FOLLOWLOCATION' => true,
                    CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST' => 'GET',
                    CURLOPT_HTTPHEADER' => [
                        'Content-Type: application/json',
                        'Authorization: Token ' . $token
                    ],
                ]);
                $response = curl_exec($curl);
                $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                if ($httpcode >= 400) {
                    return response()->json(['error' => __('Error fetching data from biometric device.'), 'details' => json_decode($response)], $httpcode);
                }

                $json_attendance = json_decode($response, true);
                return response()->json($json_attendance);

            } catch (\Throwable $th) {
                return response()->json(['error' => __('Something went wrong please try again.'), 'details' => $th->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->can('manage biometric attendance')) {
            $company_settings = Utility::settings();
            if (empty($company_settings['auth_token'])) {
                return response()->json(['error' => __('Please first create auth token in settings.')], 400);
            }

            $employee = Employee::where('created_by', $user->creatorId())->where('biometric_emp_id', $request->biometric_emp_id)->first();

            if (empty($employee)) {
                return response()->json(['error' => __('Employee not found.')], 404);
            }

            $biometric_code = $employee->biometric_emp_id;
            $startTime = Utility::getValByName('company_start_time');
            $endTime = Utility::getValByName('company_end_time');
            $date = date("Y-m-d", strtotime($request->punch_time));
            $time = date("H:i:s", strtotime($request->punch_time));

            $existing_attendance = AttendanceEmployee::where('employee_id', $employee->id)
                ->where('date', $date)
                ->where(function ($query) use ($time) {
                    $query->where('clock_in', $time)
                          ->orWhere('clock_out', $time);
                })
                ->exists();

            if ($existing_attendance) {
                return response()->json(['error' => __('Attendance for this time already exists.')], 409);
            }

            $attendance = AttendanceEmployee::where('employee_id', $employee->id)
                ->where('date', $date)
                ->where('clock_out', '00:00:00')
                ->orderBy('id', 'desc')
                ->first();

            if ($attendance) {
                // Clock Out
                $endTimestamp = strtotime($date . ' ' . $endTime);
                $currentTimestamp = strtotime($date . ' ' . $time);
                $earlyLeavingSeconds = $endTimestamp - $currentTimestamp > 0 ? $endTimestamp - $currentTimestamp : 0;
                $earlyLeaving = gmdate('H:i:s', $earlyLeavingSeconds);

                $overtimeSeconds = $currentTimestamp - $endTimestamp > 0 ? $currentTimestamp - $endTimestamp : 0;
                $overtime = gmdate('H:i:s', $overtimeSeconds);

                $attendance->update([
                    'clock_out' => $time,
                    'early_leaving' => $earlyLeaving,
                    'overtime' => $overtime
                ]);

                return response()->json(['message' => __('Employee successfully clocked out.'), 'data' => new AttendanceEmployeeResource($attendance)]);
            } else {
                // Clock In
                $lastClockOutEntry = AttendanceEmployee::where('employee_id', $employee->id)
                    ->where('date', $date)
                    ->where('clock_out', '!=', '00:00:00')
                    ->orderBy('id', 'desc')
                    ->first();

                $lateSeconds = 0;
                if ($lastClockOutEntry) {
                    $lateSeconds = strtotime($date . ' ' . $time) - strtotime($date . ' ' . $lastClockOutEntry->clock_out);
                } else {
                    $lateSeconds = strtotime($date . ' ' . $time) - strtotime($date . ' ' . $startTime);
                }
                $late = gmdate('H:i:s', max(0, $lateSeconds));

                $new_attendance = AttendanceEmployee::create([
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'status' => 'Present',
                    'clock_in' => $time,
                    'clock_out' => '00:00:00',
                    'late' => $late,
                    'early_leaving' => '00:00:00',
                    'overtime' => '00:00:00',
                    'total_rest' => '00:00:00',
                    'created_by' => $user->creatorId(),
                ]);

                return response()->json(['message' => __('Employee successfully clocked in.'), 'data' => new AttendanceEmployeeResource($new_attendance)], 201);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }

    public function syncAll(Request $request)
    {
        $user = Auth::user();
        if ($user->can('manage biometric attendance')) {
            $company_setting = Utility::settings();
            $api_urls = !empty($company_setting['zkteco_api_url']) ? $company_setting['zkteco_api_url'] : '';
            $token = !empty($company_setting['auth_token']) ? $company_setting['auth_token'] : '';

            if (empty($token)) {
                return response()->json(['error' => __('Auth token is missing.')], 400);
            }

            $start_date = $request->input('start_date', date('Y-m-d', strtotime('-7 days')));
            $end_date = $request->input('end_date', date('Y-m-d'));

            $api_url = rtrim($api_urls, '/');
            $url = $api_url . '/iclock/api/transactions/?' . http_build_query([
                'start_time' => $start_date . ' 00:00:00',
                'end_time' => $end_date . ' 23:59:59',
                'page_size' => 10000,
            ]);

            try {
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION' => true,
                    CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST' => 'GET',
                    CURLOPT_HTTPHEADER' => [
                        'Content-Type: application/json',
                        'Authorization: Token ' . $token
                    ],
                ]);
                $response = curl_exec($curl);
                $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                if ($httpcode >= 400) {
                    return response()->json(['error' => __('Error fetching data from biometric device.'), 'details' => json_decode($response)], $httpcode);
                }

                $attendances = json_decode($response, true)['data'] ?? [];

                $synced_count = 0;
                $error_logs = [];

                foreach ($attendances as $bio_attendance) {
                    $employee = Employee::where('created_by', $user->creatorId())->where('biometric_emp_id', $bio_attendance['emp_code'])->first();

                    if (!$employee) {
                        $error_logs[] = "Employee with biometric ID {$bio_attendance['emp_code']} not found.";
                        continue;
                    }

                    $punch_time = strtotime($bio_attendance['punch_time']);
                    $date = date("Y-m-d", $punch_time);
                    $time = date("H:i:s", $punch_time);

                    $existing_attendance = AttendanceEmployee::where('employee_id', $employee->id)
                        ->where('date', $date)
                        ->where(function ($query) use ($time) {
                            $query->where('clock_in', $time)
                                  ->orWhere('clock_out', $time);
                        })
                        ->exists();

                    if ($existing_attendance) {
                        continue;
                    }

                    $this->store(new Request([
                        'biometric_emp_id' => $employee->biometric_emp_id,
                        'punch_time' => $bio_attendance['punch_time']
                    ]));

                    $synced_count++;
                }

                return response()->json([
                    'message' => "Sync completed. {$synced_count} records processed.",
                    'synced_count' => $synced_count,
                    'errors' => $error_logs
                ]);

            } catch (\Throwable $th) {
                return response()->json(['error' => __('Something went wrong during sync.'), 'details' => $th->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 403);
        }
    }
}
