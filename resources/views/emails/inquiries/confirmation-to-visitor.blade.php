@component('mail::message')
# Thank You for Reaching Out!

Dear {{ $inquiry->name }},

We have received your inquiry and our team is reviewing it right now.  
We will get back to you within 24–48 hours with more details and a personalized proposal.

**Your Inquiry Summary:**

**Tour:**  
@if($inquiry->tour)
    {{ $inquiry->tour->title }}
@else
    General inquiry
@endif

@if($inquiry->isTourBooking())
**Travelling as:** {{ $inquiry->companions ?? 'Not specified' }}  
**Accommodation preference:** {{ $inquiry->accommodation ?? 'Not specified' }}  
**Budget range (per person):** {{ $inquiry->budget_min ? '$' . number_format($inquiry->budget_min, 0) : '-' }} to {{ $inquiry->budget_max ? '$' . number_format($inquiry->budget_max, 0) : '-' }}

@endif
**Preferred Dates:** {{ $inquiry->preferred_start_date ? $inquiry->preferred_start_date->format('d M Y') : '-' }} to {{ $inquiry->preferred_end_date ? $inquiry->preferred_end_date->format('d M Y') : '-' }}

**Group:** {{ $inquiry->adults }} Adults, {{ $inquiry->children }} Children

**Your Message:**  
{!! nl2br(e($inquiry->message)) !!}

If you have any additional information or questions, feel free to reply directly to this email{{ \App\Models\Setting::get('whatsapp_number') ? ' or contact us via WhatsApp: +' . \App\Models\Setting::get('whatsapp_number') : '' }}.

We look forward to helping you plan your unforgettable African adventure!

Best regards,  
{{ \App\Models\Setting::get('site_name', 'Afro-Vertex Tours & Safaris') }} Team  
{{ \App\Models\Setting::get('site_email', 'info@afrovertextours.com') }}
@if(\App\Models\Setting::get('footer_phone'))
{{ \App\Models\Setting::get('footer_phone') }} (WhatsApp available)
@endif

@component('mail::button', ['url' => route('home')])
Explore More Tours
@endcomponent
@endcomponent