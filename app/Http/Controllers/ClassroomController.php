<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Classroom as MainModel;
use Illuminate\Support\Facades\DB;

class ClassroomController extends Controller
{
    // Lấy danh sách tất cả các lớp học và trả về view 'classes.index'
    public function index()
    {
        $data['rows'] = MainModel::all();
        return view('classes.index', $data);
    }

    // Hiển thị form để thêm lớp học mới
    public function add()
    {
        return view('classes.form');
    }

    // Hiển thị thông tin chi tiết của một lớp học dựa trên ID
    public function view($id) {
        $data['rec'] = MainModel::findOrFail($id);
        return view('classes.student_list', $data);
    }

    // Tạo mới một lớp học
    public function create(Request $request)
    {
        try {
            $params = $request->all();
            // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
            DB::transaction(function () use ($params) {
                MainModel::create($params);
            });
            return redirect()->route('classes')->withSuccess("Đã thêm");
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về thông báo lỗi
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    // Hiển thị form chỉnh sửa thông tin lớp học
    public function edit($id)
    {
        $data['rec'] = MainModel::findOrFail($id);
        return view('classes.form')->with($data);
    }

    // Cập nhật thông tin lớp học
    public function update(Request $request, $id)
    {
        try {
            $rec = MainModel::findOrFail($id);
            $params = $request->all();
            // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
            DB::transaction(function () use ($params, $rec) {
                $rec->update($params);
            });
            return redirect()->route('classes')->withSuccess("Đã cập nhật");
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về thông báo lỗi
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    // Xóa một lớp học
    public function delete($id)
    {
        try {
            $rec = MainModel::findOrFail($id);
            // Kiểm tra nếu lớp học có sinh viên thì không cho phép xóa
            if($rec->students->count() > 0)
                throw new \Exception('Bạn phải chuyển hết sinh viên ra khỏi lớp trước khi xóa lớp');
            $rec->delete();
            return redirect()->back()->withSuccess("Đã xóa");
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về thông báo lỗi
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
