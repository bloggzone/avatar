{!! '<' . '?' . "xml version='1.0' encoding='UTF-8'?>" !!}
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

@foreach ($arr_slug as $i => $info)
@php
	$slug 		= $info['slug'];	
	$slug 		= rawurlencode($slug);	
    $post_url 	= imake_url($niche,$slug);

    $publish 	= $info['publish'];
    $date 		= str_replace('+00:00', 'Z', gmdate('c', strtotime($publish)));
@endphp
    <url>
    	<loc>{{$post_url}}</loc>
    	<lastmod>{{$date}}</lastmod>
    </url>
@endforeach

</urlset>
