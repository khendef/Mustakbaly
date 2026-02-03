<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('questions', function ($user) {
    return true;
});
