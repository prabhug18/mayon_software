<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use App\APIResponse;

class PdfController extends Controller
{
    use APIResponse;

    // Generate PDF of all users
    public function users()
    {
        $users = User::all();
        $pdf = Pdf::loadView('pdf.users', compact('users'));
        return $pdf->download('users.pdf');
    }
}
