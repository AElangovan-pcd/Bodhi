<?php

namespace App\Http\Controllers;

use App\Course;
use App\LinkRequest;
use Illuminate\Http\Request;

class LinkRequestController extends Controller
{

    public function toggle_linkable($cid) {
        try {
            $course = Course::select('id', 'linkable')->find($cid);
            $linkable = $course->toggleLinkable();
            return ['status' => 'success', 'linkable' => $linkable];
        }
        catch(\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function link_request($cid, Request $request) {
        try {
            switch($request->input('action')) {
                case 'request':
                    $msg = $this->make_link_request($cid, $request->input('other_cid'));
                    break;
                case 'withdraw':
                    $msg = $this->delete_link_request($cid, $request->input('other_cid'));
                    break;
                case 'accept':
                    $msg = $this->accept_link_request($cid, $request->input('other_cid'));
                    break;
                case 'reject':
                    $msg = $this->reject_link_request($cid, $request->input('other_cid'));
                    break;
                case 'unlink_child':
                    $msg = $this->unlink_child_course($cid, $request->input('other_cid'));
                    break;
                case 'unlink_parent':
                    $msg = $this->unlink_child_course($request->input('other_cid'),$cid);
                    break;
                default:
                    throw new \Exception('No valid action submitted.');
            }
            $course = Course::select('id','parent_course_id')
                ->with('linked_parent_course')
                ->with('linked_courses')
                ->with('link_requests_incoming.child_course')
                ->find($cid);
            return ['status' => 'success', 'msg' => $msg, 'course' => $course];
        }
        catch(\Exception $e) {
            return ['status' => 'error', 'msg' => $e->getMessage()];
        }
    }

    private function make_link_request($child_id, $parent_id) {
        LinkRequest::create([
            "child_id" => $child_id,
            "parent_id" => $parent_id,
            "status" => "requested",
        ]);
        return LinkRequest::where('child_id', $child_id)->with('parent_course')->get();
    }

    private function delete_link_request($child_id, $parent_id) {
        LinkRequest::where([
            "child_id" => $child_id,
            "parent_id" => $parent_id,
        ])->delete();
        return LinkRequest::where('child_id', $child_id)->with('parent_course')->get();
    }

    private function accept_link_request($parent_id, $child_id) {
        Course::find($child_id)->update(['parent_course_id' => $parent_id]);
        LinkRequest::where([
            "child_id" => $child_id,
            "parent_id" => $parent_id,
        ])->delete();
        return LinkRequest::where('parent_course_id', $parent_id)->with('child_course');
    }

    private function reject_link_request($parent_id, $child_id) {

    }

    private function unlink_child_course($cid, $child_id) {
        $course = Course::find($child_id);
        if(!($course->id == $child_id || $course->parent_course_id == $cid))
            throw new \Exception('Not authorized to unlink.');
        $course->update(['parent_course_id' => null]);
        return null;
    }
}
