<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('abc/test', 'TestController@index');


Auth::routes(['register' => false]);

Route::get('/home', 'HomeController@index')->name('home');
Route::post('/save-tags', 'HomeController@saveTags')->name('save.tags');

Route::get('logout', 'HomeController@logout')->name('logOutCustom');


Route::group(['middleware' => 'auth'], function () {


    Route::post('/ajaxProdImageUpload', 'HomeController@ajaxProdImageUpload')->name('add_img');
    Route::post('/ajaxProdImageDelete', 'HomeController@ajaxProdImageDelete')->name('remove_img');

    Route::get('/reset-all-images', [
        'as' => 'reset.all.images',
        'uses' => 'HomeController@resetAllImages'
    ]);

    Route::post('/rename-files-sku', [
        'as' => 'rename.files.sku',
        'uses' => 'HomeController@renameFilesSku'
    ]);

    Route::get('/process-images-into-shopify', [
        'as' => 'process.images.files.excel',
        'uses' => 'HomeController@processImagesIntoExcelFile'
    ]);

    Route::get('/download-shopify-import-file', [
        'as' => 'download.shopify.import.excel',
        'uses' => 'HomeController@downloadShopifyOutPutExcelFile'
    ]);

    Route::get('/download-stock-excel', [
        'as' => 'download.stock.excel',
        'uses' => 'HomeController@downloadStockExcelFIle'
    ]);

});
