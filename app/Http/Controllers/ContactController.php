<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Mail\NewInquiryNotification;
use App\Mail\InquiryConfirmation;
use App\Models\Inquiry;
use App\Models\Setting;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'required|string|max:50',
            'message'    => 'required|string|min:10',
            // reCAPTCHA rule restored: it was commented out, which made every
            // contact submission fail validation below (the verified key was
            // never present in $validated).
            'g-recaptcha-response' => 'required|string',
        ]);

        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => config('services.recaptcha.secret_key'),
            'response' => $validated['g-recaptcha-response'],
            'remoteip' => $request->ip(),
        ]);

        if (!$recaptchaResponse->successful() || !$recaptchaResponse->json('success')) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'Please complete the reCAPTCHA verification and try again.',
            ]);
        }

        // Fixed: 'type' wasn't in Inquiry's $fillable (and the column itself didn't
        // exist until now), so this was previously silently dropped on every contact
        // submission — every contact and tour inquiry looked identical in admin.
        $inquiry = Inquiry::create([
            'type'    => 'contact',
            'name'    => $validated['first_name'] . ' ' . $validated['last_name'],
            'email'   => $validated['email'],
            'phone'   => $validated['phone'],
            'message' => $validated['message'],
            'status'  => 'pending',
        ]);

        // Fixed: this was previously commented out entirely — a contact submission
        // saved to the database but never actually notified anyone by email.
        $adminEmail = Setting::get('site_email', 'info@afrovertextours.com');
        Mail::to($adminEmail)->send(new NewInquiryNotification($inquiry));
        Mail::to($inquiry->email)->send(new InquiryConfirmation($inquiry));

        return redirect()->back()->with('success', 'Thank you! Your message has been sent. We will get back to you soon.');
    }
}
