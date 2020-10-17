<?php

namespace App\Http\Controllers;

use App\Console\Commands\UpdateOnlyShopifyFileCommand;
use App\Console\Commands\UpdateStockAndShopifyFIlesCommand;
use App\Jobs\UpdateStockAndShopifyFilesCreateJob;
use App\Models\Setting;
use App\Models\SyncJob;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


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

    public function logout(Request $request) {
        Auth::logout();
        return redirect('/');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $setting = Setting::where('key','tax')->first();
        $tags = Setting::where('key','tags')->first();

        $lastUpdate = Setting::where('key','last-change')->first();
        $files = [];

        if(\request('is_sku')){

            $folder = request('is_sku');
            $filenamePath = ('public/shopify-images/'.$folder);
            $files = Storage::files($filenamePath);
        }

        return view('home' ,compact('setting' ,'lastUpdate' ,'files' ,'tags'));
    }

    public function resetAllImages(){

        $file = new Filesystem;
        $file->cleanDirectory('storage/shopify-images');

        session()->flash('app_message', 'Your all images in the PROJECT are removed please upload again.');

        return back();
    }

    public function saveTags(Request $request){

        Setting::updateOrCreate(['key' => 'tags'], ['value' => $request->tags]);
        session()->flash('app_message', 'Tags has been updated successfully.');

        return back();
    }

    public function updateTax(Request $request){

       Setting::updateOrCreate(['key' => 'tax'], ['value' => $request->tax]);
       session()->flash('app_message', 'Tax has been updated successfully.');

       return back();
    }

    public function ajaxProdImageUpload(Request $request){

        ini_set('max_execution_time', 1800);

        if ($request->hasFile('file')) {

            $imgUpload = $request->file('file');{

                $filename = $imgUpload->getClientOriginalName();
                $extension = $imgUpload->getClientOriginalExtension();

                $validextensions = array("jpeg", "jpg", "png");

                // Check extension
                if (in_array(strtolower($extension), $validextensions)) {
                    try {

                        $withoutExtension = pathinfo($filename, PATHINFO_FILENAME);

                        $names = (explode('-', $withoutExtension));

                        if (isset($names[1]) && is_numeric($names[1])) {
                            $filenameWithExt = $names[1] . '.' . $extension;
                            $folder = $names[0];
                        } else {
                            $filenameWithExt = 1 . '.' . $extension;
                            $folder = $names[0];
                        }

                        $filenamePath = ('public/shopify-images/' . $folder . '/' . $filenameWithExt);

                        \Storage::disk('local')->put($filenamePath, file_get_contents($imgUpload->getRealPath()));

                    } catch (\Exception $ex) {
                        Log::warning($filename . ' error ' . $ex->getMessage());
                    }

                }
            }
        }
    }

    public function downloadStockExcelFIle(){
        try{
            $path = storage_path('app/temp/PVP-2.xlsx');
            return response()->download($path);
        }
        catch (\Exception $ex){

            session()->flash('app_error', 'No file found please try to generate file or contact admin.');
            return back();
        }

    }

    public function downloadShopifyOutPutExcelFile(){

        try{
            $path = storage_path('app/temp/Shopify-OUTPUT-FILE-Ready-to-Import.xlsx');
            return response()->download($path);
        }
        catch (\Exception $ex){

            session()->flash('app_error', 'No file found please try to generate file or contact admin.');
            return back();
        }
    }

    public function processImagesIntoExcelFile(){

        ini_set('max_execution_time', 3600); //900 seconds = 30 minutes

        $jb = new UpdateStockAndShopifyFIlesCommand();
        $jb->createStockShopifyOutPutExcelFile();

        Setting::updateOrCreate(['key' => 'last-change'], ['value' => now()->toDateTimeString()]);

        SyncJob::where('type', 'stock-export')->update(['status' => 'completed']);

        SyncJob::truncate();

        session()->flash('app_message', 'Your cron job has been scheduled and starting soon please wait.');

        return back();
    }


}
