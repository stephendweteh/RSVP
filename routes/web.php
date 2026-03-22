<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminMailTemplateController;
use App\Http\Controllers\AdminRsvpController;
use App\Http\Controllers\AdminRsvpTitleController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AdminSliderController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\RsvpCalendarController;
use App\Http\Controllers\RsvpController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/rsvp');

Route::get('/rsvp', [RsvpController::class, 'index'])->name('rsvp.index');
Route::post('/rsvp', [RsvpController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('rsvp.store');
Route::get('/rsvp/calendar/event.ics', RsvpCalendarController::class)->name('rsvp.calendar.ics');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminController::class, 'login']);
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::middleware('administrator')->group(function (): void {
            Route::get('/settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
            Route::put('/settings/sms', [AdminSettingsController::class, 'updateSms'])->name('settings.sms.update');
            Route::put('/settings/calendar', [AdminSettingsController::class, 'updateCalendar'])->name('settings.calendar.update');
            Route::delete('/settings/rsvps', [AdminSettingsController::class, 'destroyAllRsvps'])->name('settings.rsvps.destroy-all');
            Route::get('/settings/mail-templates/{mailTemplate}', [AdminMailTemplateController::class, 'edit'])->name('mail-templates.edit');
            Route::put('/settings/mail-templates/{mailTemplate}', [AdminMailTemplateController::class, 'update'])->name('mail-templates.update');
            Route::post('/settings/mail-templates/{mailTemplate}/reset', [AdminMailTemplateController::class, 'reset'])->name('mail-templates.reset');
        });

        Route::get('/rsvps', [AdminRsvpController::class, 'index'])->name('rsvps.index');
        Route::get('/rsvps/export', [AdminRsvpController::class, 'export'])->name('rsvps.export');
        Route::post('/rsvps/{id}/approve', [AdminRsvpController::class, 'approve'])->name('rsvps.approve');
        Route::post('/rsvps/{id}/reject', [AdminRsvpController::class, 'reject'])->name('rsvps.reject');

        Route::get('/rsvp-title', [AdminRsvpTitleController::class, 'edit'])->name('rsvp-title.edit');
        Route::put('/rsvp-title', [AdminRsvpTitleController::class, 'update'])->name('rsvp-title.update');

        Route::get('/slider', [AdminSliderController::class, 'index'])->name('slider.index');
        Route::post('/slider', [AdminSliderController::class, 'store'])->name('slider.store');
        Route::delete('/slider/{slider}', [AdminSliderController::class, 'destroy'])->name('slider.destroy');
        Route::post('/slider/{slider}/move', [AdminSliderController::class, 'move'])->name('slider.move');

        Route::resource('users', AdminUserController::class)->middleware('manage_users');
    });
});
