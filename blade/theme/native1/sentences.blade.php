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
