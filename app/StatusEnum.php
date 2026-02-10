<?php

namespace App;

trait StatusEnum
{
    public static $SUCCESS = 200;
    public static $ERROR = 400;
    public static $NOT_FOUND = 404;
    public static $UNAUTHORIZED = 401;
    public static $FORBIDDEN = 403;
    public static $VALIDATION_ERROR = 422;
}
