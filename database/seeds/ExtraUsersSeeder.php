<?php

use Illuminate\Database\Seeder;
use App\User;
use Carbon\Carbon;

class ExtraUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=3; $i<=20; $i++) {
            $user = new User();
            $user->firstname = 'Student' . $i;
            $user->lastname = 'Test' . $i;
            $user->email = 'student' . $i . '@calpoly.edu';
            $user->password = bcrypt('Demo2019student' . $i);
            $user->admin = 0;
            $user->instructor = 0;
            $user->email_verified_at = Carbon::now()->format('Y-m-d H:i:s');
            $user->save();

            DB::table('course_user')->insert([
                'course_id' => 1,
                'user_id'   => $user->id,
            ]);
        }
    }
}
