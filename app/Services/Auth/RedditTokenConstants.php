<?php

namespace App\Services\Auth;

class RedditTokenConstants {

    public static $DEFAULT_EXPIRES_IN = 3600;

    public static $REQUIRED_SCOPES = [
        'identity',
        'read'
    ];

}