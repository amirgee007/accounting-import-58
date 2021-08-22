<?php

namespace App\Http\Controllers;

use App\Exports\ApiLogsExcelFileExport;
use App\Jobs\UpdateStockAndShopifyFilesCreateJob;
use App\Mail\GlobalEmailAll;
use App\Models\ApiErrorLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TestController extends Controller
{
    public function index2()
    {
        ini_set('max_execution_time', 30000000); //300 seconds = 5 minutes


        $totalImagesFound = 0;
        $totalImagesNotFound = 0;
        $totalProductProcessed = 0;


        #(new UpdateStockAndShopifyFilesCreateJob(1,1))->handle();

        $counter = [
            123,
            120,
            11,
        ];

        $content = 'Hi, Your images has been processed and API is working fine checked at '.now()->toDateTimeString();

        $email = Setting::where('key','adminEmail')->first();

        \Mail::to([[ 'email' => $email ? $email->value : 'amirseersol@gmail.com', 'name' => 'Amir' ],
        ])->bcc('amirseersol@gmail.com')->send(new GlobalEmailAll("Images has been processed.", $content, $counter));


        dd('ok nOOOOw');

        ini_set('memory_limit', -1);

    }
}
