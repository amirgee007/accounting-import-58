<?php

namespace App\Providers;

use App\Models\SyncJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;


class AppServiceProvider extends ServiceProvider
{
    public function getUnserialized($str) {
        return  unserialize($str);
    }

    public function syncJobHandle($payload, $data)
    {
        $unsterilized = $this->getUnserialized($payload['data']['command']);
        if (isset($unsterilized->syncJobId)) {
            $updateJob = SyncJob::where('id', $unsterilized->syncJobId)->update($data);
            if ($updateJob) {
                //pusher notifications
                \Log::info($unsterilized->syncJobType . ' sync job is ' . $data['status']);
                return true;
            }
        }
        return false;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale(config('app.locale'));

        \Illuminate\Database\Schema\Builder::defaultStringLength(191);

        Queue::before(function (JobProcessing $event) {
            $payload = $event->job->payload();
            $this->syncJobHandle($payload, ['status' => 'active']);

            if(method_exists($payload['data']['commandName'],'onStart')) {
                $unsterilized = $this->getUnserialized($payload['data']['command']);
                call_user_func($payload['data']['commandName'] . '::onStart', $unsterilized);
            }
        });

        Queue::after(function (JobProcessed $event) {
            $payload = $event->job->payload();
            $this->syncJobHandle($payload,
                ['status' => 'completed',
                    'completed_at' => Carbon::now()->toDateTimeString()]
            );

            if(method_exists($payload['data']['commandName'],'onComplete')) {
                $unsterilized = $this->getUnserialized($payload['data']['command']);
                call_user_func($payload['data']['commandName'] . '::onComplete', $unsterilized);
            }
        });

    }
}
