@if(!empty($googleReviewsUrl) && in_array(parse_url($googleReviewsUrl, PHP_URL_SCHEME), ['http', 'https'], true))
    <a class="{{ $reviewLinkClass ?? 'td-morelink' }}" href="{{ $googleReviewsUrl }}" target="_blank" rel="noopener noreferrer">Leave a Review</a>
@endif
