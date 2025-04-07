<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User as MainModel;
use App\Models\StudentProfile;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    // Phương thức hiển thị danh sách sinh viên
    public function index()
    {
        // Lấy danh sách người dùng có vai trò là 'student'
        $data['rows'] = MainModel::where('role', 'student')->get();
        return view('students.index', $data);
    }

    // Phương thức hiển thị form thêm sinh viên
    public function add()
    {
        // Lấy danh sách tất cả các lớp học
        $data['classes'] = Classroom::all();
        return view('students.form')->with($data);
    }

    // Phương thức tạo mới sinh viên
    public function create(Request $request)
    {
        try {
            $params = $request->all();
            // Mã hóa mật khẩu
            $params['password'] = Hash::make($params['password']);
            // Gán username bằng mã sinh viên
            $params['username'] = $params['code'];
            // Gán vai trò là 'student'
            $params['role'] = 'student';
            DB::transaction(function () use ($params) {
                // Tạo hồ sơ sinh viên và lấy ID
                $params['profile_id'] = StudentProfile::create($params)->id;
                // Tạo người dùng
                MainModel::create($params);
            });
            // Chuyển hướng với thông báo thành công
            return redirect()->route('students')->withSuccess("Đã thêm");
        } catch (\Exception $e) {
            // Chuyển hướng lại với thông báo lỗi
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    // Phương thức hiển thị form chỉnh sửa sinh viên
    public function edit($id)
    {
        // Lấy danh sách tất cả các lớp học
        $data['classes'] = Classroom::all();
        // Tìm sinh viên theo ID
        $data['rec'] = MainModel::findOrFail($id);
        return view('students.form')->with($data);
    }

    // Phương thức cập nhật thông tin sinh viên
    public function update(Request $request, $id)
    {
        try {
            $rec = MainModel::findOrFail($id);
            $params = $request->all();
            // Nếu có mật khẩu mới, mã hóa mật khẩu
            if(strlen($params['password']))
                $params['password'] = Hash::make($params['password']);
            else
                unset($params['password']); // Nếu không, bỏ qua mật khẩu
            // Gán username bằng mã sinh viên
            $params['username'] = $params['code'];
            // Gán vai trò là 'student'
            $params['role'] = 'student';
            DB::transaction(function () use ($params, $rec) {
                // Cập nhật hồ sơ sinh viên
                $rec->profile->update($params);
                // Cập nhật thông tin người dùng
                $rec->update($params);
            });
            // Chuyển hướng với thông báo thành công
            return redirect()->route('students')->withSuccess("Đã cập nhật");
        } catch (\Exception $e) {
            // Chuyển hướng lại với thông báo lỗi
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    // Phương thức xóa sinh viên
    public function delete($id) {
        try {
            $rec = MainModel::findOrFail($id);
            // Xóa hồ sơ sinh viên
            $rec->profile->delete();
            // Xóa người dùng
            $rec->delete();
            // Chuyển hướng với thông báo thành công
            return redirect()->back()->withSuccess("Đã xóa");
        } catch (\Exception $e) {
            // Chuyển hướng lại với thông báo lỗi
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
