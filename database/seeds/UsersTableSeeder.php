<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Seat;

class   UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Create the test users.
        DB::table('users')->insert([
            'firstname' => config('labpal.development.admin_user_firstname'),
            'lastname'  => config('labpal.development.admin_user_lastname'),
            'email' => config('labpal.development.admin_user_email'),
            'password' => bcrypt(config('labpal.development.admin_pwd')),
            'admin' => 1,
            'instructor' => 1,
            'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        DB::table('users')->insert([
            'firstname' => 'Prof',
            'lastname'  => 'Test',
            'email' => 'prof@calpoly.edu',
            'password' => bcrypt('tF4wR3F%rvqO'),
            'admin' => 0,
            'instructor' => 1,
            'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        DB::table('users')->insert([
            'firstname' => 'Student',
            'lastname'  => 'Test',
            'email' => 'student@calpoly.edu',
            'password' => bcrypt('lQ3V^8TT&!1H'),
            'admin' => 0,
            'instructor' => 0,
            'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        DB::table('users')->insert([
            'firstname' => 'Student2',
            'lastname'  => 'Test2',
            'email' => 'student2@calpoly.edu',
            'password' => bcrypt('ppQo923fkV%n'),
            'admin' => 0,
            'instructor' => 0,
            'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        //Add them to the test course.
        DB::table('course_user')->insert([
            'course_id' => 1,
            'user_id'   => 1,
        ]);

        DB::table('course_user')->insert([
            'course_id' => 1,
            'user_id'   => 2,
        ]);

        DB::table('course_user')->insert([
            'course_id' => 1,
            'user_id'   => 3,
            'seat'      => "1a",
        ]);

        DB::table('course_user')->insert([
            'course_id' => 1,
            'user_id'   => 4,
            'seat'      => "2a",
        ]);

        //Give students a seat in the test course
        $seat = new Seat;
        $seat->course_id = 1;
        $seat->name = '1a';
        $seat->x = .2;
        $seat->y = .3;
        $seat->save();

        $seat = new Seat;
        $seat->course_id = 1;
        $seat->name = '2a';
        $seat->x = .2;
        $seat->y = .5;
        $seat->save();
    }
}
