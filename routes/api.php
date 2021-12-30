<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AntecipaGovController;
use App\Http\Controllers\AccountController;

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

Route::get('/available-quotes', [AntecipaGovController::class, 'availableQuotes']);
Route::post('/send-proposal', [AntecipaGovController::class, 'sendProposal']);
Route::get('/consult-proposal', [AntecipaGovController::class, 'consultProposal']);
Route::get('/consult-external-proposal', [AntecipaGovController::class, 'consultExternalProposals']);
Route::get('/consult-contracts', [AntecipaGovController::class, 'consultContracts']);
Route::get('/get-contract-pdf', [AntecipaGovController::class, 'getContractPDF']);
Route::get('/get-banking-term', [AntecipaGovController::class, 'getBankingTerm']);

Route::post('/credit-operations/register', [AntecipaGovController::class, 'registerCreditTransaction']);
Route::post('/credit-operations/amortize', [AntecipaGovController::class, 'amortizeCreditTransaction']);
Route::post('/credit-operations/settle', [AntecipaGovController::class, 'settleCreditTransaction']);
Route::post('/credit-operations/cancel', [AntecipaGovController::class, 'cancelCreditTransaction']);
Route::get('/credit-operations', [AntecipaGovController::class, 'getCreditTransaction']);
Route::get('/credit-operations/status', [AntecipaGovController::class, 'getStatusCreditTransaction']);

Route::post('/accounts/store', [AccountController::class, 'store']);
