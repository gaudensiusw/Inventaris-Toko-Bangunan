<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RolePermission;

$all = ['dashboard_view','pos_access','product_view','product_edit','stock_edit','employee_view','employee_edit','employee_delete','report_view'];

RolePermission::truncate();
RolePermission::create(['role_name' => 'owner', 'permissions' => $all]);
RolePermission::create(['role_name' => 'supervisor', 'permissions' => $all]);
RolePermission::create(['role_name' => 'kasir', 'permissions' => ['dashboard_view', 'pos_access']]);
RolePermission::create(['role_name' => 'gudang', 'permissions' => ['dashboard_view', 'product_view', 'stock_edit']]);
RolePermission::create(['role_name' => 'operator', 'permissions' => ['dashboard_view', 'pos_access']]);

echo "Permissions seeded successfully!" . PHP_EOL;
