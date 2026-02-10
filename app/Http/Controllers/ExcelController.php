<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\APIResponse;

class ExcelController extends Controller
{
    use APIResponse;

    // Export users to Excel
    public function export()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    // Import users from Excel
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);
        Excel::import(new UsersImport, $request->file('file'));
        return $this->success([], 'Users imported successfully');
    }
}
