<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\DemoRequestMail;

class LandingController extends Controller
{
    public function sendDemoRequest(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'company_size' => 'nullable|string',
        ]);

        try {
            Mail::to('info@hamzahllc.com')->send(new DemoRequestMail($validated));
            return redirect()->back()->with('success', 'Thank you! Your demo request has been sent successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Sorry, something went wrong. Please try again later.');
        }
    }
}
