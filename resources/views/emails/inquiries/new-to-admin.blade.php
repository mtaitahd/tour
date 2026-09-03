@component('mail::message')
# New {{ $inquiry->type_label }}

A new inquiry has been submitted on your website.

**Submitted by:**  
{{ $inquiry->name }}  
Email: {{ $inquiry->email }}  
Phone/WhatsApp: {{ $inquiry->phone ?? 'Not provided' }}
@if($inquiry->country)
Country: {{ $inquiry->country }}
@endif

**Tour:**  
@if($inquiry->tour)
    {{ $inquiry->tour->title }} ({{ route('tour.show', $inquiry->tour->slug) }})
@else
    General inquiry (no specific tour)
@endif

@if($inquiry->isTourBooking())
**Trip Details:**  
Travelling as: {{ $inquiry->companions ?? 'Not specified' }}  
Accommodation preference: {{ $inquiry->accommodation ?? 'Not specified' }}  
Room type: {{ $inquiry->room_type ?? 'Not specified' }} | Bed type: {{ $inquiry->bed_type ?? 'Not specified' }}  
Budget range (per person): {{ $inquiry->budget_min ? '$' . number_format($inquiry->budget_min, 0) : '-' }} to {{ $inquiry->budget_max ? '$' . number_format($inquiry->budget_max, 0) : '-' }}  
Adult age range: {{ $inquiry->adult_age_range ?? 'Not specified' }} | Children age range: {{ $inquiry->children_age_range ?? 'None' }}

@endif
**Preferred Dates:**  
Start: {{ $inquiry->preferred_start_date ? $inquiry->preferred_start_date->format('d M Y') : 'Not specified' }}  
End: {{ $inquiry->preferred_end_date ? $inquiry->preferred_end_date->format('d M Y') : 'Not specified' }}

**Group Size:**  
Adults: {{ $inquiry->adults }} | Children: {{ $inquiry->children }}

**Message:**  
{!! nl2br(e($inquiry->message)) !!}

**Submitted at:** {{ $inquiry->created_at->format('d M Y H:i') }}

@component('mail::button', ['url' => route('admin.inquiries.show', $inquiry)])
View This Inquiry in Admin
@endcomponent

Thanks,  
{{ \App\Models\Setting::get('site_name', 'Afro-Vertex Tours') }} System
@endcomponent