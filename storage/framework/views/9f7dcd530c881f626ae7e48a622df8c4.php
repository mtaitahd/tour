```blade
<?php $__env->startComponent('mail::message'); ?>

# 🌍 New Safari Booking Inquiry

A new safari inquiry has been submitted through the website.

<?php $__env->startComponent('mail::panel'); ?>
## 🧾 Inquiry Summary

- **Inquiry Reference:** #<?php echo e(strtoupper(substr(md5(time()),0,8))); ?>

- **Tour Package:** <?php echo e($data['tour_title'] ?? 'N/A'); ?>

- **Submitted On:** <?php echo e(now()->format('F d, Y \a\t h:i A')); ?>

<?php echo $__env->renderComponent(); ?>

---

# 👤 Guest Information

<?php $__env->startComponent('mail::table'); ?>
| Information | Details |
|:------------|:---------|
| **Full Name** | <?php echo e($data['first_name']); ?> <?php echo e($data['last_name']); ?> |
| **Email Address** | <?php echo e($data['email']); ?> |
| **Phone Number** | <?php echo e($data['phone'] ?? 'Not provided'); ?> |
| **Country of Residence** | <?php echo e($data['country'] ?? 'Not specified'); ?> |
<?php echo $__env->renderComponent(); ?>

---

# ✈️ Safari Preferences

<?php $__env->startComponent('mail::table'); ?>
| Preference | Details |
|:-----------|:---------|
| **Travel Date** | <?php echo e($data['travel_date'] ?? 'Flexible / Not specified'); ?> |
| **Travel Companions** | <?php echo e($data['companions'] ?? 'Not specified'); ?> |
| **Accommodation Level** | <?php echo e($data['accommodation'] ?? 'Not specified'); ?> |
| **Room Type** | <?php echo e($data['room_type'] ?? 'Not specified'); ?> |
| **Bed Preference** | <?php echo e($data['bed_type'] ?? 'Not specified'); ?> |
| **Budget Range** | $<?php echo e(number_format($data['budget_min'] ?? 0)); ?> - $<?php echo e(number_format($data['budget_max'] ?? 0)); ?> USD Per Person |
<?php echo $__env->renderComponent(); ?>

---

# 👨‍👩‍👧 Traveller Details

<?php $__env->startComponent('mail::table'); ?>
| Traveller Category | Age Information |
|:------------------|:----------------|
| **Adults** | <?php echo e($data['adult_age'] ?? 'Not specified'); ?> |
| **Children** | <?php echo e($data['children_age'] ?? 'Not specified'); ?> |
<?php echo $__env->renderComponent(); ?>

---

# 💬 Guest Message

<?php $__env->startComponent('mail::panel'); ?>
<?php echo e($data['message'] ?? 'No additional message was provided by the guest.'); ?>

<?php echo $__env->renderComponent(); ?>

---

# ⚡ Quick Actions

<?php $__env->startComponent('mail::button', ['url' => 'mailto:' . $data['email']]); ?>
Reply to Guest
<?php echo $__env->renderComponent(); ?>

<?php if(!empty($data['phone'])): ?>
<?php $__env->startComponent('mail::button', ['url' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $data['phone'])]); ?>
Contact via WhatsApp
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>

---

# 📌 Internal Notes

- Respond to the guest within **24 hours**
- Verify availability before confirming
- Prepare quotation based on selected accommodation and travel dates
- Upsell optional safari activities if applicable

---

<?php $__env->startComponent('mail::subcopy'); ?>
This email was automatically generated from the Afro-Vertex Tours & Safaris booking inquiry system.
<?php echo $__env->renderComponent(); ?>

Thanks,<br>
## Afro-Vertex Tours & Safaris  
_OUR RESPONSIBILITIES AIMS TO YOUR ACHIEVEMENTS_

<?php echo $__env->renderComponent(); ?>

<?php /**PATH C:\xampp\htdocs\tour\resources\views\emails\tour-inquiry.blade.php ENDPATH**/ ?>