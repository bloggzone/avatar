{!! '<' . '?' . "xml version='1.0' encoding='UTF-8'?>" !!} <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    @foreach (niche_arr() as $i => $niche)
    @php
    $niche = rawurlencode($niche);
    $url = base_url("submap/{$niche}.xml");
    @endphp
    <sitemap>
        <loc>{{$url}}</loc>
    </sitemap>
    @endforeach

    </sitemapindex>