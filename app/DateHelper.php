<?php

namespace App;

trait DateHelper
{
    public function formatDate($date)
    {
        return $date ? $date->format('Y-m-d H:i:s') : null;
    }
}
