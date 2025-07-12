<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
</head>
<body>
    <header>My Website</header>
    <div class="content">
        @yield('content')
    </div>
    <footer>Footer Section</footer>
</body>
</html>

@extends('layout')

@section('title', 'Home Page')

@section('content')
    <h1>Welcome to my website!</h1>
@endsection
