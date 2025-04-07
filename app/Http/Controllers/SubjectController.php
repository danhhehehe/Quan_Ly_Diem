<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject as MainModel;
use Illuminate\Support\Facades\DB;
use App\Models\TeacherProfile;
use App\Models\TeacherSubject;
use App\Models\User;

class SubjectController extends Controller
{
    // Hàm hiển thị danh sách tất cả các môn học
    public function index()
    {
        $data['rows'] = MainModel::all(); // Lấy tất cả các bản ghi từ bảng Subject
        return view('subjects.index', $data); // Trả về view hiển thị danh sách môn học
    }

    // Hàm hiển thị form thêm môn học mới
    public function add()
    {
        $data['teachers'] = User::where('role', 'teacher')->get(); // Lấy danh sách tất cả giáo viên
        return view('subjects.form', $data); // Trả về view form thêm môn học
    }

    // Hàm xử lý thêm môn học mới
    public function create(Request $request)
    {
        try {
            $params = $request->all(); // Lấy tất cả dữ liệu từ request
            DB::transaction(function () use ($params) {
                $rec = MainModel::create($params); // Tạo bản ghi mới trong bảng Subject
                if (isset($params['teacher_id'])) // Nếu có danh sách giáo viên
                    foreach ($params['teacher_id'] as $row) // Lặp qua danh sách giáo viên
                        TeacherSubject::create(['subject_id' => $rec->id, 'teacher_id' => $row]); // Tạo bản ghi liên kết môn học và giáo viên
            });
            return redirect()->route('subjects')->withSuccess("Đã thêm"); // Chuyển hướng với thông báo thành công
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage())->withInput(); // Chuyển hướng lại với thông báo lỗi
        }
    }

    // Hàm hiển thị form chỉnh sửa môn học
    public function edit($id)
    {
        $subject = MainModel::find($id); // Tìm môn học theo ID
        if (!$subject) {
            return redirect()->route('subjects')->withError("Subject not found"); // Nếu không tìm thấy, chuyển hướng với thông báo lỗi
        }

        $teachers = User::where('role', 'teacher')->get(); // Lấy danh sách tất cả giáo viên
        $teacher_subject_list = $subject->teachers ? $subject->teachers->pluck('id')->toArray() : []; // Lấy danh sách ID giáo viên liên kết với môn học

        return view('subjects.form', compact('subject', 'teachers', 'teacher_subject_list')); // Trả về view form chỉnh sửa
    }

    // Hàm xử lý cập nhật môn học
    public function update(Request $request, $id)
    {
        try {
            $rec = MainModel::findOrFail($id); // Tìm môn học theo ID, nếu không tìm thấy sẽ ném lỗi
            $params = $request->all(); // Lấy tất cả dữ liệu từ request
            DB::transaction(function () use ($params, $rec) {
                $teacher_subject_list = $rec->teacherSubjectList; // Lấy danh sách liên kết giáo viên - môn học
                foreach ($teacher_subject_list as $row) // Xóa tất cả các liên kết cũ
                    $row->delete();
                $rec->update($params); // Cập nhật thông tin môn học
                if (isset($params['teacher_id'])) // Nếu có danh sách giáo viên mới
                    foreach ($params['teacher_id'] as $row) // Lặp qua danh sách giáo viên
                        TeacherSubject::create(['subject_id' => $rec->id, 'teacher_id' => $row]); // Tạo liên kết mới
            });
            return redirect()->route('subjects')->withSuccess("Đã cập nhật"); // Chuyển hướng với thông báo thành công
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage())->withInput(); // Chuyển hướng lại với thông báo lỗi
        }
    }

    // Hàm xử lý xóa môn học
    public function delete($id)
    {
        try {
            $rec = MainModel::findOrFail($id); // Tìm môn học theo ID, nếu không tìm thấy sẽ ném lỗi
            DB::transaction(function () use ($rec) {
                $rec->teacherSubjectList()->delete(); // Xóa tất cả các liên kết giáo viên - môn học
                $rec->delete(); // Xóa môn học
            });
            return redirect()->back()->withSuccess("Đã xóa"); // Chuyển hướng với thông báo thành công
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage()); // Chuyển hướng lại với thông báo lỗi
        }
    }
}
