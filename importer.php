<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Shuchkin\SimpleXLSX;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$xlsx = SimpleXLSX::parse('d:\CFARM_HR\รายชื่อ-1.xlsx');
if (!$xlsx) {
    die(SimpleXLSX::parseError());
}

$rows = $xlsx->rows();
array_shift($rows); // remove header

DB::beginTransaction();
try {
    $count = 0;
    foreach ($rows as $row) {
        if (empty($row[0])) continue; // Skip empty rows

        $emp_code = trim($row[0]);
        $full_name = trim($row[1]);
        $position_name = trim($row[4]);
        $department_name = trim($row[5]);
        $hire_date_str = trim($row[6]); // 7 มี.ค. 2552
        
        $email = strtolower(Str::random(8)) . '@cfarm.local';
        $password = Hash::make('password');

        $user = User::where('username', $emp_code)->first();
        if (!$user) {
            $user = User::create([
                'username' => $emp_code,
                'email' => $email,
                'password' => $password,
                'role' => 'employee',
                'is_active' => true
            ]);
        } else {
            // Update password to be "password" if they already exist, as requested.
            $user->password = $password;
            $user->save();
        }

        $name_clean = str_replace(['นาย ', 'นางสาว ', 'นาง ', 'นาย', 'นางสาว', 'นาง'], '', $full_name);
        $name_parts = array_values(array_filter(preg_split('/\s+/', trim($name_clean))));
        $first_name = $name_parts[0] ?? '-';
        array_shift($name_parts);
        $last_name = !empty($name_parts) ? implode(' ', $name_parts) : '-';

        $department = null;
        if (!empty($department_name)) {
            $department = Department::firstOrCreate(['name' => $department_name]);
        }

        $position = null;
        if (!empty($position_name)) {
            $department_id = $department ? $department->id : null;
            $position = Position::firstOrCreate(['name' => $position_name, 'department_id' => $department_id]);
        }

        $employee = Employee::firstOrNew(['employee_code' => $emp_code]);
        $employee->user_id = $user->id;
        $employee->first_name = $first_name;
        $employee->last_name = $last_name;
        $employee->department_id = $department ? $department->id : null;
        $employee->position_id = $position ? $position->id : null;
        $employee->shift_id = 1;
        $employee->branch_id = 1;
        $employee->employment_status = 'active';
        
        // Convert Thai Date
        // e.g. "7 มี.ค. 2552" -> 2009-03-07
        if (!empty($hire_date_str)) {
            $thaiMonths = [
                'ม.ค.' => '01', 'ก.พ.' => '02', 'มี.ค.' => '03', 'เม.ย.' => '04',
                'พ.ค.' => '05', 'มิ.ย.' => '06', 'ก.ค.' => '07', 'ส.ค.' => '08',
                'ก.ย.' => '09', 'ต.ค.' => '10', 'พ.ย.' => '11', 'ธ.ค.' => '12'
            ];
            $dateParts = explode(' ', str_replace('  ', ' ', $hire_date_str));
            if (count($dateParts) == 3) {
                $day = str_pad($dateParts[0], 2, '0', STR_PAD_LEFT);
                $monthStr = $dateParts[1];
                $yearThai = $dateParts[2];
                $month = $thaiMonths[$monthStr] ?? '01';
                $year = (int)$yearThai - 543;
                try {
                    $employee->hire_date = Carbon::createFromFormat('Y-m-d', "$year-$month-$day");
                } catch (\Exception $e) {
                    echo "Parsing error for date: $hire_date_str \n";
                }
            }
        }
        
        $employee->save();
        $count++;
    }
    DB::commit();
    echo "Successfully imported $count employees.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
