<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/* Route::group(['middleware' => 'auth:api'], function(){
    Route::post('openrequests', 'SolicitudController@OpenRequestOnSoftexpert');
    Route::post('details', 'UserController@details');
    Route::post('/formularies', 'ReaderController@getform');
}); */


Route::post('register', 'Auth\RegisterController@register');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout');


Route::group(['middleware' => 'auth:api'], function() {
    Route::post('/formularies', "ReaderController@getform");
    Route::post('/Solicitudes/UserHasOpenRequest','SolicitudController@OpenRequestInabimaSE');
    Route::post('/utilities/ConvertDateToString', 'UtilitiesController@ConvertDateToLiteralString');

    Route::post('/utilities/CopyDataGridToGrid', 'UtilitiesController@CopyDataGridToGrid');

    Route::get('/documents/{solicitud}','DocumentsController@searchDocumentData');
    Route::get('/documents/id/{documentid}', 'DocumentsController@searchDocumentDataByDocumentId');

    Route::post('/workflow/multipleassocdocs', 'WorkflowController@AsociateMultipleDocs');
    Route::post('/utilities/iibi/validatelab','UtilitiesController@ValidateLab');
    Route::get('/external/invi/proyectos','ExternalApiController@getProjetsInvi');

    Route::post('/external/general/changeDocumentPortalStatus','DocumentsController@ChangeDocumentDisponibility');

});
Route::post('/status','StatusPortalController@updateStatus');

Route::post('/documents/new','DocumentsController@newDocumentWithModelAndAsociate');


Route::post('validate/formulary','ValidatorController@ValidateForm');

Route::post('/utilities/ConvertTimeToLocal', 'UtilitiesController@ConvertTimeToLocal');
Route::post('/utilities/explodeStringToSql', 'UtilitiesController@ExplodeStringForSql');

Route::post('/utilities/getDataSoftexpertTable','UtilitiesController@GetDataTablaSoftexpert');
Route::post('/utilities/GetTarifa/{institution}','UtilitiesController@GetTarifaServicio');

Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@signup');

Route::get('me', 'AuthController@me');

Route::get('/Process/{id}', 'ProcessController@ValidateProcess');

Route::post('/external/caasd/cancelacion','ExternalApiController@cancelacionContratoCaasd');
Route::post('/external/caasd/cancelacion/init', 'ExternalApiController@initCancelacionContratoCaasdCompanies');
Route::post('/utilities/ConvertStringToDate','UtilitiesController@ConvertStringToDate');

Route::get('/test/timout', 'TestController@test');



//Softexpert Entity Resource Controller
Route::prefix('softexpert/entity')->group(function () {
    //Get all
    Route::get('{entity}','SoftexpertController@index');
    //Create an item
    Route::post('{entity}','SoftexpertController@store');
    //Get an item
    Route::get('{entity}/{id}','SoftexpertController@show');
    //Delete an item
    Route::delete('{entity}/{id}','SoftexpertController@destroy');
});






