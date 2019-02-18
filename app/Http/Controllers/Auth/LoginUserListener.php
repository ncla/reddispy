<?php

namespace App\Http\Controllers\Auth;

interface LoginUserListener
{
    public function successfulLogin();

    public function failedLogin();
}