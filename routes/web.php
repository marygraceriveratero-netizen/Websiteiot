<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FireLogController;
use App\Http\Controllers\ChartSensorApiController;
use App\Http\Controllers\ChartSensorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FirebaseLoginController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;



Route::get('/', function () {
    return redirect()->route('login');
});



// 🔹 Pages nav highlight
Route::get('/Homepage', fn() => view('fire-dashboard'))->name('Homepage');
Route::get('/extinguisher', fn() => view('Extinguisher'))->name('extinguisher');
Route::get('/Event_log_history', fn() => view('Event_log_history'))->name('Event_log_history');
Route::get('/Alert_type', fn() => view('Alert_type'))->name('Alert_type');
Route::get('/AdminOSHO', fn() => view('AdminOSHO'))->name('AdminOSHO');
Route::get('/fire-feed', fn() => view('/fire-feed'))->name('/fire-feed');

// 🔹 Fire Logs (UI + Data)
Route::get('/fire_feed', [FireLogController::class, 'showImageFeed'])->name('fire_feed');
Route::get('/Alert_type', [FireLogController::class, 'indexx'])->name('Alert_type');
Route::get('/Event_log_history', [FireLogController::class, 'showFireLogs'])->name('Event_log_history');
Route::put('/firelogs/{id}/status', [FireLogController::class, 'updateStatus'])->name('firelogs.updateStatus');


// 🔹 Chart (UI + Data)
Route::get('/chart_sensor_dashboard', [ChartSensorController::class, 'index'])->name('chart_sensor_dashboard');
Route::get('/chart_sensor_data', [ChartSensorController::class, 'fetchData'])->name('chart_sensor_data');

// 🔹 Fire Logs API (Firebase)
Route::get('/fire-logs', [FireLogController::class, 'index']);
Route::get('/fire-logs/latest', [FireLogController::class, 'latest']);
Route::post('/fire-logs', [FireLogController::class, 'store']);
Route::patch('/fire-logs/{id}', [FireLogController::class, 'update']);
Route::get('/fire-logs/export/csv', [FireLogController::class, 'exportCsv']);

// 🔹 Chart API (Firebase)
Route::post('/chart_sensor', [ChartSensorApiController::class, 'store']);

// 🔹 Admin(Firebase)
Route::get('/AdminOSHO', [AdminController::class, 'index'])->name('AdminOSHO');
Route::get('/admin/register', [AdminController::class, 'create'])->name('admins.create');
Route::post('/admin/register', [AdminController::class, 'store'])->name('admins.store');
Route::put('/admins/{id}', [AdminController::class, 'update'])->name('admins.update');      //add mamaya
Route::delete('/admins/{id}', [AdminController::class, 'destroy'])->name('admins.destroy');



// 🔹 email(Firebase)
Route::post('/fire-alert/send', [App\Http\Controllers\FireAlertController::class, 'send']);

// 🔹 email confirm verification(Firebase)
Route::get('/verify', [AdminController::class, 'showVerificationForm'])->name('verification.form');
Route::post('/verify', [AdminController::class, 'verifyCode'])->name('verification.submit');

// 🔹 login(Firebase)
Route::get('/login', [FirebaseLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [FirebaseLoginController::class, 'login'])->name('login.post');
Route::post('/logout', [FirebaseLoginController::class, 'logout'])->name('logout');

Route::view('/forgot-password', 'forgot');
Route::view('/reset-password', 'reset-password');

Route::post('/send-reset-code', function (Request $request) {
    $email = $request->input('email');
    $code = $request->input('resetCode');

    Mail::send('emails.reset-code', ['code' => $code], function ($message) use ($email) {
        $message->to($email)->subject('Your Password Reset Code');
    });

    return response()->json(['status' => 'sent']);
});

// 🔹 authCheck(Firebase) kung wala naka login sa sesion base in role rekta sila sa login
Route::get('/dashboard', function () {
    if (!session()->has('role')) {
        return redirect()->route('login');
    }
    return view('dashboard'); // your dashboard blade
})->name('dashboard');

Route::get('/extinguisher', function () {
    if (!session()->has('role')) {
        return redirect()->route('login');
    }
    return view('Extinguisher'); // extinguisher  blade
})->name('extinguisher');

Route::get('/Event_log_history', function () {
    if (!session()->has('role')) {
        return redirect()->route('login');
    }
    return view('Event_log_history'); // Event_log_history  blade
})->name('Event_log_history');


Route::get('/Alert_type', function () {
    if (!session()->has('role')) {
        return redirect()->route('login');
    }
    return view('Alert_type'); // Alert_type  blade
})->name('Alert_type');



Route::get('/AdminOSHO', function () {
    if (!session()->has('role')) {
        return redirect()->route('login');
    }

    // Call controller manually
    return app(AdminController::class)->index();
})->name('AdminOSHO');


Route::get('/fire-feed', function () {
    if (!session()->has('role')) {
        return redirect()->route('login');
    }
    return view('fire-feed'); // fire-feed  blade
})->name('fire-feed');



Route::post('/set-session', function (Request $request) {
    $request->session()->put('firebase_user', $request->input('uid'));
    $request->session()->put('role', $request->input('role'));
    $request->session()->put('email', $request->input('email'));
    $request->session()->put('name', $request->input('name'));

    return response()->json(['status' => 'ok']);
});
