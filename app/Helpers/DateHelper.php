<?php
namespace App\Helpers;

class DateHelper
{
    public static function format($date, $format = 'Y-m-d H:i:s')
    {
        return $date ? date($format, strtotime($date)) : null;
    }

    public static function now($format = 'Y-m-d H:i:s')
    {
        return date($format);
    }

    public static function diffForHumans($date)
    {
        $timestamp = strtotime($date);
        $diff = time() - $timestamp;
        if ($diff < 60) {
            return $diff . ' seconds ago';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } else {
            return floor($diff / 86400) . ' days ago';
        }
    }
}
