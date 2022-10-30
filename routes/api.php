<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

//Register Customer
Route::post('/register',[App\Http\Controllers\UserController::class, 'registerCustomer']);

//Login Customer
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'loginCustomer']);

//Create A loan Application
Route::post('/create/loan',[App\Http\Controllers\LoanController::class, 'createLoan']);

//Get List of all Loans for admin
Route::get('/get/loans',[App\Http\Controllers\LoanController::class, 'getAllLoans']);

//Approve Loans From admin
Route::patch('/approve/loan',[App\Http\Controllers\LoanController::class, 'approveLoan']);

//Show user specific loan details to customer
Route::get('/get/customer-loan',[App\Http\Controllers\LoanController::class, 'getCustomerLoan']);

//Add Payment for scheduled repayment
Route::patch('/add/scheduled-repayment',[App\Http\Controllers\LoanController::class, 'addScheduledRepayment']);