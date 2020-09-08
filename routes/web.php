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

Auth::routes(['register' => false]);

Route::get('/home', 'HomeController@index')->name('home');
Route::get('logout', 'HomeController@logout')->name('logOutCustom');


Route::group(['middleware' => 'auth'], function () {


    Route::post('/ajaxProdImageUpload', 'HomeController@ajaxProdImageUpload')->name('add_img');
    Route::post('/ajaxProdImageDelete', 'HomeController@ajaxProdImageDelete')->name('remove_img');


    Route::get('/download-stock-excel', [
        'as' => 'create.stock.excel',
        'uses' => 'HomeController@createStockExcelFIle'
    ]);

    Route::get('/run-sync-stock-excel-files', [
        'as' => 'create.stock.files',
        'uses' => 'HomeController@syncJobToUpdateFiles'
    ]);

    Route::get('/download-shopify-import-file', [
        'as' => 'create.shopify.import.excel',
        'uses' => 'HomeController@createShopifyOutPutExcelFile'
    ]);


});
