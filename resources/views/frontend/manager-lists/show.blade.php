@extends('frontend.layouts.app')

@php
    use Illuminate\Support\Str;
    $listHeading = $managerList->caption ?: $managerList->title;
    $fallbackImage = asset('assets/images/safari-hero.jpg');
@endphp

@section('page-content')
<div class="sfb-listing-page">
    <div class="sfb-breadcrumb"><div class="sfb-container"><a href="{{ route('home') }}">Home</a><span>/</span><span>{{ $managerList->title }}</span></div></div>
    <div class="sfb-container sfb-main-wrap">
        @if($previewMode ?? false)
            <div class="alert alert-info" role="status"><strong>Preview mode:</strong> This listing is visible only to authorized admins. <a href="{{ $managerList->content_type === 'pages' ? route('admin.manager-lists.pages') : route('admin.manager-lists.tours') }}">Back to Manager Lists</a></div>
        @endif
        <div class="sfb-layout manager-list-layout">
            <main class="sfb-results" id="sfb-results-start" aria-label="{{ $managerList->title }}">
                <header class="sfb-results-header">
                    <h1>{{ $listHeading }}</h1>
                    @if($managerList->introduction)<div class="manager-list-introduction">{!! $managerList->introduction !!}</div>@endif
                </header>
                <div class="sfb-results-info"><strong>{{ $items->firstItem() ?: 0 }}&ndash;{{ $items->lastItem() ?: 0 }} of {{ number_format($items->total()) }}</strong><span>{{ Str::plural($managerList->content_type === 'tours' ? 'tour' : 'page', $items->total()) }}</span></div>
                <div class="sfb-tour-grid">
                    @forelse($items as $item)
                        @if($managerList->content_type === 'tours')
                            @php
                                $destinationsText = $item->destinations->sortBy('pivot.order')->take(4)->pluck('name')->implode(', ');
                                $typeText = $item->categories->take(2)->pluck('name')->implode(', ');
                                $durationText = $item->duration_days ? $item->duration_days . ' ' . Str::plural('day', $item->duration_days) : 'Flexible duration';
                            @endphp
                            <article class="sfb-tour-card">
                                <a class="sfb-tour-card__full-link" href="{{ route('tour.show', $item->slug) }}" aria-label="View {{ $item->cardTitle() }}"></a>
                                <div class="sfb-tour-card__image-wrap"><img src="{{ $item->cardImageUrl('medium') }}" alt="{{ $item->cardTitle() }}" loading="lazy"><div class="sfb-tour-card__gradient" aria-hidden="true"></div><h2>{{ $item->cardTitle() }}</h2></div>
                                <div class="sfb-tour-card__body">
                                    <div class="sfb-tour-card__meta-grid"><div><span>Duration</span><strong>{{ $durationText }}</strong></div><div><span>Destination</span><strong>{{ $destinationsText ?: 'East Africa' }}</strong></div><div><span>Travel style</span><strong>{{ Str::title(str_replace('_', ' ', (string) $item->tour_level)) ?: 'Tailor-made' }}</strong></div><div><span>Tour type</span><strong>{{ $typeText ?: 'Safari adventure' }}</strong></div></div>
                                    <div class="sfb-tour-card__footer"><div class="sfb-tour-card__rating"><strong>Plan your trip</strong><span>Expertly arranged</span></div><div class="sfb-tour-card__price"><strong>View details</strong></div></div>
                                    <a href="{{ route('tour.show', $item->slug) }}" class="sfb-tour-card__cta">View Tour</a>
                                </div>
                            </article>
                        @else
                            @php
                                $heroImage = $item->hasHeroImage() ? ($item->heroUrl('medium') ?: $item->heroUrl() ?: $fallbackImage) : $fallbackImage;
                                $plainText = trim(preg_replace('/\s+/', ' ', strip_tags((string) $item->content)));
                            @endphp
                            <article class="sfb-tour-card sfb-page-card">
                                <a class="sfb-tour-card__full-link" href="{{ route('page.show', $item->slug) }}" aria-label="Read {{ $item->title }}"></a>
                                <div class="sfb-tour-card__image-wrap"><img src="{{ $heroImage }}" alt="{{ $item->title }}" loading="lazy"><div class="sfb-tour-card__gradient" aria-hidden="true"></div><h2>{{ $item->title }}</h2></div>
                                <div class="sfb-tour-card__body">
                                    <div class="sfb-tour-card__meta-grid"><div><span>Type</span><strong>Information</strong></div><div><span>Reading time</span><strong>{{ $item->readingMinutes() }} min</strong></div><div><span>Length</span><strong>{{ number_format(str_word_count($plainText)) }} words</strong></div><div><span>Updated</span><strong>{{ $item->updated_at?->format('j M Y') ?: 'Recently' }}</strong></div></div>
                                    <div class="sfb-tour-card__footer"><div class="sfb-tour-card__rating"><strong>Read online</strong><span>Helpful travel information</span></div><div class="sfb-tour-card__price"><span>Access</span><strong>Free</strong></div></div>
                                    <a href="{{ route('page.show', $item->slug) }}" class="sfb-tour-card__cta">Read More</a>
                                </div>
                            </article>
                        @endif
                    @empty
                        <div class="sfb-empty-results"><h2>No {{ $managerList->content_type === 'tours' ? 'tours' : 'pages' }} are available in this listing yet.</h2><p>Please check back soon.</p></div>
                    @endforelse
                </div>
                @if($items->hasPages())<div class="sfb-pagination">{{ $items->links() }}</div>@endif
                @if($faqs->isNotEmpty())
                    <section class="manager-list-faq" aria-labelledby="manager-list-faq-title">
                        <h2 id="manager-list-faq-title">Frequently Asked Questions</h2>
                        @foreach($faqs as $faq)
                            <details><summary>{{ $faq['question'] }}</summary><div>{{ $faq['answer'] }}</div></details>
                        @endforeach
                    </section>
                    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faqs->map(fn ($faq) => ['@type' => 'Question', 'name' => $faq['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']]])->values()->all()], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                @endif
            </main>
        </div>
    </div>
</div>
<style>
.manager-list-layout{grid-template-columns:minmax(0,1fr)!important}.manager-list-introduction{margin-top:14px;line-height:1.75}.manager-list-faq{margin:42px 0 20px}.manager-list-faq h2{margin-bottom:18px}.manager-list-faq details{border-bottom:1px solid #dce2eb;padding:16px 0}.manager-list-faq summary{font-weight:700;cursor:pointer}.manager-list-faq details div{padding:12px 0 0;line-height:1.65}.sfb-pagination{margin-top:28px}
</style>
@endsection
