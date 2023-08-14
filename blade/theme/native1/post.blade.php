@extends($layout)

@php      
    $related        = collect($random_related);
    $sentences      = collect($sentences);
    $images         = collect($images);    
    $image          = $images->shuffle()->shift();
    $max_image      = MAX_IMAGE_RESULT;
    $ads_link       = ADS_LINK;
    $cover_img      = blade_image($keyword,TRUE);
    //$cover_img      = cdn_image($image['url']);
@endphp

@section('title')
{{ $title }}
@endsection

@section('head')
@include('json_id')
<link rel="preconnect" href="https://i2.wp.com">
<link rel="dns-prefetch" href="https://i2.wp.com">
<link rel="preconnect" href="https://i.pinimg.com">
<link rel="dns-prefetch" href="https://i.pinimg.com">
<link rel="preload" href="{{ $cover_img }}" as="image" media="(max-width: 420px)">
<link rel="preload" href="{{ $cover_img }}" as="image" media="(min-width: 420.1px)" >
@endsection

@section('content')
<article>
    <p><strong>{{ imake_stringcase("ucwords", $keyword) }}</strong>. {{ $sentences->shuffle()->take(2)->implode(' ') }}</p>
    @if($image)
    <figure>
        <img class="img-fluid mx-auto d-block ads-img" src="{{ $cover_img }}" alt="{{ $image['title'] }}" />
        <br>
        <figcaption>{{ $image['title'] }} from {{ $image['domain'] }}</figcaption>
    </figure>
    @endif
    <p>
        {{ $sentences->shuffle()->take(3)->implode(' ') }}
    </p>
    <h3>{{ $image['title'] }}</h3>
    <p>{{ $sentences->shuffle()->pop() }} {{ $sentences->shuffle()->take(3)->implode(' ') }}</p>
    @include('ads_in_article')
</article>
<section>
@foreach($images->shuffle()->take($max_image) as $n =>  $image)
@php
    $mobile_img     = cdn_image($image['url']);

    $sentences_p    = $sentences->shuffle()->take(5)->implode(' ');
    $sentences_txt  = word_limiter($sentences_p, 60,'.');
@endphp

    <aside>
        <img class="img-fluid mx-auto d-block ads-img lazyload" alt="{{ $image['title'] }}" data-src="{{ $mobile_img }}" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=="  />
        <br>
        <small>Source: {{ $image['domain'] }}</small>
            @if(strpos($ads_link, '//') !== false)
            <center>
                <button class="btn btn-sm btn-success ads-img">Check Details</button>
            </center>
            @endif
        <p align="justify">{{ $sentences_txt }}</p>
    </aside>
@endforeach
</section>
<section>
@foreach($sentences->shuffle()->chunk(4) as $chunked_sentences)
    @php
        $chunked_sentences  = collect($chunked_sentences);
        $chunked_h3         = $chunked_sentences->shift();
        $chunked_p          = $chunked_sentences->implode(' ');
    @endphp
    <h3 class="h6"><b>{{ imake_stringcase("ucfirst", $chunked_h3) }}</b></h3>
    <br/>
    <p>{{$chunked_p}}</p>
@endforeach
</section>
@endsection