<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\APIResponse;

class ActivityLogController extends Controller
{
    use APIResponse;

    // List all activity logs
    public function index()
    {
        $logs = Activity::with('causer', 'subject')->latest()->paginate(25);
        return $this->success($logs);
    }

    // List activity logs for a specific user
    public function user($userId)
    {
        $logs = Activity::where('causer_id', $userId)->with('subject')->latest()->paginate(25);
        return $this->success($logs);
    }
}
