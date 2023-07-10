<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>@yield('title','Bucket Manage')</title>
	<!-- bootstrap css-->
	<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
	<!-- bootstrap js-->
	<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
	<!-- custome css -->
	<link href="{{ asset('css/custome.css') }}" rel="stylesheet">	
</head>

<body>
	@yield('content')
	@yield('footer')
</body>

</html>