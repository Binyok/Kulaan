<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('kulaan:process-matching')->everyFiveMinutes();

Schedule::command('kulaan:expire-orders')->everyTenMinutes();

Schedule::command('kulaan:auto-release-escrows')->everyTenMinutes();

Schedule::command('kulaan:enforce-ship-deadlines')->everyTenMinutes();
