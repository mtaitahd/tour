```blade
@component('mail::message')

# 🌍 New Safari Booking Inquiry

A new safari inquiry has been submitted through the website.

@component('mail::panel')
## 🧾 Inquiry Summary

- **Inquiry Reference:** #{{ strtoupper(substr(md5(time()),0,8)) }}
- **Tour Package:** {{ $data['tour_title'] ?? 'N/A' }}
- **Submitted On:** {{ now()->format('F d, Y \a\t h:i A') }}
@endcomponent

---

# 👤 Guest Information

@component('mail::table')
| Information | Details |
|:------------|:---------|
| **Full Name** | {{ $data['first_name'] }} {{ $data['last_name'] }} |
| **Email Address** | {{ $data['email'] }} |
| **Phone Number** | {{ $data['phone'] ?? 'Not provided' }} |
| **Country of Residence** | {{ $data['country'] ?? 'Not specified' }} |
@endcomponent

---

# ✈️ Safari Preferences

@component('mail::table')
| Preference | Details |
|:-----------|:---------|
| **Travel Date** | {{ $data['travel_date'] ?? 'Flexible / Not specified' }} |
| **Travel Companions** | {{ $data['companions'] ?? 'Not specified' }} |
| **Accommodation Level** | {{ $data['accommodation'] ?? 'Not specified' }} |
| **Room Type** | {{ $data['room_type'] ?? 'Not specified' }} |
| **Bed Preference** | {{ $data['bed_type'] ?? 'Not specified' }} |
| **Budget Range** | ${{ number_format($data['budget_min'] ?? 0) }} - ${{ number_format($data['budget_max'] ?? 0) }} USD Per Person |
@endcomponent

---

# 👨‍👩‍👧 Traveller Details

@component('mail::table')
| Traveller Category | Age Information |
|:------------------|:----------------|
| **Adults** | {{ $data['adult_age'] ?? 'Not specified' }} |
| **Children** | {{ $data['children_age'] ?? 'Not specified' }} |
@endcomponent

---

# 💬 Guest Message

@component('mail::panel')
{{ $data['message'] ?? 'No additional message was provided by the guest.' }}
@endcomponent

---

# ⚡ Quick Actions

@component('mail::button', ['url' => 'mailto:' . $data['email']])
Reply to Guest
@endcomponent

@if(!empty($data['phone']))
@component('mail::button', ['url' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $data['phone'])])
Contact via WhatsApp
@endcomponent
@endif

---

# 📌 Internal Notes

- Respond to the guest within **24 hours**
- Verify availability before confirming
- Prepare quotation based on selected accommodation and travel dates
- Upsell optional safari activities if applicable

---

@component('mail::subcopy')
This email was automatically generated from the Afro-Vertex Tours & Safaris booking inquiry system.
@endcomponent

Thanks,<br>
## Afro-Vertex Tours & Safaris  
_OUR RESPONSIBILITIES AIMS TO YOUR ACHIEVEMENTS_

@endcomponent

