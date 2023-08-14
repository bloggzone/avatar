@php
    $sentences      = collect($sentences);
    $images         = collect($images);
    $first          = $images->shuffle()->shift();
    $cover_img      = cdn_image($first['url']);
    $max_image      = MAX_IMAGE_RESULT;
    $ads_link       = ADS_LINK;
@endphp
<article>
    <p><strong>{{ $title }}</strong>. {{ $sentences->shuffle()->take(2)->implode(' ') }}</p>
    @if($first)
    <figure>
        <noscript>
            <img src="{{ $cover_img }}" alt="{{ $first['title'] }}" width="640" height="360" />
        </noscript>
        <img class="v-cover ads-img" src="{{ $cover_img }}" alt="{{ $first['title'] }}" width="100%" style="margin-right: 8px;margin-bottom: 8px;" />
        <figcaption>{{ $first['title'] }} from {{ $first['domain'] }}</figcaption>
    </figure>
    @endif
    <p>{!! single_backlink_render($sentences->shuffle()->take(3)->implode(' '), $keyword) !!}</p>
</article>
<!--more-->
<section>
@foreach($images->shuffle()->take($max_image) as $image)
    <aside>
        <img alt="{{ $image['title'] }}" src="{{ cdn_image($image['url']) }}" width="100%" style="margin-right: 8px;margin-bottom: 8px;" />
        <small>Source: <i>{{ $image['domain'] }}</i></small>
        @if(strpos($ads_link, '//') !== false)
        <center>
            <button class="btn btn-sm btn-success ads-img">Check Details</button>
        </center>
        @endif
        <p>{{ $sentences->shuffle()->take(2)->implode(' ') }}</p>
    </aside>
@endforeach
</section>
<section>
@foreach($sentences->shuffle()->take(20)->chunk(4) as $key => $chunked_sentences)
    @php
        $chunked_sentences  = collect($chunked_sentences);
        $chunked_h3         = $chunked_sentences->shift();
        $chunked_p          = $chunked_sentences->implode(' ');
        if($key === 1)
        {
            $chunked_p      = single_backlink_render($chunked_p, $keyword);
        }
    @endphp
    <h3>{{ imake_stringcase("ucfirst", $chunked_h3) }}</h3>
    <br/>
    @if($key === 1)
    <p>{!! $chunked_p !!}</p>
    @else
    <p>{{$chunked_p}}</p>
    @endif
@endforeach
</section>
