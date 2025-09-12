<?php

namespace App\Http\Controllers;

use App\Mail\SupportMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $user = auth()->user();

        $to = config('mail.support_address', 'soporte@tuempresa.com');

        // Usar el Mailable en lugar de Mail::raw
        Mail::to($to)->send(new SupportMail(
            $user,
            $validated['subject'],
            $validated['message']
        ));

        return back()->with('status', 'support-sent');
    }
}
