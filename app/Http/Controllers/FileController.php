<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use View;
use App\Course;
use App\Folder;
use Illuminate\Support\Facades\Input;
use Validator;
use Response;
use App\CourseFile;
use Storage;
use Auth;

class FileController extends Controller
{
    public function landing($course_id) {
        $course = Course::find($course_id);
        $view = View::make('instructor.files.fileLanding');
        $view->course = $course;
        $folders = Folder::where('course_id',$course_id)->orderBy('order')
            ->with(['course_files' => function($query) {
                $query->orderBy('order');
            }])->get();

        $data = array(
            "course"      => $course,
            "folders"      => $folders,
        );

        $view->data = json_encode($data);
        return $view;
    }

    public function save_file_layout($cid) {
        $data = json_decode(Input::get('data'));
        $course = Course::find($cid);
        $fids = [];
        $i=0;
        foreach($data->folders as $folder) {
            array_push($fids, $this->save_folder($folder, $cid, $i));
            $i++;
        }
        $this->delete_folders($course->folders, $fids);
        return json_encode(['status' => 'success', 'fids' => $fids]);
    }

    private function save_folder($folder, $cid, $order) {
        if($folder->id == -1) {
            $newFolder = new Folder();
            $newFolder->course_id = $cid;
        }
        else
            $newFolder = Folder::find($folder->id);

        $newFolder->name = $folder->name;
        $newFolder->visible = $folder->visible;
        $newFolder->order = $order;
        $newFolder->options = $folder->options;

        $newFolder->save();

        $this->updateFileNames($folder);

        if(isset($folder->sortedFiles)) {
            $this->update_file_orders($folder);
        }

        return $newFolder->id;
    }

    private function updateFileNames($folder) {
        foreach($folder->course_files as $cf) {
            $file = CourseFile::find($cf->id);
            $file->name = $cf->name;
            $file->save();
        }
    }

    private function update_file_orders($folder) {
        $i = 0;
        foreach($folder->sortedFiles as $fid) {
            $file = CourseFile::find($fid);
            $file->folder_id = $folder->id;
            $file->order = $i;
            $file->save();
            $i++;
        }
    }

    private function delete_folders($folders, $fids) {
        foreach($folders as $folder) {
            if(!in_array($folder->id, $fids))
                $folder->delete();
        }
    }

    public function upload_to_folder($cid, $fid, Request $request) {
        $input = Input::all();

        $rules = array(
            'file' => 'max:25000',
        );

        $validation = Validator::make($input, $rules);

        if ($validation->fails())
        {
            return Response::make($validation->errors()->first(), 400);
        }

        $file = Input::file('file');

        $path = $file->store('files/'.$cid);

        $f = new CourseFile();

        $f->course_id = $cid;
        $f->folder_id = $fid;
        $f->name = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME);
        $f->filename = $file->getClientOriginalName();
        $f->extension = $file->getClientOriginalExtension();
        $f->location = $path;
        $f->order = $request->input('order');
        $f->save();

        return Response::json([
            'status' => 'success',
            'file' => $f,
            ],200);
    }

    public function update_file($cid, $cfid, Request $request) {
        $input = Input::all();

        $rules = array(
            'file' => 'max:10240',
        );

        $validation = Validator::make($input, $rules);

        if ($validation->fails())
        {
            return Response::make($validation->errors()->first(), 400);
        }

        $file = Input::file('file');

        $f = CourseFile::find($cfid);

        if($f->course_id != $cid)
            return Response::json([
                'status' => 'fail',
                'message' => 'File is not part of this course '.$f->course_id.' '.$cid,
            ], 400);

        //Delete the old file
        Storage::delete($f->location);

        //Store the new file
        $path = $file->store('files/'.$cid);

        //Update location and name
        $f->name = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME);
        $f->filename = $file->getClientOriginalName();
        $f->extension = $file->getClientOriginalExtension();
        $f->location = $path;
        $f->save();

        return Response::json([
            'status' => 'success',
            'file' => $f,
        ],200);
    }

    public function delete_file($cid, Request $request) {
        $file = CourseFile::find($request->input('fid'));
        Storage::delete($file->location);
        $file->delete();
        return Response::json([
            'status' => 'success',
        ],200);
    }

    public function download_file($cid, $cfid) {
        $parent_cid = Course::select('parent_course_id')->find($cid)->parent_course_id;
        $file = CourseFile::find($cfid);


        if(!($file->course_id == $cid || $file->course_id == $parent_cid) || (!$file->visible && !Auth::user()->instructor)) {
            abort(403, 'Unauthorized action.');
        }

        return Storage::download($file->location, $file->filename);
        //return response()->download(storage_path().'/'.$file->location, $file->name.'.'.$file->filename);
    }
}
