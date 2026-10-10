<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('kulaan:process-matching')->everyFiveMinutes();

Schedule::command('kulaan:expire-orders')->everyTenMinutes();
