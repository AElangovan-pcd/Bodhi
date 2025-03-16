<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('App.User.{id}.Course.{courseId}', function ($user, $id, $courseId) {
    return ((int) $user->id === (int) $id && $user->courses->contains($courseId));
});

Broadcast::channel('course-instructor.{courseId}', function ($user, $courseId) {
    $course = \App\Course::find($courseId);
    return $user->id === $course->owner;
});

Broadcast::channel('course-all.{courseId}', function ($user, $courseId) {
    $course = \App\Course::find($courseId);
    return $user->courses->contains($courseId);
});
