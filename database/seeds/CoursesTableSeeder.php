<?php

use Illuminate\Database\Seeder;
use App\Course;

class CoursesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $course = new Course;
        $course->name = 'Test Course';
        $course->owner = 1;
        $course->key = 'test';
        $course->active = 1;
        //$course->classroom_id = 1;
        $course->save();
    }
}
