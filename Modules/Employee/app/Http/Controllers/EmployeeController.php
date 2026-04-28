<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = [
            [
                'name' => 'Budi Santoso',
                'role' => 'Supervisor',
                'join_date' => '01 Jan 2023',
                'status' => 'Aktif',
            ],
            [
                'name' => 'Ahmad Faisal',
                'role' => 'Kasir',
                'join_date' => '12 Jan 2024',
                'status' => 'Aktif',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'role' => 'Staff Gudang',
                'join_date' => '05 Feb 2024',
                'status' => 'Izin',
            ],
            [
                'name' => 'Bambang Pamungkas',
                'role' => 'Supir',
                'join_date' => '20 Mar 2023',
                'status' => 'Alpa',
            ],
            [
                'name' => 'Dewi Sartika',
                'role' => 'Staff Gudang',
                'join_date' => '15 Aug 2023',
                'status' => 'Aktif',
            ],
        ];

        return view('employee::index', compact('employees'));
    }
}
