@if(count($faqs))
    <section class="sfb-faq" data-sfb-faq aria-labelledby="sfb-faq-title">
        <div class="sfb-faq__layout">
            <aside class="sfb-faq__panel">
                <div class="sfb-faq__panel-inner">
                    <div class="sfb-faq-expert">
                        <img class="sfb-faq-expert__photo" src="{{ $faqExpert['image'] }}" alt="{{ $faqExpert['name'] }}" loading="lazy">
                        <span class="sfb-faq-expert__badge">Expert</span>
                        <strong class="sfb-faq-expert__name">{{ $faqExpert['name'] }}</strong>
                        <p>{{ $faqExpert['bio'] }}</p>
                        <a href="{{ $faqExpert['link'] }}">More about {{ Str::before($faqExpert['name'], ' ') }}</a>
                    </div>

                    <div class="sfb-faq-nav-wrap">
                        <ol class="sfb-faq-nav" aria-label="Frequently asked questions">
                            @foreach($faqs as $index => $faq)
                                <li>
                                    <a href="#sfb-faq-{{ $index }}" data-sfb-faq-link="{{ $index }}"{{ $index === 0 ? ' class="is-active"' : '' }}>
                                        <span class="sfb-faq-nav__num">{{ $index + 1 }}.</span>
                                        <span class="sfb-faq-nav__text">{{ $faq['question'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </aside>

            <div class="sfb-faq__content">
                <h2 id="sfb-faq-title">{{ count($faqs) }} Questions About {{ $faqSubject ?? 'African' }} Safari Tours</h2>
                <p class="sfb-faq-answered">
                    Answered by
                    <img src="{{ $faqExpert['image'] }}" alt="" loading="lazy">
                    <strong>{{ $faqExpert['name'] }}</strong>
                </p>

                <ol class="sfb-faq-list">
                    @foreach($faqs as $index => $faq)
                        <li class="sfb-faq-item{{ $loop->first ? ' is-active is-open' : '' }}" id="sfb-faq-{{ $index }}" data-sfb-faq-item="{{ $index }}">
                            <button type="button" class="sfb-faq-item__head" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="sfb-faq-body-{{ $index }}">
                                <span class="sfb-faq-item__num">{{ $index + 1 }}</span>
                                <span class="sfb-faq-item__question">{{ $faq['question'] }}</span>
                                <span class="sfb-faq-item__toggle" aria-hidden="true">
                                    <svg class="tg-plus" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                                    <svg class="tg-minus" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14"/></svg>
                                </span>
                            </button>
                            <div class="sfb-faq-item__body" id="sfb-faq-body-{{ $index }}">
                                <p class="sfb-faq-item__answer">{{ $faq['answer'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>
@endif
