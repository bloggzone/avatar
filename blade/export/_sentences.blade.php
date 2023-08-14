@foreach(collect($sentences)->shuffle()->chunk(4) as $chunked_sentences)
    @php
        $chunked_sentences  = collect($chunked_sentences);
        $chunked_h3         = $chunked_sentences->shift();
        $chunked_p          = $chunked_sentences->implode(' ');
    @endphp
    <h3 class="h6"><b>{{ imake_stringcase("ucfirst", $chunked_h3) }}</b></h3>
    <br/>
    <p>{{$chunked_p}}</p>
@endforeach
