<div
    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'filament-notifications-body mt-1 text-sm text-gray-500',
        'dark:text-gray-300' => config('notifications.dark_mode'),
    ]); ?>"
>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\xampp\htdocs\tour\vendor\filament\notifications\resources\views\components\body.blade.php ENDPATH**/ ?>