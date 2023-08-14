@php
    $images         = collect($images);
    $first          = $images->shuffle()->shift();
    $cover_img      = blade_image($keyword,TRUE);
    $max_image      = MAX_IMAGE_RESULT;
    $ads_link       = ADS_LINK;
	 
@endphp
<article>
    @if($first)
    <figure>
        <noscript>
            <img src="{{ $cover_img }}" alt="{{ $first['title'] }}" width="640" height="360" />
        </noscript>
        <img class="v-cover ads-img" src="{{ $cover_img }}" alt="{{ $first['title'] }}" width="100%" style="margin-right: 8px;margin-bottom: 8px;" />
        <figcaption>{{ $first['title'] }} from {{ $first['domain'] }}</figcaption>
    </figure>
    @endif
	
	
    <a href="/" target="_blank">{{ $keyword }} - </a> {!! $sentences !!}

</article>
