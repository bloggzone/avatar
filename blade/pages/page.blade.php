@extends($layout)

@section('head')
<title>{{ imake_stringcase("ucwords", str_replace('-', ' ', $page)) }}</title>
@endsection

@section('content')
@include('pages.' . $page)
@endsection