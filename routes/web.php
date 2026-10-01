<?php

use App\Livewire\ResponseCenter;
use Illuminate\Support\Facades\Route;

Route::get('/', ResponseCenter::class)->name('home');
