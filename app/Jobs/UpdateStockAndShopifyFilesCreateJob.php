<?php

namespace App\Jobs;

use App\Console\Commands\UpdateStockAndShopifyFIlesCommand;
use App\Mail\GlobalEmailAll;
use App\Models\SyncJob;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Exports\ShopifyImportFileExport;
use App\Exports\StockFileExport;
use App\Models\Setting;
use App\Services\Shopify\HttpApiRequest;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class UpdateStockAndShopifyFilesCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $count = 0;
    public  $syncJobId;
    public  $syncJobType;
    public  $btnClick;

    public  $tries  =  3;

    public function __construct($jobId, $jobType, $btnClick)
    {
        $this->syncJobId = $jobId;
        $this->syncJobType = $jobType;
        $this->btnClick = $btnClick;
    }


    public function handle(){

        Log::emergency(now()->toDateTimeString() . ' started updated JOB now for all the things...!New feb2022');

        (new UpdateStockAndShopifyFIlesCommand())->createStockShopifyOutPutExcelFile($this->btnClick);
    }

    public function failed(\Exception $exception)
    {
        SyncJob::where('id', $this->syncJobId)->update([
            'status' => 'failed',
            'last_error_message' => $exception->getMessage()
        ]);

        \Log::error($this->syncJobType . ' sync job is failed');
    }

}
