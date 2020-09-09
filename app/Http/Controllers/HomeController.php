<?php

namespace App\Http\Controllers;

use App\Console\Commands\UpdateOnlyShopifyFileCommand;
use App\Console\Commands\UpdateStockAndShopifyFIlesCommand;
use App\Jobs\UpdateStockAndShopifyFilesCreateJob;
use App\Models\Setting;
use App\Models\SyncJob;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


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
        $lastUpdate = Setting::where('key','last-change')->first();
        $files = [];

        if(\request('is_sku')){

//            $folder = request('is_sku');
//            $filenamePath = ('public/sello-images/'.$folder);
//
//            $files = Storage::files($filenamePath);
//
//            return view('dashboard.admin' ,compact('files'));
        }

        return view('home' ,compact('setting' ,'lastUpdate' ,'files'));
    }

    public function updateTax(Request $request){

       Setting::updateOrCreate(['key' => 'tax'], ['value' => $request->tax]);
       session()->flash('app_message', 'Tax has been updated successfully.');

       return back();
    }

    public function ajaxProdImageUpload(Request $request){

        if($request->hasFile('file')) {

            // Upload path
            $destinationPath = 'files/';

            // Create directory if not exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Get file extension
            $extension = $request->file('file')->getClientOriginalExtension();

            // Valid extensions
            $validextensions = array("jpeg","jpg","png","pdf");

            // Check extension
            if(in_array(strtolower($extension), $validextensions)){

                // Rename file
                $fileName = $request->file('file')->getClientOriginalName().time() .'.' . $extension;
                // Uploading file to given path
                $request->file('file')->move($destinationPath, $fileName);

            }

        }
    }

    public function ajaxProdImageUploadOLD(Request $request){

        try {

            $preview = $config = $errors = [];

            $input = $request->images;

            Log::warning(count($input));

            if (empty($input))
                $output = [];
            else {

                foreach ($request->images as $index => $imgUpload) {

                    $filename = $imgUpload->getClientOriginalName();
                    $extension = $imgUpload->getClientOriginalExtension();
                    $fileSize = $imgUpload->getClientSize(); // getting original fileName;

                    try{

                        $withoutExtension = pathinfo($filename, PATHINFO_FILENAME);

                        $names = (explode('-' ,$withoutExtension));

                        if(isset($names[1]) && is_numeric($names[1])){
                            $filenameWithExt = $names[1] . '.' . $extension;
                            $folder = $names[0];
                        }
                        else
                        {
                            $filenameWithExt = 1 . '.' . $extension;
                            $folder = $names[0];
                        }

                        $filenamePath = ('public/shopify-images/'.$folder .'/'.$filenameWithExt);

                        \Storage::disk('local')->put($filenamePath, file_get_contents(
                                $imgUpload->getRealPath())
                        );

                        $newFileUrl = url(str_replace("public","storage",$filenamePath));

                        $preview[] = $newFileUrl;

                        $config[] = [
                            'key' => $filenameWithExt,
                            'extra' => ['_token' => rand()],
                            'caption' => $folder.'/'.$filenameWithExt,
                            'size' => $fileSize,
                            'downloadUrl' => $newFileUrl, // the url to download the file
                            'url' => route('remove_img'), // server api to delete the file based on key
                        ];

                    }catch (\Exception $ex){

                        Log::warning($filename. ' image name is INVALID here,  please try again with correct name-int.extension');
                    }

                }

                $output = [
                    'initialPreview' => $preview,
                    'initialPreviewConfig' => $config,
                    'initialPreviewAsData' => true,
                ];
            }

            echo json_encode($output);

        } catch (\Exception $e) {
            Log::error('Error during image upload ' . $e->getMessage() . ' amir user id ' . auth()->id());

            $output = [
                'error' => 'No files selected to upload.'
            ];
            echo json_encode($output);
        }
    }

    public function ajaxProdImageDelete(Request $request){

        $key = $request->key;

        $output = [];

        $product_id = $request->product_id;

        try{

            $productImageId = ProductImage::where('product_id' ,$product_id)->where('url_original' ,'like' ,"%{$key}%")->first();

            if($productImageId && env('APP_ENV') == 'production'){

                $original_path = str_replace(env('AWS_BUCKET_URL'), "", $productImageId->url_original); #s3 needs only path not full

                //Storage::disk('s3')->delete($original_path);

                $output = ['info'=>'File successfully unlinked.'];
            }
//            else {
//                throw new \ErrorException('Image not found please contact admin.');
//            }
        }

        catch(\Exception $e){
            throw new \ErrorException(' Image not found please contact admin.' .$product_id.'-'.$key);
        }

        echo json_encode($output);
        return;
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

        $jb = new UpdateStockAndShopifyFIlesCommand();
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
