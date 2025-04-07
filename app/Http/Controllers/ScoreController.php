<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Score as MainModel;
use App\Models\Subject;
use App\Models\User;
use App\Models\RequestEditScore;
use App\Models\Classroom;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\DB;

class ScoreController extends Controller
{
    // Hiển thị danh sách các môn học
    public function viewSubjects()
    {
        $data['rows'] = Subject::all(); // Lấy tất cả các môn học
        return view('scores.subject.list', $data); // Trả về view danh sách môn học
    }

    // Hiển thị điểm theo môn học
    public function bySubject($id)
    {
        $data['rows'] = MainModel::where('subject_id', $id)->get(); // Lấy điểm theo ID môn học
        $data['rec'] = Subject::findOrFail($id); // Lấy thông tin môn học
        return view('scores.subject.index', $data); // Trả về view danh sách điểm theo môn học
    }

    // Hiển thị danh sách học sinh
    public function viewStudents()
    {
        $data['rows'] = User::where('role', 'student')->get(); // Lấy danh sách người dùng có vai trò là học sinh
        return view('scores.student.list', $data); // Trả về view danh sách học sinh
    }

    // Hiển thị điểm theo học sinh
    public function byStudent($id)
    {
        $data['rows'] = MainModel::where('student_id', $id)->get(); // Lấy điểm theo ID học sinh
        $data['rec'] = StudentProfile::findOrFail($id); // Lấy thông tin hồ sơ học sinh
        return view('scores.student.index', $data); // Trả về view danh sách điểm theo học sinh
    }

    // Hiển thị danh sách các học kỳ
    public function viewSemesters()
    {
        if(auth()->user()->role == 'student') { // Nếu người dùng là học sinh
            $user = auth()->user();
            $semesters = [];
            $scores = MainModel::where('student_id', $user->profile->id)->get(); // Lấy điểm của học sinh
            foreach($scores as $score) {
                if(!in_array($score->subject->semester, $semesters)) // Kiểm tra học kỳ đã tồn tại chưa
                    $semesters[] = $score->subject->semester;
            }
            sort($semesters); // Sắp xếp danh sách học kỳ
            foreach($semesters as $index => $semester) {
                $semesters[$index] = ['semester' => $semester];
            }
            $data['rows'] = $semesters; // Gán danh sách học kỳ vào biến dữ liệu
        } else
            $data['rows'] = Subject::select('semester')->distinct()->orderBy('semester', 'DESC')->get(); // Lấy danh sách học kỳ từ môn học
        return view('scores.semester.list', $data); // Trả về view danh sách học kỳ
    }

    // Hiển thị điểm theo học kỳ
    public function bySemester(Request $request, $semester)
    {
        $data['rec'] = $semester; // Lưu thông tin học kỳ
        $data['rows'] = MainModel::all(); // Lấy tất cả điểm
        $data['rows_filtered'] = [];
        foreach ($data['rows'] as $row) {
            if ($row->subject->semester == $semester) { // Lọc điểm theo học kỳ
                if(
                    !(auth()->user()->role == 'student') // Nếu không phải học sinh
                    || (auth()->user()->role == 'student' && auth()->user()->profile->id == $row->student->id) // Hoặc là học sinh nhưng đúng ID
                )
                    array_push($data['rows_filtered'], $row);
            }
        }
        $data['rows'] = $data['rows_filtered']; // Gán danh sách điểm đã lọc
        return view('scores.semester.index', $data); // Trả về view danh sách điểm theo học kỳ
    }

    // Hiển thị danh sách lớp học
    public function viewClassrooms()
    {
        $data['rows'] = Classroom::all(); // Lấy tất cả lớp học
        return view('scores.classroom.list', $data); // Trả về view danh sách lớp học
    }

    // Hiển thị điểm theo lớp học
    public function byClassroom(Request $request, $id)
    {
        $data['rec'] = Classroom::findOrFail($id); // Lấy thông tin lớp học
        $data['rows'] = MainModel::all(); // Lấy tất cả điểm
        $data['rows_filtered'] = [];
        foreach ($data['rows'] as $row) {
            if ($row->student->class->id == $id) { // Lọc điểm theo lớp học
                array_push($data['rows_filtered'], $row);
            }
        }
        $data['rows'] = $data['rows_filtered']; // Gán danh sách điểm đã lọc
        return view('scores.classroom.index', $data); // Trả về view danh sách điểm theo lớp học
    }

