<?php

namespace App\Http\Controllers;

// Sử dụng các trait để hỗ trợ quyền truy cập, xử lý công việc và xác thực
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Hỗ trợ kiểm tra quyền truy cập
use Illuminate\Foundation\Bus\DispatchesJobs; // Hỗ trợ xử lý các công việc (jobs)
use Illuminate\Foundation\Validation\ValidatesRequests; // Hỗ trợ xác thực dữ liệu
use Illuminate\Routing\Controller as BaseController; // Kế thừa lớp cơ bản của Controller

class Controller extends BaseController
{
    // Sử dụng các trait để thêm chức năng vào Controller
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
