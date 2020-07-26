<?php

namespace App\Http\Controllers;

use App\Console\Commands\UpdateOnlyShopifyFileCommand;
use App\Jobs\UpdateStockAndShopifyFilesCreateJob;
use App\Models\Setting;
use App\Models\SyncJob;

use Illuminate\Http\Request;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $setting = Setting::where('key','tax')->first();
        $lastUpdate = Setting::where('key','last-change')->first();

        return view('home' ,compact('setting' ,'lastUpdate'));
    }

    public function updateTax(Request $request){

       Setting::updateOrCreate(['key' => 'tax'], ['value' => $request->tax]);
       session()->flash('app_message', 'Tax has been updated successfully.');

       return back();
    }

    public function createStockExcelFIle(){

        try{
            $path = storage_path('app/temp/PVP-2.xlsx');

            return response()->download($path);
        }
        catch (\Exception $ex){

            session()->flash('app_error', 'No file found please try to generate file or contact admin.');
            return back();
        }

    }

    public function createShopifyOutPutExcelFile(){

        try{
            $path = storage_path('app/temp/Shopify-OUTPUT-FILE-Ready-to-Import.xlsx');

            return response()->download($path);
        }
        catch (\Exception $ex){

            session()->flash('app_error', 'No file found please try to generate file or contact admin.');
            return back();
        }
    }

    public function syncJobToUpdateFiles(){

        $jb = new UpdateOnlyShopifyFileCommand();
        $jb->createStockShopifyOutPutExcelFile();

        Setting::updateOrCreate(['key' => 'last-change'], ['value' => now()->toDateTimeString()]);

        SyncJob::where('type', 'stock-export')->update(['status' => 'completed']);

        SyncJob::truncate();

//        # check if there is product sync job
//        $activeJob = SyncJob::activeStatus('stock-export')->first();
//
//        if (!$activeJob) {
//            $newSyncJob = SyncJob::create(['type' => 'stock-export']);
//            SyncJob::where('id' ,'<>' ,$newSyncJob->id)->where('type' ,$newSyncJob->jobType)->delete();
//            UpdateStockAndShopifyFilesCreateJob::dispatch($newSyncJob->id, $newSyncJob->type);
//        }

        session()->flash('app_message', 'Your cron job has been scheduled and starting soon please wait.');

        return back();
    }


}
