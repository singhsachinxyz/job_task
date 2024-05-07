<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ValidateDataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/import-employees', [ValidateDataController::class, 'postExcel'] );
Route::get('/export-all-employees', [ValidateDataController::class, 'exportAllEmployees'] );
Route::get('/export-dummy-employees/{id}', [ValidateDataController::class, 'exportDummyEmployees'] );


Route::post('test/soap-enrollment', function (Illuminate\Http\Request $request) {

    $xmlBodyContent =   '<Envelope xmlns="http://www.w3.org/2003/05/soap-envelope">
                            <Header xmlns:wsa="http://www.w3.org/2005/08/addressing">
                                <wsa:Action>http://tempuri.org/IBCService/GetEnrollmentDetails_Policy</wsa:Action>
                            </Header>
                            <Body>
                                <GetEnrollmentDetails_Policy xmlns="http://tempuri.org/">
                                    <UserName>' . $request->username . '</UserName>
                                    <Password>' . $request->password . '</Password>
                                    <PolicyNumber>' . $request->policy_no . '</PolicyNumber>
                                    <StartIndex>' . $request->start_index . '</StartIndex>
                                    <Range>' . $request->range . '</Range>
                                </GetEnrollmentDetails_Policy>
                            </Body>
                        </Envelope>';
    $url = 'https://m.fhpl.net/Bunnyconnect/BCService.svc';

    // $response = Http::withHeaders([
    //     'Content-Type'=>'application/soap+xml',
    //     'SOAPAction'=>'http://tempuri.org/IBCService/GetEnrollmentDetailsEmployee_Agent'
    // ])
    // ->withBody($xmlBodyContent, "application/soap+xml")->post($url);
    
    $response = Http::withHeaders([
        'Content-Type'=>'application/soap+xml',
        'SOAPAction'=>'http://tempuri.org/IBCService/GetEnrollmentDetailsEmployee_Agent'
    ])->send('POST', $url, ['body' => $xmlBodyContent]);

    $responseBody = $response->body();
    
    if($response->successful() && !empty($responseBody)){
        $responseArray = json_decode(convertXmlToArray($responseBody)['sBody']['GetEnrollmentDetails_PolicyResponse']['GetEnrollmentDetails_PolicyResult']);
        return $responseArray;
    }
    else{
        return $responseBody;
    }
});