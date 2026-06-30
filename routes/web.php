<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SchoolOnboardingController;
use App\Http\Controllers\SchoolProfileController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolUserController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentScheduleController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherScheduleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViewAsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'welcome')->name('home');


// Dashboard umum berdasarkan role
Route::middleware(['auth', 'active.user', 'school.onboarded'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Profil (semua user yang login)
Route::middleware(['auth', 'active.user'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route hanya untuk Super Admin
Route::middleware(['auth', 'active.user', 'role:super_admin'])->group(function () {
    Route::get('/view-as', [ViewAsController::class, 'index'])->name('view-as.index');
    Route::post('/view-as', [ViewAsController::class, 'store'])->name('view-as.store');
    Route::delete('/view-as', [ViewAsController::class, 'destroy'])->name('view-as.destroy');
    Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
    Route::patch('/schools/{school}/approve', [SchoolController::class, 'approve'])->name('schools.approve');
    Route::patch('/schools/{school}/reject', [SchoolController::class, 'reject'])->name('schools.reject');
});

// Route untuk role sekolah
Route::middleware(['auth', 'active.user'])->group(function () {
    Route::middleware('effective.role:school_admin')->group(function () {
        Route::get('/school-onboarding', [SchoolOnboardingController::class, 'edit'])->name('school-onboarding.edit');
        Route::post('/school-onboarding', [SchoolOnboardingController::class, 'update'])->name('school-onboarding.update');
    });

    Route::middleware('school.onboarded')->group(function () {
        Route::middleware('effective.role:teacher')->group(function () {
            Route::get('/teacher-schedules', [TeacherScheduleController::class, 'index'])->name('teacher-schedules.index');
        });

        Route::middleware('effective.role:student')->group(function () {
            Route::get('/student-schedules', [StudentScheduleController::class, 'index'])->name('student-schedules.index');
            Route::get('/student-nametag', [StudentController::class, 'printOwnNametag'])->name('students.own-nametag');
            Route::get('/exams/{exam}/take', [ExamController::class, 'take'])->name('exams.take');
            Route::post('/exams/{exam}/answers', [ExamController::class, 'saveAnswer'])->name('exams.answers.save');
            Route::post('/exams/{exam}/submit', [ExamController::class, 'submit'])->name('exams.submit');
            Route::get('/exams/{exam}/result', [ExamController::class, 'result'])->name('exams.result');
        });

        Route::middleware('effective.role:school_admin,teacher,student')->group(function () {
            Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
        });

        Route::middleware('effective.role:school_admin,teacher')->group(function () {
            Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
            Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
            Route::get('/exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit');
            Route::put('/exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
            Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
            Route::patch('/exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');
            Route::patch('/exams/{exam}/close', [ExamController::class, 'close'])->name('exams.close');
            Route::get('/exams/{exam}/results', [ExamController::class, 'results'])->name('exams.results');
            Route::post('/exams/{exam}/questions', [ExamController::class, 'storeQuestion'])->name('exams.questions.store');
            Route::put('/exams/{exam}/questions/{question}', [ExamController::class, 'updateQuestion'])->name('exams.questions.update');
            Route::delete('/exams/{exam}/questions/{question}', [ExamController::class, 'destroyQuestion'])->name('exams.questions.destroy');
            Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
            Route::get('/attendances/reports', [AttendanceController::class, 'report'])->name('attendances.report');
            Route::get('/attendances/reports/print', [AttendanceController::class, 'printReport'])->name('attendances.report.print');
            Route::get('/attendances/reports/daily', [AttendanceController::class, 'dailyReport'])->name('attendances.report.daily');
            Route::get('/attendances/reports/daily/print', [AttendanceController::class, 'printDailyReport'])->name('attendances.report.daily.print');
            Route::get('/attendances/reports/schedules', [AttendanceController::class, 'scheduleReport'])->name('attendances.report.schedule');
            Route::get('/attendances/reports/schedules/print', [AttendanceController::class, 'printScheduleReport'])->name('attendances.report.schedule.print');
            Route::get('/attendances/daily', [AttendanceController::class, 'daily'])->name('attendances.daily');
            Route::post('/attendances/daily/open', [AttendanceController::class, 'openDaily'])->name('attendances.daily.open');
            Route::get('/attendances/daily/{attendanceSession}', [AttendanceController::class, 'editDaily'])->name('attendances.daily.edit');
            Route::post('/attendances/daily/{attendanceSession}/scan', [AttendanceController::class, 'scanDaily'])->name('attendances.daily.scan');
            Route::put('/attendances/daily/{attendanceSession}', [AttendanceController::class, 'updateDaily'])->name('attendances.daily.update');
            Route::get('/attendances/schedules', [AttendanceController::class, 'schedule'])->name('attendances.schedule');
            Route::post('/attendances/schedules/open', [AttendanceController::class, 'openSchedule'])->name('attendances.schedule.open');
            Route::get('/attendances/schedules/{attendanceSession}', [AttendanceController::class, 'editSchedule'])->name('attendances.schedule.edit');
            Route::post('/attendances/schedules/{attendanceSession}/scan', [AttendanceController::class, 'scanSchedule'])->name('attendances.schedule.scan');
            Route::put('/attendances/schedules/{attendanceSession}', [AttendanceController::class, 'updateSchedule'])->name('attendances.schedule.update');
        });

        Route::middleware('effective.role:school_admin')->group(function () {
            Route::get('/school-profile', [SchoolProfileController::class, 'edit'])->name('school-profile.edit');
            Route::patch('/school-profile', [SchoolProfileController::class, 'update'])->name('school-profile.update');
            Route::get('/school-users', [SchoolUserController::class, 'index'])->name('school-users.index');
            Route::post('/school-users', [SchoolUserController::class, 'store'])->name('school-users.store');
            Route::resource('academic-years', AcademicYearController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('semesters', SemesterController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('school-classes', SchoolClassController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('classrooms', ClassroomController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
            Route::resource('rooms', RoomController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::get('/schedules/print', [ScheduleController::class, 'print'])->name('schedules.print');
            Route::resource('schedules', ScheduleController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::resource('subjects', SubjectController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::get('/teachers/import/template', [TeacherController::class, 'downloadTemplate'])->name('teachers.import-template');
            Route::post('/teachers/import', [TeacherController::class, 'import'])->name('teachers.import');
            Route::resource('teachers', TeacherController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::get('/students/import/template', [StudentController::class, 'downloadTemplate'])->name('students.import-template');
            Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
            Route::get('/students/nametags', [StudentController::class, 'printNametags'])->name('students.nametags');
            Route::get('/students/{student}/nametag', [StudentController::class, 'printNametag'])->name('students.nametag');
            Route::resource('students', StudentController::class)->only(['index', 'store', 'update', 'destroy']);
        });
    });
});

require __DIR__.'/auth.php';
