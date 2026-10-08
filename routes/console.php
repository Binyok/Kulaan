<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('kulaan:process-matching')->everyFiveMinutes();
