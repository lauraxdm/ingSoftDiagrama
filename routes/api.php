<?php

use App\Http\Controllers\Api\DiagramController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('users', [UserController::class, 'index'])->name('api.users.index');
Route::get('users/invitations', [UserController::class, 'invitations'])->name('api.users.invitations');
Route::get('users/{user}/collaborations', [UserController::class, 'collaborations'])->name('api.users.collaborations');
Route::post('invitations/', [InvitationController::class, 'store'])->name('api.invitations.store');
Route::put('invitations/{invitation}', [InvitationController::class, 'update'])->name('api.invitations.update');

Route::get('diagrams/{diagram}', [DiagramController::class, 'get'])->name('api.diagrams.get');
Route::get('diagrams/{diagram}/export/java', [DiagramController::class, 'exportJava'])->name('api.diagrams.exports.java');
Route::get('diagrams/{diagram}/export/php', [DiagramController::class, 'exportPHP'])->name('api.diagrams.exports.php');
Route::get('diagrams/{diagram}/export/csharp', [DiagramController::class, 'exportCSharp'])->name('api.diagrams.exports.csharp');
Route::get('diagrams/{diagram}/export/cpp', [DiagramController::class, 'exportCpp'])->name('api.diagrams.exports.cpp');
Route::get('diagrams/{diagram}/export/xmi', [DiagramController::class, 'exportXMI'])->name('api.diagrams.exports.xmi');
Route::put('diagrams/{diagram}', [DiagramController::class, 'update'])->name('api.diagrams.update');

Route::get('projects/{project}/users', [ProjectController::class, 'collaborators'])->name('api.projects.collaborators');
Route::delete('projects/{project}/users/{user}', [ProjectController::class, 'dismissUser'])->name('api.projects.dismiss');