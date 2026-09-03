<?php if(count($faqs)): ?>
    <section class="sfb-faq" data-sfb-faq aria-labelledby="sfb-faq-title">
        <div class="sfb-faq__layout">
            <aside class="sfb-faq__panel">
                <div class="sfb-faq__panel-inner">
                    <div class="sfb-faq-expert">
                        <img class="sfb-faq-expert__photo" src="<?php echo e($faqExpert['image']); ?>" alt="<?php echo e($faqExpert['name']); ?>" loading="lazy">
                        <span class="sfb-faq-expert__badge">Expert</span>
                        <strong class="sfb-faq-expert__name"><?php echo e($faqExpert['name']); ?></strong>
                        <p><?php echo e($faqExpert['bio']); ?></p>
                        <a href="<?php echo e($faqExpert['link']); ?>">More about <?php echo e(Str::before($faqExpert['name'], ' ')); ?></a>
                    </div>

                    <div class="sfb-faq-nav-wrap">
                        <ol class="sfb-faq-nav" aria-label="Frequently asked questions">
                            <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="#sfb-faq-<?php echo e($index); ?>" data-sfb-faq-link="<?php echo e($index); ?>"<?php echo e($index === 0 ? ' class="is-active"' : ''); ?>>
                                        <span class="sfb-faq-nav__num"><?php echo e($index + 1); ?>.</span>
                                        <span class="sfb-faq-nav__text"><?php echo e($faq['question']); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ol>
                    </div>
                </div>
            </aside>

            <div class="sfb-faq__content">
                <h2 id="sfb-faq-title"><?php echo e(count($faqs)); ?> Questions About <?php echo e($faqSubject ?? 'African'); ?> Safari Tours</h2>
                <p class="sfb-faq-answered">
                    Answered by
                    <img src="<?php echo e($faqExpert['image']); ?>" alt="" loading="lazy">
                    <strong><?php echo e($faqExpert['name']); ?></strong>
                </p>

                <ol class="sfb-faq-list">
                    <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="sfb-faq-item<?php echo e($loop->first ? ' is-active is-open' : ''); ?>" id="sfb-faq-<?php echo e($index); ?>" data-sfb-faq-item="<?php echo e($index); ?>">
                            <button type="button" class="sfb-faq-item__head" aria-expanded="<?php echo e($loop->first ? 'true' : 'false'); ?>" aria-controls="sfb-faq-body-<?php echo e($index); ?>">
                                <span class="sfb-faq-item__num"><?php echo e($index + 1); ?></span>
                                <span class="sfb-faq-item__question"><?php echo e($faq['question']); ?></span>
                                <span class="sfb-faq-item__toggle" aria-hidden="true">
                                    <svg class="tg-plus" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                                    <svg class="tg-minus" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14"/></svg>
                                </span>
                            </button>
                            <div class="sfb-faq-item__body" id="sfb-faq-body-<?php echo e($index); ?>">
                                <p class="sfb-faq-item__answer"><?php echo e($faq['answer']); ?></p>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ol>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\frontend\partials\faq-section.blade.php ENDPATH**/ ?>