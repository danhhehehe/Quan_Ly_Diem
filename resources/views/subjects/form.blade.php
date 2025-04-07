@extends('layout.base')
@section('page_title', isset($rec) ? "Cập nhật môn học: {$rec->name}" : 'Thêm môn học')
@section('slot')
<form id="form" class="text-start" method="POST"
    action="{{ isset($rec) && $rec !== null ? route('subjects.update', ['id' => $rec->id]) : route('subjects.create') }}">
    {{ csrf_field() }}
    @if(isset($rec) && isset($rec->id) && $rec->id)
        <input type="hidden" name="subject_id" value="{{ $rec->id }}">
    @else
        <input type="hidden" name="subject_id" value="">
    @endif
    <input type="hidden" name="id" value="{{ isset($subject) && $subject !== null ? $subject->id : '' }}">
    <label class="form-label mt-3">Tên môn *</label>
    <div class="input-group input-group-outline">
        <input type="text" name="name" class="form-control" required value="{{ isset($rec) && $rec ? $rec->name : old('name') }}">
    </div>

    <label class="form-label mt-3">Mã môn *</label>
    <div class="input-group input-group-outline">
        <input type="text" name="code" class="form-control" required value="{{ isset($rec) && $rec ? $rec->code : old('code') }}">
    </div>

    <label class="form-label mt-3">Kì học *</label>
    <div class="input-group input-group-outline">
        <input type="number" name="semester" class="form-control" required value="{{ isset($rec) && $rec ? $rec->semester : old('semester') }}">
    </div>

    <label class="form-label mt-3">Giáo viên</label>
    @php
    $teachers = $teachers ?? []; // Ensure $teachers is defined
    @endphp
    <div class="overflow-auto" style="max-height: 50vh;">
        @foreach($teachers as $row)
        @php
        $check = isset($teacher_subject_list) && $teacher_subject_list && in_array($row->id, $teacher_subject_list);
        @endphp
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="teacher_id[]"
                value="{{ isset($row->profile) && $row->profile ? $row->profile->id : '' }}" {{ $check ? 'checked' : '' }}>
            <label class="custom-control-label" for="customRadio1">{{$row->name}}</label>
        </div>
        @endforeach
    </div>

    <input type="submit" class="btn w-100 my-4 mb-2" 
       style="background-color: rgb(64, 143, 208); color: white;" 
       value="{{ isset($rec) ? 'Cập nhật' : 'Thêm' }}">

</form>
@stop