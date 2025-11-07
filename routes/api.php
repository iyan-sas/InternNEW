<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Event;

Route::get('/events', function () {
    return Event::all()->map(function ($event) {
        return [
            'id' => $event->id,           // ✅ ADD THIS
            'title' => $event->title,
            'start' => $event->date,
        ];
    });
});
