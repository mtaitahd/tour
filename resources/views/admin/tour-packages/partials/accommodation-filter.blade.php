(function () {
    const level = document.querySelector('[name="tour_level"]');
    if (!level) return;

    const tiers = @json(\App\Models\Accommodation::TOUR_TIERS);
    const normalize = value => value.trim().toLowerCase().replace(/^high classic$/, 'classic');

    function filter() {
        const labels = tiers[level.value] || tiers.mid_range;
        document.querySelectorAll('[data-accommodation-choice]').forEach(select => {
            const row = select.closest('.accommodation-tier-row');
            const label = labels[row.dataset.tierKey];
            row.querySelector('label').textContent = label;
            select.options[0].textContent = 'Choose ' + label + ' accommodation';

            Array.from(select.options).slice(1).forEach(option => {
                const tier = normalize(option.dataset.accommodationTier || '');
                const matches = tier === 'none' || tier === normalize(label);
                option.hidden = !matches;
                // Preserve a legacy selection until the editor replaces it.
                option.disabled = !matches && !option.selected;
            });
        });
    }

    window.filterAccommodationOptions = filter;
    level.addEventListener('change', filter);
    document.addEventListener('change', event => {
        if (event.target.matches('[data-accommodation-choice]')) filter();
    });
    filter();
})();
