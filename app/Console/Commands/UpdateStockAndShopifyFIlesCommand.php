<?php

namespace App\Console\Commands;

use App\Models\AlreadyExistProduct;
use App\Models\ShopifySizeColor;
use App\Models\SyncJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use App\Exports\ShopifyImportFileExport;
use App\Exports\StockFileExport;
use App\Models\Setting;
use App\Services\Shopify\HttpApiRequest;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;


class UpdateStockAndShopifyFIlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateStockShopifyFiles:run';

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

    public $monthsSpanish = [
        'January' => 'January',
        'February' => 'Febrero',
        'March' => 'Marzo',
        'April' => 'Abril',
        'May' => 'Mayo',
        'June' => 'Junio',
        'July' => 'Julio',
        'August' => 'Agosto',
        'September' => 'Septiembre',
        'October' => 'Octubre',
        'November' => 'Noviembre',
        'December' => 'Diciembre',
    ];


    public function handle()
    {
        # check if there is product sync job
        $activeJob = SyncJob::activeStatus('stock-export')->first();

        if (!$activeJob) {

            Log::emergency(now()->toDateTimeString() . ' started updated JOB now for all the things...!New');

            $this->createStockShopifyOutPutExcelFile();

            Log::emergency(now()->toDateTimeString() . ' Finish updated JOB now for all the things...!New');

            SyncJob::truncate();

        }
        else
        {
            Log::warning('Already running job so its skipped NOW...!');
        }

    }

    public function createStockShopifyOutPutExcelFile(){

        ini_set('memory_limit', '-1');

        $setting = Setting::where('key','tax')->first();

        $categoryArray = $categoryParents = $brandsArray =  [];

        $categoriesResponse = HttpApiRequest::getContificoApi('categoria');

        $brandsResponse = HttpApiRequest::getContificoApi('marca');

        foreach ($categoriesResponse as $category) {

            if($category['padre_id'])
                $categoryParents[$category['id']] = $category['padre_id'];

            $categoryArray[$category['id']] = trim($category['nombre']);
        }

        foreach ($brandsResponse as $brand)
            $brandsArray[$brand['id']] = trim($brand['nombre']);

        try {

            $result_size = 500;

            $page_count = 1;

            $max_pages = (env('APP_ENV') == 'local') ?  5 : 1000;
            
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

                        $matching = ltrim(trim($row['codigo'])  ,'0');
                        $existingProduct = AlreadyExistProduct::where('codigo' ,'like',"%$matching%")->count();

                        $filenamePath = ('public/shopify-images/'.trim($row['codigo']));
                        $imagesExistArray = Storage::files($filenamePath);

                        if(count($imagesExistArray) == 0) continue;

                        if($existingProduct == 0) {
                            $response = $this->getShopifyFileRow($row ,$categoryArray ,$categoryParents,$brandsArray ,$imagesExistArray);

                            if($response)
                                $allDataArrSHopify[] = $response;
                        }
                    }
                }
                else
                    break;

            } while ($page_count <= $max_pages);

            Storage::deleteDirectory('temp');

            $pathStock = 'temp/PVP-2.xlsx';
            $pathShopify = 'temp/Shopify-OUTPUT-FILE-Ready-to-Import.xlsx';

            Excel::store(new StockFileExport($allDataArrStock), $pathStock);
            Excel::store(new ShopifyImportFileExport($allDataArrSHopify), $pathShopify);

            Setting::updateOrCreate(['key' => 'last-change'], ['value' => now()->toDateTimeString()]);

            SyncJob::where('type', 'stock-export')->update(['status' => 'completed']);

            Log::alert('createStockFileShopify Created successfully....!');

        } catch (\Exception $ex) {
            Log::error(' JOB FAILED createStockFileShopify. '.$ex->getMessage() . $ex->getLine());
            SyncJob::where('type', 'stock-export')->update(['status' => 'failed']);
        }

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

    public function getShopifyFileRow($singleRow, $categoriesArray ,$parentCategory ,$brandsArrayHere ,$images)
    {

        try {

            $subCategory = $fatherCategory = $brand  = $pCellBrand = $gender = '';

            $taxPercentage = $singleRow['porcentaje_iva'] ? $singleRow['porcentaje_iva'] : 0;

            $priceWithTax = $singleRow['pvp1'] + (($taxPercentage / 100) * $singleRow['pvp1']);

            if ($singleRow['categoria_id']) {
                $subCategory = @$categoriesArray[$singleRow['categoria_id']];
                $fatherCategory = @$categoriesArray[@$parentCategory[$singleRow['categoria_id']]];
            }

            if ($singleRow['marca_id']){
                $brand = @$brandsArrayHere[$singleRow['marca_id']];
            }

            $iCellCategory = $categoryWithoutGender = $subCategory;
            $hCell = self::getCat2Data($subCategory);

            $brand = str_replace('#', '', $brand);

            $checkLastCharB = $brand ? substr(trim($brand), -2, 1) : '';

            if (ctype_space($checkLastCharB)) {
                $pCellBrand = substr_replace(trim($brand), "", -2);

                $checkLastCharB = substr(trim($pCellBrand), -2, 1);

                if (ctype_space($checkLastCharB)) {
                    $pCellBrand = substr_replace(trim($pCellBrand), "", -2);
                }
            }


            $brandForTags = $pCellBrand ? (','.$pCellBrand) : '';

            $edadAge = self::isValidDate($singleRow['descripcion']) ? Carbon::parse($singleRow['descripcion']) : null;
            $edadDatePatternColumn = $edadAge ? (','.$this->monthsSpanish[$edadAge->format('F')] . '-' . $edadAge->format('y')) : '';

            $sColumnBrandLen = strlen($pCellBrand);
            $tColumnTypeLen = strlen($fatherCategory);
            #$priceWithTax = $singleRow['pvp1'] + (($taxPercentage / 100) * $singleRow['pvp1']);

            //$titleCellBefore = $iCellCategory .' '. ($tColumnTypeLen<10 ? @$fatherCategory : '') .' '.$hCell.' '.($sColumnBrandLen > 2 ? $pCellBrand : '');

            #if last char is simple CHAR then remove here
            $checkLastChar = substr(trim($iCellCategory), -2, 1);

            if (ctype_space($checkLastChar)) {

                $lastChar = substr($iCellCategory, -1);
                $categoryWithoutGender =  substr_replace(trim($iCellCategory) ,"",-2);

                $gender = ($lastChar == 'M') ? ' Masculino' : ' Femenino';
                $iCellCategory =  substr_replace(trim($iCellCategory) ,$gender,-2);
            }

            $titleCell = ($iCellCategory . ($sColumnBrandLen > 2 ? (' '.$pCellBrand) : ''));

            #remove single char from the TYPE too
            $checkLastChar = substr(trim($fatherCategory), -2, 1);
            if (ctype_space($checkLastChar)) {
                $fatherCategory =  substr_replace(trim($fatherCategory) ,"",-2);
            }

            if (strpos($fatherCategory, ' F ') !== false)
                $fatherCategory =  str_replace(" F "," Femenino ",$fatherCategory);

            elseif(strpos($fatherCategory, ' M ') !== false)
                $fatherCategory =  str_replace(" M "," Masculino ",$fatherCategory);


            if (strpos($titleCell, ' F ') !== false)
                $titleCell =  str_replace(" F "," Femenino ",$titleCell);

            elseif(strpos($titleCell, ' M ') !== false)
                $titleCell =  str_replace(" M "," Masculino ",$titleCell);


            $titleCell = trim($titleCell);

            $sizeColor = ShopifySizeColor::where('codigo' ,$singleRow['codigo'])->first();

            #new two fields added here

            $newTagsPersonalizado = $singleRow['personalizado1'] ? (','.$singleRow['personalizado1'] . ',' . $singleRow['personalizado2']) : '';

            //$newTagsPersonalizado = $sizeColor ? (',' . $sizeColor->size . ',' . $sizeColor->color) : '';

            $fatherCategoryTag = $fatherCategory ? (','.$fatherCategory) : '';
            $tagsCell = $categoryWithoutGender . $fatherCategoryTag . $brandForTags .$newTagsPersonalizado. $edadDatePatternColumn;

            $vendor = $sColumnBrandLen > 2 ? $pCellBrand : 'ND';

            $older = ["SN", "sin marca", "ND", "Amigui", "amigui"];
            $replace = "Sin marca";
            $newer   = [$replace ,$replace, $replace, $replace, $replace];

            $vendorColumn = str_replace($older, $newer, $vendor);

            $images = array_map(function ($value) {
                return url(str_replace("public", "storage", $value));
            },
                $images);

            return [

                'Handle' => $singleRow['codigo'], #done
                'Title' => $titleCell,
                'Body' => $titleCell,
                'Vendor' => $vendorColumn,
                'Type' => $fatherCategory,
                'Tags' => rtrim($tagsCell,','),

                'Published' => false, #not too important
                'Option1_Name' => 'Talla',
                'Option1_Value' => $singleRow['personalizado1'],
                'Option2_Name' => 'Color',
                'Option2_Value' => $singleRow['personalizado2'],

                'Variant_SKU' => $singleRow['codigo_barra'],
                'V_Inventory_Tracker' => 'shopify',
                'V_Inventory_Qty' => $singleRow['cantidad_stock'], #done
                'V_Inventory_Policy' => 'deny',
                'V_Price' => round($priceWithTax, 4),
                'V_Requires_Shipping' => true,
                'V_Taxable' => true,
                'imagen_Calc' => implode(';',$images)
            ];


        } catch (\Exception $ex) {
            Log::error($singleRow['codigo'] .' codigo single row error.' . $ex->getMessage() . $ex->getLine());
            return null;
        }

    }


    public static function isValidDate($date)
    {

        try {
            Carbon::parse($date);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getCat2Data($string){

        try {
            preg_match('#\((.*?)\)#', $string, $match);
            $fOrM = substr($string, -1);

            return isset($match[1]) ? trim($match[1]) : ($fOrM == 'M' || $fOrM == 'F' ? $fOrM : '');

        } catch (\Exception $e) {
            return '';
        }


    }

}
