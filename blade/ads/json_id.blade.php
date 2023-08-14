@php
$date = date('Y-m-d');
$publishDate = $publishDate??$date;
$niche = $niche??"";
$author = SITE_AUTHOR;
$author_url = "https://www.google.com/search?q={$author}";
$img_url = blade_image($keyword,TRUE);
$img_thumb = "{$img_url}&w=250&h=250&c=7";
$site_title = blade_sitename($niche);
@endphp

<script type="application/ld+json">
  {
    "@context": "https://schema.org/",
    "@type": "Article",
    "author": {
      "@type": "Person",
      "name": "{{ $author }}",
      "url": "{{ $author_url }}"
    },
    "headline": "{{ imake_stringcase("
    ucwords ", $keyword) }}",
    "datePublished": "{{ $publishDate }}",
    "image": "{{ $img_url }}",
    "publisher": {
      "@type": "Organization",
      "name": "{{ $site_title }}",
      "logo": {
        "@type": "ImageObject",
        "url": "{{$img_thumb}}",
        "width": 250,
        "height": 250
      }
    }
  }
</script>