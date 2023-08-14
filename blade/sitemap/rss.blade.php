@php

$site_title = blade_sitename($niche);

$rss_title  = "{$site_title} - RSS Channel";

$rss_desc   = "{$site_title} delivers up-to-the-minute news and information on the latest top stories, weather, entertainment, politics and more.";


$base_url   = base_url();
$current_url= current_url();

$years      = date('Y');

$rss_cp     = "Copyright (c) {$years} {$site_title}, Inc. All Rights Reserved.";

$dateRss    = date('r', time());

$itemListTxt = ""; 

$i = 1;       

foreach ($arr_article as $i => $info)
{
	$keyword 	   = $info['keyword'];


  $scheck     = sentencesChecker($info['json_sentences']);
  $sentences = $scheck['sentences'];

  $title    = "";
  $desc     = "";

  if($scheck['blade'] ==='openai')
  {
    $title  = titlebyContent($sentences);
    $desc   = descbyContent($sentences);
  }
  else
  {
    $title   = imake_stringcase("ucwords", $keyword);
    $title   = title_maker($title);

    $desc    = implode(" ", $sentences);
    $desc    = Minify_Html($desc);
    $desc    = word_limiter($desc, 200, '...');
  }    

	$slug 		= $info['slug'];	
	$slug 		= rawurlencode($slug);	
    $post_url 	= imake_url($niche,$slug);

    $publish 	= $info['publish'];
    $date 		= str_replace('+00:00', 'Z', gmdate('c', strtotime($publish)));

    $image 		= blade_image($keyword,TRUE);

    $itemListTxt .= "
    <item>
      <title><![CDATA[{$title}]]></title>
      <description><![CDATA[{$desc}]]></description>
      <link>{$post_url}</link>
      <guid isPermaLink='true'>{$post_url}</guid>
      <pubDate>{$date}</pubDate>
      <media:content medium='image' url='{$image}' height='360' width='480' type='image/jpeg'/>
    </item>
    ";

    $i++;
}

@endphp
{!! '<' . '?' . "xml version='1.0' encoding='UTF-8'?>" !!}
<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/" version="2.0">
  <channel>
    <title><![CDATA[{{ $rss_title }}]]></title>
    <description><![CDATA[{{ $rss_desc }}]]></description>
    <link>{{ $base_url }}</link>
    <atom:link href="{{ $current_url }}" rel="self" type="application/rss+xml"/>
    <category domain="{{ $base_url }}">News</category>
    <generator>Codeigniter</generator>
    <lastBuildDate>{{ $dateRss }}</lastBuildDate>
    <pubDate>{{ $dateRss }}</pubDate>
    <copyright><![CDATA[{{ $rss_cp }}]]></copyright>
    <language><![CDATA[en-US]]></language>
    <ttl>10</ttl>
    {!! $itemListTxt !!}
  </channel>
</rss>
