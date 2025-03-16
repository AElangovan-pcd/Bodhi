<?php

namespace App\Jobs;

    use App\User;
    use Illuminate\Bus\Queueable;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Auth\Notifications\VerifyEmail;
    use Illuminate\Support\Facades\Redis;

    class QueuedVerifyEmail implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

        protected $user;

        public $tries = 6;

        public function __construct(User $user)
        {
            $this->user = $user;
        }

        public function handle()
        {
            Redis::throttle('email')->allow(30)->every(60)->then(function () {
                $this->user->notify(new VerifyEmail);
            }, function() {
               return $this->release(20);
            });
        }
    }
