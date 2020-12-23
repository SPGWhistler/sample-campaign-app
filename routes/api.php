<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SlidesController;

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

//A route to list the campaigns available
Route::get('/campaigns', [SlidesController::class, 'getCampaigns']);

//A route to get the slides for a given campaign.
Route::get('/slides/{id}', [SlidesController::class, 'getSlides']);