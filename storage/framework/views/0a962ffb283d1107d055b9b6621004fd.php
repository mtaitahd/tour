<?php $__env->startComponent('mail::message'); ?>
# New <?php echo e($inquiry->type_label); ?>


A new inquiry has been submitted on your website.

**Submitted by:**  
<?php echo e($inquiry->name); ?>  
Email: <?php echo e($inquiry->email); ?>  
Phone/WhatsApp: <?php echo e($inquiry->phone ?? 'Not provided'); ?>

<?php if($inquiry->country): ?>
Country: <?php echo e($inquiry->country); ?>

<?php endif; ?>

**Tour:**  
<?php if($inquiry->tour): ?>
    <?php echo e($inquiry->tour->title); ?> (<?php echo e(route('tour.show', $inquiry->tour->slug)); ?>)
<?php else: ?>
    General inquiry (no specific tour)
<?php endif; ?>

<?php if($inquiry->isTourBooking()): ?>
**Trip Details:**  
Travelling as: <?php echo e($inquiry->companions ?? 'Not specified'); ?>  
Accommodation preference: <?php echo e($inquiry->accommodation ?? 'Not specified'); ?>  
Room type: <?php echo e($inquiry->room_type ?? 'Not specified'); ?> | Bed type: <?php echo e($inquiry->bed_type ?? 'Not specified'); ?>  
Budget range (per person): <?php echo e($inquiry->budget_min ? '$' . number_format($inquiry->budget_min, 0) : '-'); ?> to <?php echo e($inquiry->budget_max ? '$' . number_format($inquiry->budget_max, 0) : '-'); ?>  
Adult age range: <?php echo e($inquiry->adult_age_range ?? 'Not specified'); ?> | Children age range: <?php echo e($inquiry->children_age_range ?? 'None'); ?>


<?php endif; ?>
**Preferred Dates:**  
Start: <?php echo e($inquiry->preferred_start_date ? $inquiry->preferred_start_date->format('d M Y') : 'Not specified'); ?>  
End: <?php echo e($inquiry->preferred_end_date ? $inquiry->preferred_end_date->format('d M Y') : 'Not specified'); ?>


**Group Size:**  
Adults: <?php echo e($inquiry->adults); ?> | Children: <?php echo e($inquiry->children); ?>


**Message:**  
<?php echo nl2br(e($inquiry->message)); ?>


**Submitted at:** <?php echo e($inquiry->created_at->format('d M Y H:i')); ?>


<?php $__env->startComponent('mail::button', ['url' => route('admin.inquiries.show', $inquiry)]); ?>
View This Inquiry in Admin
<?php echo $__env->renderComponent(); ?>

Thanks,  
<?php echo e(\App\Models\Setting::get('site_name', 'Afro-Vertex Tours')); ?> System
<?php echo $__env->renderComponent(); ?><?php /**PATH C:\xampp\htdocs\tour\resources\views\emails\inquiries\new-to-admin.blade.php ENDPATH**/ ?>