    // Hiển thị form thêm điểm
    public function add()
    {
        $data['subjects'] = Subject::all(); // Lấy danh sách môn học
        $data['students'] = User::where('role', 'student')->get(); // Lấy danh sách học sinh
        return view('scores.form')->with($data); // Trả về view form thêm điểm
    }

    // Thêm điểm mới
    public function create(Request $request)
    {
        try {
            $params = $request->all(); // Lấy tất cả dữ liệu từ request
            DB::transaction(function () use ($params) {
                MainModel::create($params); // Tạo mới điểm
            });
            return redirect()->route('scores.students')->withSuccess("Đã thêm"); // Chuyển hướng với thông báo thành công
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage())->withInput(); // Chuyển hướng với thông báo lỗi
        }
    }

    // Hiển thị form chỉnh sửa điểm
    public function edit($id)
    {
        $data['subjects'] = Subject::all(); // Lấy danh sách môn học
        $data['students'] = User::where('role', 'student')->get(); // Lấy danh sách học sinh
        $data['rec'] = MainModel::findOrFail($id); // Lấy thông tin điểm cần chỉnh sửa
        return view('scores.form')->with($data); // Trả về view form chỉnh sửa điểm
    }

    // Cập nhật điểm
    public function update(Request $request, $id)
    {
        try {
            $rec = MainModel::findOrFail($id); // Lấy thông tin điểm cần cập nhật
            $params = $request->all(); // Lấy tất cả dữ liệu từ request
            DB::transaction(function () use ($params, $rec) {
                $rec->update($params); // Cập nhật điểm
            });
            return redirect()->route('scores.students')->withSuccess("Đã cập nhật"); // Chuyển hướng với thông báo thành công
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage())->withInput(); // Chuyển hướng với thông báo lỗi
        }
    }

    // Xóa điểm
    public function delete($id)
    {
        try {
            $rec = MainModel::findOrFail($id); // Lấy thông tin điểm cần xóa
            $rec->delete(); // Xóa điểm
            return redirect()->back()->withSuccess("Đã xóa"); // Chuyển hướng với thông báo thành công
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage()); // Chuyển hướng với thông báo lỗi
        }
    }

    // Hiển thị danh sách yêu cầu chỉnh sửa điểm
    public function requestEdit() {
        $data['rows'] = RequestEditScore::all(); // Lấy tất cả yêu cầu chỉnh sửa điểm
        return view('scores.request_edit', $data); // Trả về view danh sách yêu cầu
    }

    // Hiển thị form thêm yêu cầu chỉnh sửa điểm
    public function requestEditAdd($id) {
        $data['rec'] = MainModel::findOrFail($id); // Lấy thông tin điểm cần yêu cầu chỉnh sửa
        return view('scores.request_edit_form', $data); // Trả về view form yêu cầu chỉnh sửa
    }

    // Tạo yêu cầu chỉnh sửa điểm
    public function requestEditCreate(Request $request, $id) {
        try {
            $params = $request->all(); // Lấy tất cả dữ liệu từ request
            $rec = MainModel::findOrFail($id); // Lấy thông tin điểm cần yêu cầu chỉnh sửa
            $params['score_id'] = $rec->id; // Gán ID điểm vào yêu cầu
            DB::transaction(function () use ($params) {
                RequestEditScore::create($params); // Tạo yêu cầu chỉnh sửa điểm
            });
            return redirect()->back()->withSuccess("Đã thêm"); // Chuyển hướng với thông báo thành công
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage())->withInput(); // Chuyển hướng với thông báo lỗi
        }
    }

    // Xóa yêu cầu chỉnh sửa điểm
    public function requestEditDelete($id)
    {
        try {
            $rec = RequestEditScore::findOrFail($id); // Lấy thông tin yêu cầu cần xóa
            $rec->delete(); // Xóa yêu cầu
            return redirect()->back()->withSuccess("Đã xóa"); // Chuyển hướng với thông báo thành công
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage()); // Chuyển hướng với thông báo lỗi
        }
    }
}
