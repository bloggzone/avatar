@extends($layout)

@section('content')
<h3>Search result for {{$query}} at {{$niche}}</h3>
<section>
@foreach($sub_data as $chunked) 
		@foreach($chunked as $n => $info)
			@php
				$aside_cover 	= ($n % 4 == 0)?'w-100':'';						
				$is_cover 		= ($n % 4 == 0)?'v-cover':'v-image';
				
				
				$json_images 	= json_decode($info['json_images'], TRUE);

				$first_image	= $json_images['images'][0];

				$keyword 		= $info['keyword'];
				$slug 			= $first_image['slug'];
				$post_url		= imake_url($niche,$slug);

				//$img_url		= $first_image['url'];
				$img_url		= blade_image($keyword);

				$json_sentences = json_decode($info['json_sentences'], TRUE);

				$max_word 		= ($n % 4 == 0)?120:30;

				$sentences      = collect($json_sentences)->shuffle()->take(5)->implode(' ');
				$sentences_txt  = word_limiter($sentences, $max_word,'.');

				$img_alt 		= imake_stringcase("ucwords", $keyword);

			@endphp
		<aside class="{{$aside_cover}}">
            <center>
            	<a href="{{ $post_url }}">
            		<img class="{{$is_cover}}" alt="{{ $img_alt }}" src="{{ $img_url }}" width="100%" />
            	</a>
            	<h3><a href="{{ $post_url }}">{{ imake_stringcase("ucwords", $keyword) }}</a></h3>
            </center>
            <p align="justify">{{ $sentences_txt }}</p>
        </aside>
		@endforeach 
@endforeach 	
</section>	
@endsection