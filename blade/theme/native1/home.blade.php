@extends($layout)

@section('content')
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
        <aside class="card mb-4">
		    <center>
            	<a href="{{ $post_url }}">
            		<img class="card-img-top" alt="{{ $img_alt }}" src="{{ $img_url }}" width="100%" />
            	</a>            	
            </center>
		  <div class="card-body">
		    <h1 class="h3 card-title"><a href="{{ $post_url }}">{{ imake_stringcase("ucwords", $keyword) }}</a></h1>
		    <p class="card-text">{{ $sentences_txt }}</p>
		  </div>
		</aside>
		@endforeach 
@endforeach 	
</section>	
@endsection