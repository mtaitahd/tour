
<?php
    /* Channel URL managed from Admin → Settings (social_youtube) */
    $ytChannel = \App\Models\Setting::get('social_youtube');

    /* ── EDIT HERE: your three latest videos ─────────────────────
       'thumbnail' → URL to a 16:9 thumbnail (ideally 1280×720)
                     Tip: https://i.ytimg.com/vi/VIDEO_ID/maxresdefault.jpg
       'title'     → video title
       'views'     → e.g. "2.9K views"
       'when'      → e.g. "2 weeks ago"
       'url'       → https://www.youtube.com/watch?v=VIDEO_ID
       ──────────────────────────────────────────────────────────── */
    $ytVideos = [
        [
            'thumbnail' => asset('public/safari-countries/tanzania.webp'),
            'title'     => 'The Great Migration — Serengeti Up Close',
            'views'     => '12K views',
            'when'      => '2 weeks ago',
            'url'       => $ytChannel ?: '#',
        ],
        [
            'thumbnail' => asset('public/safari-countries/tanzania.webp'),
            'title'     => 'Climbing Mount Kilimanjaro — Machame Route',
            'views'     => '8.4K views',
            'when'      => '1 month ago',
            'url'       => $ytChannel ?: '#',
        ],
        [
            'thumbnail' => asset('public/safari-countries/botswana.webp'),
            'title'     => 'Ngorongoro Crater — A Day in the Caldera',
            'views'     => '6.1K views',
            'when'      => '2 months ago',
            'url'       => $ytChannel ?: '#',
        ],
    ];

    /* Channel subscribe-confirmation link — opens YouTube's one-click
       "Confirm channel subscription" prompt in a new tab. */
    $ytSubUrl = $ytChannel ? $ytChannel . (str_contains($ytChannel, '?') ? '&' : '?') . 'sub_confirmation=1' : null;
?>

<?php if($ytChannel && count($ytVideos) > 0): ?>
<section class="av-yt" id="avSubscribe">
    <div class="av-yt__container">

        
        <div class="av-yt__head">
            <h2 class="av-yt__title">Subscribe Afro&#8209;Vertex Tours &amp; Safaris on YouTube</h2>
            <span class="av-yt__line" aria-hidden="true"></span>
        </div>

        
        <div class="av-yt__grid">
            <?php $__currentLoopData = $ytVideos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a class="av-yt-card" href="<?php echo e($video['url']); ?>" target="_blank" rel="noopener noreferrer">
                    <span class="av-yt-card__thumb">
                        <img src="<?php echo e($video['thumbnail']); ?>"
                             alt="<?php echo e($video['title']); ?>"
                             loading="lazy"
                             width="1280" height="720">
                        <span class="av-yt-card__play" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="#ffffff" xmlns="http://www.w3.org/2000/svg"><path d="M8 5.5v13l11-6.5L8 5.5z"/></svg>
                        </span>
                    </span>
                    <span class="av-yt-card__title"><?php echo e($video['title']); ?></span>
                    <span class="av-yt-card__meta"><?php echo e($video['views']); ?>, <?php echo e($video['when']); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <a class="av-yt__btn" href="<?php echo e($ytSubUrl ?? $ytChannel); ?>" target="_blank" rel="noopener noreferrer">
            <svg width="27" height="27" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="2.4" y="5.4" width="19.2" height="13.2" rx="3.6" stroke="#ffffff" stroke-width="1.8"/><path d="M10.2 9.2v5.6l5-2.8-5-2.8z" fill="#ffffff"/></svg>
            Subscribe on YouTube
        </a>

    </div>
</section>

<script>
(function () {
    var el = document.getElementById('avSubscribe');
    if (!el) return;
    if (!('IntersectionObserver' in window)) {
        el.classList.add('is-visible');
        return;
    }
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                el.classList.add('is-visible');
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.12 });
    io.observe(el);
})();
</script>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\frontend\partials\youtube-subscribe.blade.php ENDPATH**/ ?>