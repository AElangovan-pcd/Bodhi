<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index')->name('home');

Auth::routes(['verify' => true]);

Route::group(['middleware' => ['auth', 'verified']], function() {

    Route::get('/home', 'HomeController@index')->name('home');
    Route::get('/changePassword','HomeController@showChangePasswordForm');
    Route::post('/changePassword','HomeController@changePassword')->name('changePassword');
    Route::post('/changeName','HomeController@changeName');
    Route::get('join', 'CourseController@join_courses_page');
    Route::get('join/{cid}', 'CourseController@join_course_page');
    Route::post('join/{cid}', 'CourseController@join_course');

    Route::group(['prefix' =>'course/{cid}', 'middleware' => 'course'], function() {
        //Route::get('landing','CourseController@landing');
        Route::get('landing','CourseController@merged_landing_student');
        Route::post('raiseHand', 'HandsController@raise_hand');
        Route::post('lowerHand', 'HandsController@lower_hand');
        Route::post('placeInLine', 'HandsController@place_in_line');
        Route::get('drop','CourseController@drop_course');
        Route::post('changeSeat', 'CourseController@change_seat');
        Route::post('infoQuizSubmit', 'InfoController@submit_quiz_answers');
        Route::get('grades','GradeController@student_view');

        Route::group(['prefix' =>'assignment', 'middleware' => 'assignment'], function() {
            Route::get('/{aid}/view','AssignmentController@get_assignment_view');
            Route::get('/{aid}/share','SharingController@get_student_share_view');
            Route::post('/{aid}/evaluate','AssignmentController@evaluate_question');
            Route::post('/{aid}/evaluateMolecule','AssignmentController@evaluate_molecule_question');
            Route::post('/{aid}/submitWrittenAnswer','WrittenAnswerController@submit_written_answer');
            Route::post('/{aid}/previewWrittenAnswer','WrittenAnswerController@preview_written_answer');
            Route::post('/{aid}/submitComment','CommentsController@submit_comment');
            Route::get('/{aid}/newValues/{qid}','AssignmentController@new_computed_values');
            Route::get('/{aid}/quiz_next', 'AssignmentController@quiz_next_piece');
        });

        Route::group(['prefix' =>'polls'], function() {
            Route::post('check_active', 'PollController@check_active');
            Route::get('participate', 'PollController@take_poll');
            Route::post('submit','PollController@answer_poll');
            Route::post('get','PollController@get_poll');
        });

        Route::group(['prefix' =>'forum'], function() {
            Route::get('landing', 'ForumController@forum_landing');
            Route::get('create', 'ForumController@create_forum');
            Route::get('edit/{fid}', 'ForumController@edit_forum');
            Route::get('delete/{fid}','ForumController@delete_forum');
            Route::post('save_forum', 'ForumController@save_forum');
            Route::get('view/{fid}','ForumController@view_forum');
            Route::post('save_forum_answer','ForumController@save_forum_answer');
            Route::get('deleteResponse/{rid}','ForumController@delete_forum_response');
            Route::post('response_vote','ForumController@response_vote');
            Route::post('response_details','ForumController@response_details');
            Route::post('topic_subscription', 'ForumController@topic_subscription');
            Route::post('forum_subscription', 'ForumController@forum_subscription');
        });

        Route::group(['prefix' =>'review'], function() {
            Route::get('{rid}/view','ReviewController@view_review');
            Route::post('{rid}/submit','ReviewController@submit_upload');
            Route::post('{rid}/submitRev','ReviewController@submit_revision_upload');
            Route::post('{rid}/submitResponse','ReviewController@submit_response_upload');
            Route::get('{rid}/mySubmission','ReviewController@download_submission');
            Route::get('{rid}/myRevision','ReviewController@download_revision');
            Route::get('{rid}/myResponse','ReviewController@download_response');
            Route::get('{rid}/getFeedback','ReviewController@download_feedback');
            Route::get('{rid}/complete/{jid}','ReviewController@complete_review');
            Route::get('{rid}/complete/{jid}/download','ReviewController@download_job_submission');
            Route::post('{rid}/complete/{jid}/save','ReviewController@save_complete');
            Route::get('{rid}/responseView/{jid}','ReviewController@download_job_response');
            Route::get('{rid}/results','ReviewController@view_results');
        });

        Route::group(['prefix' => 'files'], function() {
            Route::get('download/{cfid}', 'FileController@download_file');
        });

        //Learning Assistant Routes
        Route::group(['prefix' =>'LA', 'middleware' => 'assistant'], function() {

            Route::group(['middleware'=>'assistantEdit'], function() {
                Route::get('assignment/new','AssignmentController@new_assignment');
                Route::post('assignment/saveEdit', 'AssignmentController@save_and_preview_assignment');  //Outside group to avoid middleware in case the assignment does not have an id yet.
                Route::get('assignment/{aid}/edit','AssignmentController@edit_assignment');
            });

            Route::group(['prefix' =>'review'], function() {
                Route::get('landing','ReviewController@landing');
                Route::get('{rid}/monitor','ReviewController@monitor_review');
                Route::get('{rid}/stats','ReviewController@view_results_stats');
                Route::post('{rid}/uploadFeedback', 'ReviewController@upload_feedback');
                Route::get('{rid}/downloadSubmission/{sid}','ReviewController@download_user_submission');
                Route::get('{rid}/downloadAllSubmissions','ReviewController@download_all_submissions');
                Route::get('{rid}/downloadAllRevisions','ReviewController@download_all_revisions');
                Route::get('{rid}/downloadAllResponses','ReviewController@download_all_responses');
                Route::get('{rid}/downloadRevision/{sid}','ReviewController@download_user_revision');
                Route::get('{rid}/downloadResponse/{sid}','ReviewController@download_user_response');
                Route::get('{rid}/downloadFeedback/{fid}','ReviewController@download_user_feedback');
                Route::get('{rid}/complete/{jid}','ReviewController@complete_review');
                Route::get('{rid}/complete/{jid}/download','ReviewController@download_job_submission');
                Route::post('{rid}/complete/{jid}/save','ReviewController@save_complete');
                Route::get('{rid}/studentResults/{sid}','ReviewController@view_student_results');
                Route::get('results/{rid}','ReviewController@review_results');
            });

            Route::get('classroomImage','CourseController@get_image');

            Route::group(['prefix' =>'assignment', 'middleware' => 'assignment'], function() {
                //Results Routes
                Route::group(['prefix' => '{aid}/results'], function () {
                    Route::get('old','ResultsController@course_assignment_results'); //TODO Deprecate
                    Route::get('lazy', 'ResultsController@lazy_results');  //TODO Deprecate
                    Route::get('main', 'ResultsController@lazy_results');
                    Route::post('load_answers', 'ResultsController@load_answers');
                    Route::post('load_all_answers', 'ResultsController@load_all_answers');
                    Route::post('load_student_answers', 'ResultsController@load_student_answers');
                    Route::post('student_details', 'ResultsController@student_details');
                    Route::get('student_view/{sid}', 'AssignmentController@load_into_student_view');
                    Route::get('classroom', 'ResultsController@classroom_results');
                    Route::get('share', 'SharingController@get_student_share_view')->name('share');
                    Route::post('share/share_variable', 'SharingController@share_variable');
                    Route::get('allResults', 'ResultsController@all_results');
                    Route::get('stats', 'ResultsController@stats');

                    Route::group(['prefix' =>'writtenAnswers'], function() {
                        Route::get('/','WrittenAnswerController@written_answer_view');
                        Route::post('submit_response', 'WrittenAnswerController@submit_response');
                        Route::post('submit_feedback', 'WrittenAnswerController@submit_feedback');
                        Route::post('save_responses', 'WrittenAnswerController@save_responses');
                        Route::post('save_options', 'WrittenAnswerController@save_options');
                        Route::post('retry/{wid}', 'WrittenAnswerController@retry');
                        Route::post('regrade/{wid}', 'WrittenAnswerController@regrade');
                    });
                });
            });
        });
        Route::any('hands', array(
            'uses' => 'HandsController@hands_for_course',
            'as'   => 'get_hands_for_course'
        ))->middleware('assistant');
        Route::any('dismiss_hand/{sid}', 'HandsController@dismiss_hand')->middleware('assistant');
    });

    Route::group(['prefix' =>'admin', 'middleware' => 'admin'], function() {
        Route::get('manageInstructors','AdminController@manage_instructors');
        Route::post('addInstructor','AdminController@add_instructor');
        Route::get('revokeInstructor/{id}', 'AdminController@revoke_instructor');
        Route::get('manageCourses','AdminController@manage_courses');
        Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
    });

    Route::group(['prefix' =>'instructor', 'middleware' => 'instructor'], function() {
        Route::get('home', 'HomeController@instructor_index')->name('home');
        Route::get('createCourse', 'CourseController@create_course_page');
        Route::post('createCourse', 'CourseController@create_course');
        Route::get('manageStudents','AdminController@manage_students');
        Route::post('manageStudents/select','AdminController@manage_students_select');
        Route::get('manageStudents/resetPassword/{sid}','AdminController@reset_student_password');

        Route::group(['prefix' =>'course/{cid}', 'middleware' => 'course'], function() {
            //Course tools
            //Route::get('landing','CourseController@instructor_landing');
            Route::get('landing', 'CourseController@merged_landing_instructor');
            Route::post('uploadAssignment','AssignmentController@import_assignment');
            Route::get('duplicate','CourseController@duplicate_course');
            Route::post('infoQuizSubmit', 'InfoController@submit_quiz_answers');
            Route::get('totals', 'ResultsController@course_totals');
            Route::post('toggleLinkable', 'LinkRequestController@toggle_linkable');
            Route::post('linkRequest', 'LinkRequestController@link_request');

            //Course administration
            Route::post('updateDetails', 'CourseController@update_details');
            Route::post('updateAssignmentStates', 'CourseController@update_assignment_states');
            Route::get('deactivate','CourseController@deactivate_course');
            Route::get('activate','CourseController@activate_course');
            Route::get('delete','CourseController@delete_course');
            Route::get('archive', 'CourseController@archive');
            Route::get('unArchive', 'CourseController@unArchive');
            Route::post('updateOrder','CourseController@update_assignment_order');
            Route::get('student/{sid}/details', 'CourseController@get_student_details');
            Route::post('student/{sid}/changeSeat', 'CourseController@change_student_seat');
            Route::post('student/{sid}/changeMultiplier', 'CourseController@change_student_multiplier');
            Route::get('student/{sid}/resetPassword', 'CourseController@reset_student_password');
            Route::get('student/{sid}/dropStudent', 'CourseController@drop_student');
            Route::get('manageAssistants','CourseController@manage_assistants');
            Route::post('addAssistant','CourseController@add_assistant');
            Route::get('revokeAssistant/{id}', 'CourseController@revoke_assistant');
            Route::get('toggleAssistantEdit','CourseController@toggle_assistant_edit');

            //Classroom layout routes
            Route::get('classroomLayout','CourseController@classroom_layout');
            Route::post('saveLayout','CourseController@save_layout');
            Route::post('uploadLayoutImage','CourseController@upload_layout_image');
            Route::get('classroomImage','CourseController@get_image');
            Route::post('loadTemplate','CourseController@load_template');
            Route::post('removeTemplate','CourseController@remove_template');

            Route::get('assignment/new','AssignmentController@new_assignment');
            Route::post('assignment/saveEdit', 'AssignmentController@save_and_preview_assignment');  //Outside group to avoid middleware in case the assignment does not have an id yet.
            Route::get('assignments', 'CourseController@assignment_list');
            Route::post('saveAssignments', 'CourseController@save_assignment_list');
            //Assignment routes
            Route::group(['prefix' =>'assignment', 'middleware' => 'assignment'], function() {

                Route::get('/{aid}/edit','AssignmentController@edit_assignment');
                Route::get('/{aid}/view','AssignmentController@get_assignment_view');
                Route::get('/{aid}/activate','AssignmentController@activate_assignment');
                Route::get('/{aid}/activate_including_linked','AssignmentController@activate_assignment_including_linked');
                Route::get('/{aid}/deactivate','AssignmentController@deactivate_assignment');
                Route::get('/{aid}/deactivate_including_linked','AssignmentController@deactivate_assignment_including_linked');
                Route::get('/{aid}/disable','AssignmentController@disable_assignment');
                Route::get('/{aid}/disable_including_linked','AssignmentController@disable_assignment_including_linked');
                Route::get('/{aid}/duplicate','AssignmentController@duplicate_assignment');
                Route::get('/{aid}/enable','AssignmentController@enable_assignment');
                Route::get('/{aid}/enable_including_linked','AssignmentController@enable_assignment_including_linked');
                Route::get('/{aid}/delete','AssignmentController@remove_assignment');
                Route::get('/{aid}/export','AssignmentController@export_xml');
                Route::get('/{aid}/deleteComment/{commentId}','CommentsController@delete_comment');
                Route::get('/{aid}/share','SharingController@get_student_share_view')->name('share');
                Route::post('/{aid}/update_extension', 'AssignmentController@update_extension');
                Route::get('/{aid}/pushToLinkedCourses', 'AssignmentController@push_to_linked_courses');
                Route::get('/{aid}/unlink', 'AssignmentController@unlink_from_parent');
                Route::post('/{aid}/evaluate_for_user/{user}', 'AssignmentController@evaluate_for_user');
                Route::post('/{aid}/submit_written_for_user/{user}', 'WrittenAnswerController@submit_written_for_user');
                Route::post('/{aid}/evaluate_molecule_for_user/{user}', 'AssignmentController@evaluate_molecule_for_user');

                Route::get('/{aid}/newValues/{qid}','AssignmentController@new_computed_values');
                Route::post('/{aid}/newValuesForUser/{qid}/{uid}','AssignmentController@new_computed_values_for_user');
                Route::get('/{aid}/quiz_next', 'AssignmentController@quiz_next_piece');

                //Route::post('/{aid}/evaluateForUser/{user}','AssignmentController@evaluate_for_user');
                //Route::any('/{aid}/reScoreForUser/{qid}/{user}', 'AssignmentController@rescore_for_user');
                //Route::any('/{aid}/reScoreQuestion/{qid}', 'AssignmentController@rescore_question');

                //Results Routes
                Route::group(['prefix' =>'{aid}/results'], function() {
                    Route::get('old','ResultsController@course_assignment_results');  //TODO Deprecate
                    Route::get('linked', 'ResultsController@linked_assignment_results');
                    Route::get('lazy', 'ResultsController@lazy_results');  //TODO Deprecate
                    Route::get('main', 'ResultsController@lazy_results');
                    Route::post('load_answers', 'ResultsController@load_answers');
                    Route::post('load_all_answers', 'ResultsController@load_all_answers');
                    Route::post('load_student_answers', 'ResultsController@load_student_answers');
                    Route::post('student_details','ResultsController@student_details');
                    Route::get('student_view/{sid}','AssignmentController@load_into_student_view');
                    Route::get('classroom','ResultsController@classroom_results');
                    Route::post('share/share_variable','SharingController@share_variable');
                    Route::get('allResults', 'ResultsController@all_results');
                    Route::get('stats', 'ResultsController@stats');

                    //Should build entirely new quiz module
                    Route::get('generate_quizzes', 'AssignmentController@generate_quizzes');
                    Route::get('generate_quiz_for_student/{sid}', 'AssignmentController@generate_quiz_for_student');
                    Route::get('generate_quizzes_including_linked', 'AssignmentController@generate_quizzes_including_linked');
                    Route::get('generate_missing_quizzes', 'AssignmentController@generate_missing_quizzes');
                    Route::get('generate_missing_quizzes_including_linked', 'AssignmentController@generate_missing_quizzes_including_linked');
                    Route::get('update_quiz_timings', 'AssignmentController@update_quiz_timings');
                    Route::get('update_quiz_timings_including_linked', 'AssignmentController@update_quiz_timings_including_linked');
                    Route::post('update_quiz_settings', 'AssignmentController@update_quiz_settings');
                    Route::post('update_student_quiz_detail', 'AssignmentController@update_student_quiz_detail');
                    Route::post('update_batch_quiz_detail', 'AssignmentController@update_batch_quiz_detail');
                    Route::post('update_student_quiz_status', 'AssignmentController@update_student_quiz_status');
                    Route::post('update_student_quiz_page_status', 'AssignmentController@update_student_quiz_page_status');
                    Route::post('rescore_question', 'AssignmentController@rescore_question');
                    Route::post('rescore_question_including_linked', 'AssignmentController@rescore_question_including_linked');
                    Route::get('allow_quiz_review', 'AssignmentController@allow_quiz_review');
                    Route::get('allow_quiz_review_including_linked', 'AssignmentController@allow_quiz_review_including_linked');
                    Route::get('disallow_quiz_review', 'AssignmentController@disallow_quiz_review');
                    Route::get('disallow_quiz_review_including_linked', 'AssignmentController@disallow_quiz_review_including_linked');
                    Route::post('toggle_quiz_review_for_student', 'AssignmentController@toggle_quiz_review_for_student');
                    Route::get('release_deferred_feedback', 'AssignmentController@release_deferred_feedback');
                    Route::get('release_deferred_feedback_including_linked', 'AssignmentController@release_deferred_feedback_including_linked');
                    Route::get('redefer_feedback', 'AssignmentController@redefer_feedback');
                    Route::get('redefer_feedback_including_linked', 'AssignmentController@redefer_feedback_including_linked');
                    Route::post('update_deferred_state_for_student', 'AssignmentController@update_deferred_state_for_student');

                    Route::group(['prefix' =>'writtenAnswers'], function() {
                        Route::get('/','WrittenAnswerController@written_answer_view');
                        Route::post('submit_response', 'WrittenAnswerController@submit_response');
                        Route::post('submit_feedback', 'WrittenAnswerController@submit_feedback');
                        Route::post('save_responses', 'WrittenAnswerController@save_responses');
                        Route::post('save_options', 'WrittenAnswerController@save_options');
                        Route::post('retry/{wid}', 'WrittenAnswerController@retry');
                        Route::post('regrade/{wid}', 'WrittenAnswerController@regrade');
                    });
                });
            });

            Route::group(['prefix' =>'polls'], function() {
                Route::get('landing','PollController@landing');
                Route::get('create','PollController@create_poll');
                Route::post('delete', 'PollController@delete_poll');
                Route::post('save','PollController@save_poll');
                Route::get('edit/{pid}','PollController@edit_poll');
                Route::post('activate', 'PollController@activate_poll');
                Route::post('complete','PollController@complete_poll');
                Route::post('restart','PollController@restart_poll');
                Route::post('duplicate','PollController@duplicate_poll');
                Route::get('results/{pid}','PollController@poll_results');
                Route::post('copy','PollController@copy_poll');
                Route::get('export','PollController@export_xml');
                Route::post('import', 'PollController@import_xml');
                Route::post('updateOrder','PollController@update_poll_order');
                Route::get('all_results', 'PollController@all_poll_results');
            });

            Route::group(['prefix' =>'review'], function() {
                Route::get('{rid}/view','ReviewController@view_review');
                Route::get('landing','ReviewController@landing');
                Route::get('create','ReviewController@create_review');
                Route::post('delete', 'ReviewController@delete_review');
                Route::post('saveAssignment','ReviewController@save_review');
                Route::post('{rid}/saveSchedules', 'ReviewController@save_schedule');
                Route::get('{rid}/edit','ReviewController@edit_review');
                Route::get('{rid}/monitor','ReviewController@monitor_review');
                Route::get('{rid}/activate', 'ReviewController@activate_review');
                Route::get('{rid}/duplicate', 'ReviewController@duplicate_review');
                Route::get('{rid}/delete', 'ReviewController@delete_review');
                Route::get('{rid}/export', 'ReviewController@export_json');
                Route::post('uploadAssignment', 'ReviewController@import_json');
                Route::get('{rid}/stats','ReviewController@view_results_stats');
                Route::get('changeStatus/{rid}/{state}','ReviewController@change_state');
                Route::get('{rid}/changeStatus/{state}','ReviewController@change_state');
                Route::post('{rid}/generateReviewers', 'ReviewController@generate_reviewers');
                Route::post('{rid}/uploadForStudent','ReviewController@submit_upload_for_student');
                Route::get('{rid}/deleteStudentSubmission/{sid}','ReviewController@delete_student_submission');
                Route::post('{rid}/uploadFeedback', 'ReviewController@upload_feedback');
                Route::get('{rid}/downloadSubmission/{sid}','ReviewController@download_user_submission');
                Route::get('{rid}/downloadAllSubmissions','ReviewController@download_all_submissions');
                Route::get('{rid}/downloadAllRevisions','ReviewController@download_all_revisions');
                Route::get('{rid}/downloadAllResponses','ReviewController@download_all_responses');
                Route::get('{rid}/downloadRevision/{sid}','ReviewController@download_user_revision');
                Route::get('{rid}/downloadResponse/{sid}','ReviewController@download_user_response');
                Route::get('{rid}/downloadFeedback/{fid}','ReviewController@download_user_feedback');
                Route::get('{rid}/complete/{jid}','ReviewController@complete_review');
                Route::get('{rid}/complete/{jid}/download','ReviewController@download_job_submission');
                Route::post('{rid}/complete/{jid}/save','ReviewController@save_complete');
                Route::get('{rid}/studentResults/{sid}','ReviewController@view_student_results');
                Route::post('restart','ReviewController@restart_review');
                Route::post('duplicate','ReviewController@duplicate_review');
                Route::get('results/{rid}','ReviewController@review_results');
                Route::post('copy','ReviewController@copy_review');
                Route::get('export','ReviewController@export_xml');
                Route::post('import', 'ReviewController@import_xml');

                Route::get('{rid}/seedTests','ReviewController@seed_test_reviews');
            });

            Route::group(['prefix' => 'info'], function() {
                Route::get('landing', 'InfoController@landing');
                Route::post('saveInfo', 'InfoController@save_infos');
                Route::post('saveSchedules', 'InfoController@save_schedules');
                Route::get('results/{qid}', 'InfoController@info_quiz_results');
                Route::get('regrade/{qid}','InfoController@regrade_info_quiz');
                Route::get('export','InfoController@export_json');
                Route::post('uploadInfo','InfoController@import_json');
                Route::get('pushToLinkedCourses/{iid}', 'InfoController@push_to_linked_courses');
            });

            Route::group(['prefix' =>'scheduler'], function() {
                Route::get('landing', 'AssignmentController@scheduler_landing');
                Route::post('saveSchedules', 'AssignmentController@save_schedules');
            });

            Route::group(['prefix' => 'grades'], function() {
                Route::get('landing', 'GradeController@landing');
                Route::post('saveGrades', 'GradeController@save_grades');
            });

            Route::group(['prefix' => 'files'], function() {
                Route::get('landing', 'FileController@landing');
                Route::post('saveFileLayout', 'FileController@save_file_layout');
                Route::post('upload/{fid}', 'FileController@upload_to_folder');
                Route::post('update/{cfid}', 'FileController@update_file');
                Route::post('deleteFile', 'FileController@delete_file');
                Route::get('download/{cfid}', 'FileController@download_file');
            });

            Route::group(['prefix' =>'forum'], function() {
                Route::get('landing', 'ForumController@forum_landing');
                Route::get('create', 'ForumController@create_forum');
                Route::get('edit/{fid}', 'ForumController@edit_forum');
                Route::get('delete/{fid}','ForumController@delete_forum');
                Route::post('save_forum', 'ForumController@save_forum');
                Route::get('view/{fid}','ForumController@view_forum');
                Route::post('save_forum_answer','ForumController@save_forum_answer');
                Route::get('deleteResponse/{rid}','ForumController@delete_forum_response');
                Route::post('response_vote','ForumController@response_vote');
                Route::post('response_details','ForumController@response_details');
                Route::post('topic_subscription', 'ForumController@topic_subscription');
                Route::post('forum_subscription', 'ForumController@forum_subscription');
                Route::post('endorse', 'ForumController@endorse');
                Route::get('stats','ForumController@new_stats');
                //Route::get('new_stats','ForumController@new_stats');
            });

        });


    });
});
