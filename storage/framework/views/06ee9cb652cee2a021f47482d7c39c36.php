<?php $__env->startComponent('mail::message'); ?>
# Thank You for Reaching Out!

Dear <?php echo e($inquiry->name); ?>,

We have received your inquiry and our team is reviewing it right now.  
We will get back to you within 24–48 hours with more details and a personalized proposal.

**Your Inquiry Summary:**

**Tour:**  
<?php if($inquiry->tour): ?>
    <?php echo e($inquiry->tour->title); ?>

<?php else: ?>
    General inquiry
<?php endif; ?>

<?php if($inquiry->isTourBooking()): ?>
**Travelling as:** <?php echo e($inquiry->companions ?? 'Not specified'); ?>  
**Accommodation preference:** <?php echo e($inquiry->accommodation ?? 'Not specified'); ?>  
**Budget range (per person):** <?php echo e($inquiry->budget_min ? '$' . number_format($inquiry->budget_min, 0) : '-'); ?> to <?php echo e($inquiry->budget_max ? '$' . number_format($inquiry->budget_max, 0) : '-'); ?>


<?php endif; ?>
**Preferred Dates:** <?php echo e($inquiry->preferred_start_date ? $inquiry->preferred_start_date->format('d M Y') : '-'); ?> to <?php echo e($inquiry->preferred_end_date ? $inquiry->preferred_end_date->format('d M Y') : '-'); ?>


**Group:** <?php echo e($inquiry->adults); ?> Adults, <?php echo e($inquiry->children); ?> Children

**Your Message:**  
<?php echo nl2br(e($inquiry->message)); ?>


If you have any additional information or questions, feel free to reply directly to this email<?php echo e(\App\Models\Setting::get('whatsapp_number') ? ' or contact us via WhatsApp: +' . \App\Models\Setting::get('whatsapp_number') : ''); ?>.

We look forward to helping you plan your unforgettable African adventure!

Best regards,  
<?php echo e(\App\Models\Setting::get('site_name', 'Afro-Vertex Tours & Safaris')); ?> Team  
<?php echo e(\App\Models\Setting::get('site_email', 'info@afrovertextours.com')); ?>

<?php if(\App\Models\Setting::get('footer_phone')): ?>
<?php echo e(\App\Models\Setting::get('footer_phone')); ?> (WhatsApp available)
<?php endif; ?>

<?php $__env->startComponent('mail::button', ['url' => route('home')]); ?>
Explore More Tours
<?php echo $__env->renderComponent(); ?>
<?php echo $__env->renderComponent(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\emails\inquiries\confirmation-to-visitor.blade.php ENDPATH**/ ?>