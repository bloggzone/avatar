@extends($layout)

@section('title')
{{ $title }}
@endsection

@section('head')
@include('json_id')
<link rel="preconnect" href="https://i2.wp.com">
<link rel="dns-prefetch" href="https://i2.wp.com">
<link rel="preconnect" href="https://i.pinimg.com">
<link rel="dns-prefetch" href="https://i.pinimg.com">
<link rel="preload" href="{{ $image }}" as="image" media="(max-width: 420px)">
<link rel="preload" href="{{ $image }}" as="image" media="(min-width: 420.1px)" >
@endsection

@section('content')
{!! $content !!}
@endsection