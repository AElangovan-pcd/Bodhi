<?php

namespace App\Http\Controllers;

use App\Question;
use Illuminate\Http\Request;
//use Mews\Purifier\Facades\Purifier;
use Stevebauman\Purify\Facades\Purify as Purifier;
use App\Comment;
use Auth;
use App\Events\NewComment;
use Redirect;

class CommentsController extends Controller
{
    public function submit_comment($cid, $aid, Request $request) {
        $question = Question::find($request->get('question_id'));
        if($question->assignment->id != $aid)
            return 'question not in assignment';

        $c = new Comment;
        $c->question_id = $request->get('question_id');
        $c->user_id = Auth::id();
        $c->contents = Purifier::clean($request->get('contents'));
        $c->save();

        broadcast(new NewComment($cid, $aid, $c->question_id, $c->id, $c->contents));
        return "posted";
    }

    public function delete_comment($cid, $aid, $comment_id) {
        $comment = Comment::find($comment_id);
        if($comment->question->assignment->id != $aid)
            return "comment not part of assignment";
        $comment->delete();
        return Redirect::back()->with('success','Comment Deleted');
    }
}
