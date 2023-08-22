@php
		$static_url = $session_data["static_url"];
		$api_url = $session_data["api_url"];
		$currency = $session_data["currency"];
		$title = $session_data["title"];
@endphp
<script>
	console.log(@json($session_data["session"]))
</script>
<!DOCTYPE html>
  <head>
    <base href="{{ $static_url }}">
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no,minimum-scale=1,width=device-width,height=device-height">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link href="main.css" rel="stylesheet">
    <script>window.language="en";
      window.currency="{{ $currency }}";
    </script>
  </head>
  <body id="gateway-game">
    <script>window.RELEASE_VERSION="v1.8.0";
      window.RELEASE_DATE="21 apr 2022";
      window.serverUrl="{{ $api_url }}";
    </script>
    <script src="vendors.js">
    </script>
    <script src="main.js">
    </script>
  </body>
</html>
