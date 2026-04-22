<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = [
            [
                'name' => 'Haji Lulung',
                'category' => 'Kontraktor',
                'phone' => '0811-2233-4455',
                'total_order' => '12',
                'loyalty' => 'Gold'
            ],
            [
                'name' => 'Bapak Joko',
                'category' => 'Retail',
                'phone' => '0852-6677-8899',
                'total_order' => '3',
                'loyalty' => 'Silver'
            ],
            [
                'name' => 'PT Bangun Sejahtera',
                'category' => 'Perusahaan',
                'phone' => '021-555-1234',
                'total_order' => '25',
                'loyalty' => 'Platinum'
            ],
            [
                'name' => 'Ibu Maria',
                'category' => 'Retail',
                'phone' => '0812-4455-6677',
                'total_order' => '1',
                'loyalty' => 'Bronze'
            ],
        ];

        return view('customer::index', compact('customers'));
    }
}
