# Contact Form + Tour Booking/Inquiries System — Deployment Notes

## 1. Copy files

Overwrite the matching paths with the files in this zip.

## 2. Delete two now-dead files

```
app/Mail/TourInquiryMail.php
resources/views/emails/tour-inquiry.blade.php
```

Fully superseded by `NewInquiryNotification` + `InquiryConfirmation`, which already
existed in your codebase but were never wired into anything.

## 3. Run the migration

```
php artisan migrate
```

Adds to `inquiries`: `type`, `companions`, `accommodation`, `room_type`, `bed_type`,
`budget_min`, `budget_max`, `adult_age_range`, `children_age_range`, `country`.

## 4. Critical bugs fixed (read this — explains why things look different now)

1. **The tour booking form never saved anything.** `InquiryController::store()` only
   sent an email to a hardcoded address (`afrovertex@gmail.com`) and had a comment
   saying "optional: save to Inquiry model" — it never did. Every tour booking
   request you've ever received only exists in that one inbox, never in Admin →
   Inquiries.
2. **The contact form saved to the database but sent no email at all.** The
   notification line was commented out. You'd only find out about a contact
   submission by manually checking admin.
3. **Two properly-built mail classes already existed, fully unused**:
   `NewInquiryNotification` (to you, with reply-to set to the customer) and
   `InquiryConfirmation` (to the customer, "we got your message"). Matching email
   templates already existed too. Someone had already designed the right system —
   it just was never connected to either form. Both are now used by both forms.
4. A bug in `Admin\InquiryController::update()` checked for status values
   (`'booking'`, `'paid'`) that don't exist in the status enum — only `'confirmed'`
   could ever trigger the "copy tour price to total_amount" logic. Fixed.
5. `/inquiries` POST route was registered twice in `routes/web.php`. Removed the
   duplicate.

## 5. What's new

- **Both forms now save to `Inquiry` and send both emails** (admin notification +
  customer confirmation), using your real Settings for the admin recipient address
  (`site_email`) instead of a hardcoded one.
- **`type` column** distinguishes `contact` vs `tour_booking` submissions — visible
  as a badge in the admin list and detail view, plus a filter in the admin list.
- **The tour booking form now also collects actual headcount** ("How many people
  are travelling?" — Adults/Children number inputs), not just age *ranges*. Age
  range tells you composition; headcount tells you group size — you need both to
  quote accurately. Maps straight onto the existing `adults`/`children` columns.
- **reCAPTCHA added to the contact form**, matching the tour booking form (your
  `.env` already has working `RECAPTCHA_SITE_KEY`/`RECAPTCHA_SECRET_KEY` — this
  just extends the same protection to the second form).
- **Admin Inquiries list**: added Type filter, and status filters for Contacted/
  Confirmed/Cancelled (previously only "All"/"Pending" existed). Added a working
  delete action (previously a stub).
- **Admin Inquiries detail view**: now shows the full trip preferences for tour
  bookings (travelling as, accommodation preference, room/bed type, budget range,
  age ranges) when present, plus a delete button.
- **Both email templates** updated to show the same rich trip details when
  relevant, and to pull phone/email from Settings instead of hardcoded placeholder
  numbers (the confirmation email was signing off with a fake `+255 123 456 789`
  and `info@afrovertextours.com` regardless of your real details).

## 6. One thing to verify after deploying

Send yourself a test submission through both forms and confirm:
- It shows up in Admin → Inquiries with the correct Type badge
- You receive the admin notification email at your configured `site_email`
  (Admin → Settings → Header & Footer tab)
- The submitter receives the confirmation email

If emails don't arrive, double-check your `.env` `MAIL_*` values are correct for
whatever SMTP provider you're using — the code path is correct now, but actual
delivery still depends on those credentials being valid.
