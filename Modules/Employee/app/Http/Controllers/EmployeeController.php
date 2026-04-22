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
                'name' => 'Ahmad Faisal',
                'role' => 'Kasir',
                'phone' => '0812-3456-7890',
                'status' => 'Aktif',
                'join_date' => '12 Jan 2024'
            ],
            [
                'name' => 'Siti Nurhaliza',
                'role' => 'Admin Gudang',
                'phone' => '0821-9876-5432',
                'status' => 'Aktif',
                'join_date' => '05 Feb 2024'
            ],
            [
                'name' => 'Bambang Pamungkas',
                'role' => 'Driver',
                'phone' => '0857-1122-3344',
                'status' => 'Cuti',
                'join_date' => '20 Mar 2023'
            ],
            [
                'name' => 'Dewi Sartika',
                'role' => 'Sales',
                'phone' => '0813-5566-7788',
                'status' => 'Aktif',
                'join_date' => '15 Aug 2023'
            ],
        ];

        return view('employee::index', compact('employees'));
    }
}
