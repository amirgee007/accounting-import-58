<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\SyncJob;
use Illuminate\Support\Facades\Log;
use App\Exports\StockFileExport;
use App\Models\Setting;
use App\Services\Shopify\HttpApiRequest;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;


class UpdateOnlyStockFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateStockFiles:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update all files hourly';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        # check if there is product sync job
        $activeJob = SyncJob::activeStatus('stock-export')->first();

        if (!$activeJob) {

            Log::emergency(now()->toDateTimeString() . ' started updated JOB now for all the things...!working');

            $this->createStockExcelFile();

            Log::emergency(now()->toDateTimeString() . ' Finish updated JOB now for all the things...!working');
        }
        else
        {
            Log::warning('Already running job so its skipped NOW...!');
        }

    }

    public function createStockExcelFile(){

        ini_set('memory_limit', '-1');

        $setting = Setting::where('key','tax')->first();

        try {

            $result_size = 1000;

            $page_count = 1;

            $max_pages = (env('APP_ENV') == 'local') ?  5 : 100;

            Log::info('its ok to have these pages on live' .$max_pages);
            $taxPercentage = $setting->value;

            $allDataArrStock  = $allDataArrSHopify = [];

            do {

                Log::debug($page_count. ' Done createStockShopifyOutPutExcelFile count here api');

                $typeWithParams = "producto?result_size=$result_size&result_page=$page_count";

                $data = HttpApiRequest::getContificoApi($typeWithParams);

                $page_count++;

                if(is_array($data) && count($data) > 1) {

                    foreach ($data as $row){

                        $response = $this->getStockFileRow($row);

                        if($response)
                            $allDataArrStock[] = $response;
                    }
                }
                else
                    break;

            } while ($page_count <= $max_pages);

            Storage::delete('temp/PVP-2.xlsx');

            $pathStock = 'temp/PVP-2.xlsx';

            Excel::store(new StockFileExport($allDataArrStock), $pathStock);

            Log::alert('createStockFileS Created successfully....!');

        } catch (\Exception $ex) {
            Log::error(' JOB FAILED createStockFileShopify. '.$ex->getMessage() . $ex->getLine());
            SyncJob::where('type', 'stock-export')->update(['status' => 'failed']);
        }

        SyncJob::truncate();

    }

    public function getStockFileRow($singleRow){

        try {

            $taxPercentage = $singleRow['porcentaje_iva'] ? $singleRow['porcentaje_iva'] : 0;
            $priceWithTax = $singleRow['pvp1'] + (($taxPercentage / 100) * $singleRow['pvp1']);

            return [
                'Handle' => $singleRow['codigo_barra'],
                'Variant Price' => round($priceWithTax, 2),
                'Variant Taxable' => false, #not using it
                'Stock' => $singleRow['cantidad_stock'],
            ];

        } catch (\Exception $ex) {
            Log::error($singleRow['codigo'] . ' codigo single row error.' . $ex->getMessage() . $ex->getLine());
            return null;
        }

    }
}
