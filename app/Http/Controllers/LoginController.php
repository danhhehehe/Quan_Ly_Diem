<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Xử lý yêu cầu đăng nhập.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request)
    {
        // Xác thực dữ liệu đầu vào từ người dùng (username và password là bắt buộc)
        $credentials = $request->validate([
            'username' => ['required'], // Trường username là bắt buộc
            'password' => ['required'], // Trường password là bắt buộc
        ]);

        // Kiểm tra thông tin đăng nhập
        if (Auth::attempt($credentials)) {
            // Tái tạo session để bảo mật (ngăn chặn tấn công session fixation)
            $request->session()->regenerate();
            // Chuyển hướng đến trang chính (index) nếu đăng nhập thành công
            return redirect()->route('index');
        }

        // Nếu đăng nhập thất bại, thiết lập thông báo lỗi
        $error = 'Tài khoản không tồn tại hoặc mật khẩu không đúng';

        // Quay lại trang đăng nhập với thông báo lỗi và dữ liệu đã nhập
        return back()->withError($error)->withInput();
    }

    /**
     * Đăng xuất người dùng khỏi ứng dụng.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Đăng xuất người dùng
        Auth::logout();
        // Hủy bỏ session hiện tại
        $request->session()->invalidate();
        // Tái tạo token session để bảo vệ chống tấn công CSRF
        $request->session()->regenerateToken();
        // Chuyển hướng đến trang đăng nhập sau khi đăng xuất
        return redirect()->route('login');
    }
}
