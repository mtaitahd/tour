
<a href="<?php echo e(route('tours.index')); ?>"
   class="wwgt-country<?php echo e($wwgtFeaturedFlag ? '' : ' wwgt-country--more'); ?>"
   data-name="<?php echo e(\Illuminate\Support\Str::lower($wwgtC['name'])); ?>"
   data-raw="<?php echo e($wwgtC['name']); ?>"
   data-code="<?php echo e($wwgtC['code']); ?>"
   <?php echo e($wwgtFeaturedFlag ? '' : 'hidden'); ?>

   role="listitem">
    
    <span class="wwgt-country__flag" aria-hidden="true">
        <img src="https://flagcdn.com/w40/<?php echo e(\Illuminate\Support\Str::lower($wwgtC['code'])); ?>.png"
             srcset="https://flagcdn.com/w80/<?php echo e(\Illuminate\Support\Str::lower($wwgtC['code'])); ?>.png 2x"
             alt="<?php echo e($wwgtC['name']); ?> flag"
             width="40" height="30" loading="lazy">
    </span>
    <span class="wwgt-country__name"><?php echo e($wwgtC['name']); ?></span>
    <span class="wwgt-country__chev" aria-hidden="true">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
    </span>
</a>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\frontend\partials\country-card.blade.php ENDPATH**/ ?>