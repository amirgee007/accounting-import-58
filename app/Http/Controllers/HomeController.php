<?php

namespace App\Http\Controllers;

use App\Console\Commands\UpdateStockAndShopifyFIlesCommand;
use App\Exports\ProductSkuRenamedListExport;
use App\Imports\ProductSkuListImport;
use App\Jobs\UpdateStockAndShopifyFilesCreateJob;
use App\Models\Setting;
use App\Models\SyncJob;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;


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

    public function renameFilesSku(Request $request){

        try {

            ini_set('max_execution_time', 18000);

            $v = Validator::make($request->all(), [
                'images_zip' => 'required|mimes:zip',
                'sku_file' => 'required|mimes:xlsx',
            ]);

            if($v->fails()) {
                return back()->withErrors($v);
            }
            else
            {
                $sku_file = 'public/backups/sku_file.xlsx';

                Storage::put($sku_file, file_get_contents($request->file('sku_file')->getRealPath()));

                $readd = 'app/'.$sku_file;
                $products = Excel::toArray(new ProductSkuListImport(), storage_path($readd));
                $skusFinal = [];

                if(isset($products[0])){
                    foreach ($products[0] as $product){
                        $skusFinal[] = $product[0];
                    }
                }

                else
                {
                    return Redirect::back()->withErrors('Your imported ZIP file is invalid please try again.');
                }

                $file = new Filesystem;
                $file->cleanDirectory('files/imageOriginal');
                $file->cleanDirectory('files/imageRenamed');

                $zip = new \ZipArchive();
                $file = $request->file('images_zip');

                if ($zip->open($file->path()) === TRUE) {
                    $zip->extractTo('files/imageOriginal');
                    $zip->close();

                } else {
                    Log::error("Order Products Inventories error UNABLE TO READ the zip file.");
                    return Redirect::back()->withErrors('Your imported ZIP file is invalid please try again.');
                }

                $path = public_path('files/imageOriginal');

                $files = File::allFiles($path);

                $pathNew = public_path('files/imageRenamed');

                File::makeDirectory($path, $mode = 0777, true, true);
                File::makeDirectory($pathNew, $mode = 0777, true, true);

                $skuChoose = $newName = $namesFinalExcel= null;

                $index = 0;
                foreach ($files as $counter => $file){

                   if($counter%2 == 0){
                       $skuChoose = @$skusFinal[$index];
                       $newName = $skuChoose.'.jpg';

                       $index++;

                   }
                   else{
                       $newName = $skuChoose.'-2.jpg';
                   }

                   $namesFinalExcel [] = $newName;

                   File::move($file, $pathNew.'/'.$newName);
                }


                $zip_file = 'RenameImagesFiles.zip';
                $zip = new \ZipArchive();
                $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pathNew));
                foreach ($files as $name => $file)
                {
                    // We're skipping all subfolders
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $fileName = $file->getFileName();

                        $zip->addFile($filePath, $fileName);
                    }
                }
                $zip->close();
                return response()->download($zip_file);
            }

        } catch (\Exception $ex) {

            Log::error("Order Products Inventories error " .$ex->getMessage().'-'.$ex->getLine());
            return Redirect::back()->withErrors('Your imported excel file is invalid please try again.');
        }
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $tax = Setting::where('key','tax')->first();
        $tags = Setting::where('key','tags')->first();
        $email = Setting::where('key','adminEmail')->first();

        $lastUpdate = Setting::where('key','last-change')->first();
        $files = [];

        if(\request('is_sku')){

            $folder = request('is_sku');
            $filenamePath = ('public/shopify-images/'.$folder);
            $files = Storage::files($filenamePath);
        }

        return view('home' ,compact('tax' ,'lastUpdate' ,'files' ,'tags' , 'email'));
    }

    public function resetAllImages(){

        $file = new Filesystem;
        $file->cleanDirectory('storage/shopify-images');

        session()->flash('app_message', 'Your all images in the PROJECT are removed please upload again.');

        return back();
    }

    public function saveSettings(Request $request){

        $data = array_slice($request->all(), 1, 1, true);

        Setting::updateOrCreate(['key' => key($data)], ['value' => reset($data)]);
        session()->flash('app_message', 'Settings has been updated successfully.');

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

    public function downloadErrorLogsFIle(){
        try{
            $path = storage_path('app/temp/Api-Error-Logs.xlsx');
            return response()->download($path);
        }
        catch (\Exception $ex){

            session()->flash('app_error', 'No file found please try to generate file or contact admin.');
            return back();
        }

    }

    public function downloadShopifyOutPutExcelFile(){

        try{
            $path = storage_path('app/temp/Shopify-OUTPUT-FILE-Ready-to-Import123.xlsx');

            return response()->download($path);
        }
        catch (\Exception $ex){

            session()->flash('app_error', 'No file found please try to generate file or contact admin.');
            return back();
        }
    }

    public function processImagesIntoExcelFile(){

        ini_set('max_execution_time', 3600); //900 seconds = 30 minutes

        # check if there is product sync job
        $activeJob = SyncJob::activeStatus('stock-export')->first();

        if (!$activeJob) {
            $newSyncJob = SyncJob::create(['type' => 'stock-export']);
            UpdateStockAndShopifyFilesCreateJob::dispatch($newSyncJob->id, $newSyncJob->type);
        }


        session()->flash('app_message', 'Your cron job has been scheduled and starting soon please wait.');

        return \redirect()->route('home');
    }


}
