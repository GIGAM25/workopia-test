<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\JobController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\ApplicantController;

Route::get('/', [HomeController::class, 'index'])->name('home'); //alternatively, you can use ->name('home') 
Route::get('/jobs/search', [JobController::class, 'search'])->name('jobs.search');
// Route::get('/jobs', [JobController::class, 'index']);
// Route::get('/jobs/create', [JobController::class, 'create']);
// Route::get('/jobs/{id}', [JobController::class, 'show']);
// Route::post('/jobs', [JobController::class, 'store']);

//Default Job Route breaking it to pcs to use middleware
// Route::resource('jobs', JobController::class);
Route::resource('jobs', JobController::class)->middleware('auth')->only('create', 'edit', 'update', 'destroy');
Route::resource('jobs', JobController::class)->except('create', 'edit', 'update', 'destroy');

Route::middleware('guest')->group(function (){
    //note: get request to /register & when we get to that route, I will call the RegisterController class and
    //call a method called 'register'
    Route::get('/register', [RegisterController::class, 'register'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    // Route::get('/login', [LoginController::class, 'login'])->name('login')->middleware('guest'); //manually: if alredy login, no more access to login page
    Route::get('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login.authenticate');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');

Route::middleware('auth')->group(function (){
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::post('/bookmarks/{job}', [BookmarkController::class, 'store'])->name('bookmarks.store');
    Route::delete('/bookmarks/{job}', [BookmarkController::class, 'destroy'])->name('bookmarks.destroy');
});

Route::post('/jobs/{job}/apply', [ApplicantController::class, 'store'])->name('applicant.store')->middleware('auth');
Route::delete('/applicants/{applicant}', [ApplicantController::class, 'destroy'])->name('applicant.destroy')->middleware('auth');

####################################
// Route::get('/jobs', function() {
//     return 'Available Jobs';
// })->name('jobs'); //you can add any name of routes

// Route::get('/jobs/create', function() {
//     return view('jobs.create');
// })->name('jobs.create');

#routes with params
// Route::get('/posts/{id}', function(string $id) {
//     return 'Post ' . $id;
// });

// Route::get('/posts/{id}/comments/{commendId}', function(string $id, string $commentId) {
//     return 'Post ' . $id . ' Comment ' . $commentId;
// });


#routes
// Route::post('/submit', function() {
//     return 'Submitted';
// });

#mix request
// Route::match(['get', 'post'], '/submit', function() {
//     return 'Submitted';
// });

// Route::any('/submit', function() {
//     return 'Submitted';
// });

// Route::get('/test', function() {
//     $url = route('jobs');
//     return "<a href='jobs'>Click here</a>";
// });

// Route::get('/api/users', function() {
//     return [
//         'name' => 'John Doe',
//         'email' => 'john@gmail.com'
//     ];
// });

#add contraints to route params
// Route::get('/posts/{id}', function(string $id) {
//     return 'Post ' . $id;
// })->where('id', '[0-9]+');
// Route::get('/posts/{id}', function(string $id) {
//     return 'Post ' . $id;
// })->where('id', '[a-zA-Z]+');
// //shortcut
// Route::get('/posts/{id}', function(string $id) {
//     return 'Post ' . $id;
// })->whereNumber('id');
// Route::get('/posts/{id}', function(string $id) {
//     return 'Post ' . $id;
// })->whereAlpha('id');
// //same difference of local constraints and global (app->Providers->AppServiceProvider.php)

#get value of variables
// Route::get('/users', function (Request $request) {
//     return $request->query('name');
// });
//with multiple variables
// Route::get('/users', function (Request $request) {
//     return $request->only(['name', 'age']);
// });
//with all
// Route::get('/users', function (Request $request) {
//     return $request->all();
// });
// Route::get('/users', function (Request $request) {
//     return $request->has('name');
// });
// Route::get('/users', function (Request $request) {
//     return $request->input('name');
// });

// Route::get('/test', function(Request $request) {
//     return [
//         'method' => $request->method(),
//         'url' => $request->url(),
//         'path' => $request->path(),
//         'fullUrl' => $request->fullUrl(),
//         'ip' => $request->ip(),
//         'userAgent' => $request->userAgent(),
//         'header' => $request->header(),
//     ];
// });

// #response helper
// Route::get('/test', function() {
//     // return response('<h1>Hello World</h1>', 200)->header('Content-Type', 'text/plain');
//     return response()->json(['name'=>'John Doe']);
// });

// #download
// Route::get('/download', function() {
//     return response()->download(public_path('favicon.ico'));
// });

// #cookie
// Route::get('/cookie', function() {
//     return response()->json(['name'=>'John Doe'])->cookie('name', 'John Doe');
// });
// Route::get('/read-cookie', function(Request $request) {
//     $cookieValue = $request->cookie('name');
//     return response()->json(['cookie' => $cookieValue]);
// });