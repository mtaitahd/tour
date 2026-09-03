<script>
(function () {
    'use strict';

    var faqSection = document.querySelector('[data-sfb-faq]');
    if (faqSection) {
        var faqLinks = Array.prototype.slice.call(faqSection.querySelectorAll('[data-sfb-faq-link]'));
        var faqItems = Array.prototype.slice.call(faqSection.querySelectorAll('[data-sfb-faq-item]'));
        var faqDesktop = window.matchMedia('(min-width: 992px)');
        var FAQ_OFFSET = 110;

        function setActive(index) {
            var key = String(index);
            faqItems.forEach(function (item) {
                item.classList.toggle('is-active', item.dataset.sfbFaqItem === key);
            });
            faqLinks.forEach(function (link) {
                link.classList.toggle('is-active', link.dataset.sfbFaqLink === key);
            });
        }

        function scrollToItem(item, smooth) {
            if (!item) return;
            var top = item.getBoundingClientRect().top + window.scrollY - FAQ_OFFSET;
            window.scrollTo({ top: Math.max(top, 0), behavior: smooth ? 'smooth' : 'auto' });
        }

        function openItem(item) {
            item.classList.add('is-open');
            item.querySelector('.sfb-faq-item__head').setAttribute('aria-expanded', 'true');
            var body = item.querySelector('.sfb-faq-item__body');
            body.style.maxHeight = body.scrollHeight + 'px';
        }

        function closeItem(item) {
            item.classList.remove('is-open');
            item.querySelector('.sfb-faq-item__head').setAttribute('aria-expanded', 'false');
            item.querySelector('.sfb-faq-item__body').style.maxHeight = '';
        }

        function applyMode() {
            if (faqDesktop.matches) {
                faqItems.forEach(closeItem);
            } else {
                faqItems.forEach(function (item, i) {
                    if (i === 0) openItem(item); else closeItem(item);
                });
            }
        }

        faqItems.forEach(function (item) {
            item.querySelector('.sfb-faq-item__head').addEventListener('click', function () {
                var index = parseInt(item.dataset.sfbFaqItem, 10);
                if (!faqDesktop.matches) {
                    var wasOpen = item.classList.contains('is-open');
                    faqItems.forEach(closeItem);
                    if (!wasOpen) openItem(item);
                } else {
                    setActive(isNaN(index) ? 0 : index);
                }
            });
        });

        faqLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                var index = parseInt(link.dataset.sfbFaqLink, 10);
                if (isNaN(index)) return;
                setActive(index);
                var target = faqItems[index];
                if (faqDesktop.matches) {
                    scrollToItem(target, true);
                } else {
                    faqItems.forEach(closeItem);
                    openItem(target);
                    requestAnimationFrame(function () { scrollToItem(target, true); });
                }
            });
        });

        var faqTicking = false;
        window.addEventListener('scroll', function () {
            if (!faqDesktop.matches || faqTicking) return;
            faqTicking = true;
            requestAnimationFrame(function () {
                faqTicking = false;
                var current = 0;
                for (var i = 0; i < faqItems.length; i++) {
                    if (faqItems[i].getBoundingClientRect().top - FAQ_OFFSET <= 140) current = i;
                }
                setActive(current);
            });
        }, { passive: true });

        applyMode();
        var onModeChange = function () { applyMode(); };
        if (faqDesktop.addEventListener) faqDesktop.addEventListener('change', onModeChange);
        else faqDesktop.addListener(onModeChange);
    }
})();
</script>
<?php /**PATH C:\xampp\htdocs\tour\resources\views\frontend\partials\faq-script.blade.php ENDPATH**/ ?>