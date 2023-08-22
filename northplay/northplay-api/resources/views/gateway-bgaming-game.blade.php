@php
    $gateway_session_id = $session_data["session_id"];
    $freespins_waiting = true;  
    $time = time();
    $session_debug = [];
    $session_debug['debug_mode'] = true;
    $session_debug['debug_data'] = $session_data;
@endphp
<html>
<head>
<title>{{ $session_data["game_identifier"] }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
@font-face {
  font-family: 'Inter';
  font-style:  normal;
  font-weight: 100;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-Regular.woff2?v=3.19") format("woff2");
}
@font-face {
  font-family: 'Inter';
  font-style:  italic;
  font-weight: 100;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-Regular.woff2?v=3.19") format("woff2");
}

@font-face {
  font-family: 'Inter';
  font-style:  normal;
  font-weight: 200;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-Regular.woff2?v=3.19") format("woff2");
}
@font-face {
  font-family: 'Inter';
  font-style:  italic;
  font-weight: 200;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-Regular.woff2?v=3.19") format("woff2");
}

@font-face {
  font-family: 'Inter';
  font-style:  normal;
  font-weight: 300;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-Regular.woff2?v=3.19") format("woff2");
}
@font-face {
  font-family: 'Inter';
  font-style:  italic;
  font-weight: 300;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-Regular.woff2?v=3.19") format("woff2");
}

@font-face {
  font-family: 'Inter';
  font-style:  normal;
  font-weight: 400;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-Regular.woff2?v=3.19") format("woff2");
}
@font-face {
  font-family: 'Inter';
  font-style:  italic;
  font-weight: 400;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-Italic.woff2?v=3.19") format("woff2");
}

@font-face {
  font-family: 'Inter';
  font-style:  normal;
  font-weight: 500;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-Medium.woff2?v=3.19") format("woff2");
}
@font-face {
  font-family: 'Inter';
  font-style:  italic;
  font-weight: 500;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-MediumItalic.woff2?v=3.19") format("woff2");
}

@font-face {
  font-family: 'Inter';
  font-style:  normal;
  font-weight: 600;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-SemiBold.woff2?v=3.19") format("woff2");
}
@font-face {
  font-family: 'Inter';
  font-style:  italic;
  font-weight: 600;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-SemiBoldItalic.woff2?v=3.19") format("woff2");
}

@font-face {
  font-family: 'Inter';
  font-style:  normal;
  font-weight: 700;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-Bold.woff2?v=3.19") format("woff2");
}
@font-face {
  font-family: 'Inter';
  font-style:  italic;
  font-weight: 700;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-BoldItalic.woff2?v=3.19") format("woff2");
}

@font-face {
  font-family: 'Inter';
  font-style:  normal;
  font-weight: 800;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-ExtraBold.woff2?v=3.19") format("woff2");
}
@font-face {
  font-family: 'Inter';
  font-style:  italic;
  font-weight: 800;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-ExtraBoldItalic.woff2?v=3.19") format("woff2");
}

@font-face {
  font-family: 'Inter';
  font-style:  normal;
  font-weight: 900;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-Black.woff2?v=3.19") format("woff2");
}
@font-face {
  font-family: 'Inter';
  font-style:  italic;
  font-weight: 900;
  font-display: swap;
  src: url("https://rsms.me/inter/font-files/Inter-BlackItalic.woff2?v=3.19") format("woff2");
}


/* CSS */
:root { font-family: 'Inter', sans-serif; }
@supports (font-variation-settings: normal) {
  :root { font-family: 'Inter var', sans-serif; }
}
</style>
@if(!$session_data["game_provider"] === "ryangames")
<script id="launchconfig" type="text/javascript">
window.__OPTIONS__ = {
  "server_id": "demo",
  "env": "production",
  "csrfTokenHeaderName": "{{ $session_data["storage"]["csrfTokenHeaderName"] }}",
  "csrfTokenHeaderValue": "{{ $session_data["storage"]["csrfTokenHeaderValue"] }}",
  "play_token": "{{ $session_data["session_id"] }}",
  "game": "{{ $session_data["storage"]["game"] }}",
  "identifier": "{{ $session_data["storage"]["identifier"] }}",
  "title": "{{ $session_data["storage"]["title"] }}",
  "currency": "{{ $session_data["currency"] }}",
  "cache_id": "{{ $session_data["storage"]["cache_id"] }}",
  "cache_player_id": "{{ $session_data["storage"]["cache_player_id"] }}",
  "locale": "en",
  "api": "{{ $session_data["api_url"] }}",
  "rules_url": "{{ $session_data["storage"]["rules_url"] }}",
  "deposit_url": null,
  "games_page_url": "history.back();",
  "history_url": null,
  "lobby_launch_url": null,
  "game_page_url": null,
  "websocket_url": null,
  "ui": {
    "home_button": true,
    "full_screen_prompt": true,
    "logo": "standard",
    "skin": "basic",
    "skins": [
      "basic"
    ],
    "brand_logo": null,
    "brand": {
      "name": "",
      "logo": null,
      "logo_in_game": null
    },
    "autospins_dialog": "standard",
    "max_autospin_value": null,
    "easter_eggs_available": false,
    "show_rtp_in_rules": true
  },
  "resources_path": "{{ $session_data["resources_path"] }}",
  "license_rules": {},
  "custom": {},
  "actions": {
    "deposit": null,
    "return": null,
    "history": null
  },
  "games_loader_source": "{{ $session_data["games_loader_source"] }}",
  "game_bundle_source": "{{ $session_data["games_bundle_source"] }}",
  "provable_fair": {
    "verify_url": "{{ ($session_data['game_identifier'] === 'HappyBillions' ?  'none' : 'https://demo.bgaming-network.com/api/games/verify') }}"
  },
  "math": {
    "rtp": {
      "main": 50.11,
      "freespin_buy": 50.52,
      "freespin_chance": 50.19
    },
    "gamble_limit": 4000
  }
}
</script>

@else
<script id="launchconfig" type="text/javascript">
window.__OPTIONS__ = {
  "play_token": "{{ $session_data["session_id"] }}",
  "game": "{{ $session_data["game_identifier"] }}",
  "identifier": "ryangames/{{ $session_data["game_identifier"] }}",
  "locale": "en",
  "websocket_url": null,
  "env": "production",
  "csrfTokenHeaderName": "{{ $session_data["storage"]["csrfTokenHeaderName"] }}",
  "resources_path": "{{ $session_data["resources_path"] }}",
  "license_rules": {},
  "ui": {
    "home_button": true,
    "full_screen_prompt": true,
    "logo": "standard",
    "skin": "basic",
    "skins": [
      "basic"
    ],
    "brand_logo": null,
    "brand": {
      "name": "",
      "logo": null,
      "logo_in_game": null
    },
    "autospins_dialog": "standard",
    "max_autospin_value": null,
    "easter_eggs_available": false,
    "show_rtp_in_rules": true
  },
  "custom": {},
  "title": "{{ $session_data["game_identifier"] }}",
  "currency": "{{ $session_data["currency"] }}",
  "cache_id": "{{ $session_data["storage"]["cache_id"] }}",
  "cache_player_id": "{{ $session_data["storage"]["cache_player_id"] }}",
  "games_loader_source": "{{ $session_data["games_loader_source"] }}",
  "csrfTokenHeaderValue": "{{ $session_data["storage"]["csrfTokenHeaderValue"] }}",
  "game_bundle_source": "{{ $session_data["games_bundle_source"] }}",
  "provable_fair": {
    "verify_url": "{{ ($session_data['game_identifier'] === 'HappyBillions' ?  'none' : 'https://demo.bgaming-network.com/api/games/verify') }}"
  },
  "api": "{{ $session_data["api_url"] }}",
  "deposit_url": "https://east.ovh",
  "games_page_url": "history.back();",
  "history_url": "https://east.ovh",
  "rules_url": "/game_assets/HappyBillions/rules.json?time={{ $time }}",
  "lobby_launch_url":  "https://east.ovh",
  "game_page_url":  "https://east.ovh",
  "actions": {
    "deposit": null,
    "return": null,
    "history": null
  },
  "server_id": "ryangames_server_26",
  "math": {
    "rtp": {
      "main": 50.11,
      "freespin_buy": 50.52,
      "freespin_chance": 50.19
    },
    "gamble_limit": 4000
  }
}
</script>
@endif



<style>
    body {background-color: #000000}
</style>
@if(!$session_data["game_provider"] === "ryangames")
<style>
  .preloader, .preloader_figure { position: absolute; top: 50%; left: 50%; -webkit-transform: translate(-50%, -50%); transform: translate(-50%, -50%);}
  .preloader { overflow: visible; padding-top: 2em; height: 0; width: 2em;}
  .preloader_figure { height: 0; width: 0; box-sizing: border-box; border: 0 solid #ff2c2c; border-radius: 50%; -webkit-animation: loader-figure 1.15s infinite cubic-bezier(0.215, 0.61, 0.355, 1); animation: loader-figure 1.15s infinite cubic-bezier(0.215, 0.61, 0.355, 1);}
  .preloader_label { font-family: "Consolas", "Courier", monospace; float: left; margin-left: 50%; -webkit-transform: translateX(-50%); transform: translateX(-50%); margin: 0.5em 0 0 50%; font-size: 0.875em; letter-spacing: 0.1em; line-height: 1.5em; color: #ff2c2c; white-space: nowrap; -webkit-animation: loader-label 1.15s infinite cubic-bezier(0.215, 0.61, 0.355, 1); animation: loader-label 1.15s infinite cubic-bezier(0.215, 0.61, 0.355, 1);}
  @-webkit-keyframes loader-figure { 0% { height: 0; width: 0; }
    30% { height: 2em; width: 2em; border-width: 1em; opacity: 1; }
    100% { height: 2em; width: 2em; border-width: 0; opacity: 0; }}
  @keyframes loader-figure { 0% { height: 0; width: 0; }
    30% { height: 2em; width: 2em; border-width: 1em; opacity: 1; }
    100% { height: 2em; width: 2em; border-width: 0; opacity: 0; }}
  @-webkit-keyframes loader-label { 0% { opacity: 0.35; }
    30% { opacity: 1; }
    100% { opacity: 0.35; }}
  @keyframes loader-label { 0% { opacity: 0.35; }
    30% { opacity: 1; }
    100% { opacity: 0.35; }}
</style>
@endif

<style type="text/css">
		body, html, canvas {
			position: absolute;
			top: 0; 
			bottom: 0;
			left: 0;
			right: 0;
			margin: 0;
			padding: 0;
			overflow: visible;
			width:100%;
		}

		html {
			overflow: hidden;
		}
    #overlay {
      position: fixed;
      display: none;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0,0,0,0.95);
      z-index: 2;
      cursor: pointer;
    }

    #overlay-text{
      position: absolute;
      font-family: sans-serif, serif;
      top: 50%;
      left: 50%;
      font-size: 50px;
      color: white;
      transform: translate(-50%,-50%);
      -ms-transform: translate(-50%,-50%);
    }

		canvas {
			height:100%;
			position: fixed;
			-webkit-touch-callout: none; /* iOS Safari */
			-webkit-user-select: none; /* Safari */
			user-select: none; /* Non-prefixed version, currently supported by Chrome, Edge, Opera and Firefox */
		}

		.preloader{
			z-index: 20;
		}
		</style></head>
<body id="gateway-game">
@include('northplay::gateway-javascript-global')

<div class="preloader">
<div class="preloader_figure"></div>
    <p class="preloader_label">
    Loading
    </p>
</div>

@if(isset($session_data['storage']['game_bundle_source']))
    @if($session_data["game_provider"] === "ryangames")
        <script src="/game_assets/{{ $session_data["game_identifier"] }}/loader.js?{{ time() }}" crossorigin="anonymous"></script>
        <script type="text/javascript">
        document.write('\x3Cscript src="' + window.__OPTIONS__.game_bundle_source + '" crossorigin="anonymous">\x3C/script>');
        </script>
        <script type="text/javascript">
        window.DSN = ""; window.__DSN__ = "";
    @else
      <script src="https://cdn.bgaming-network.com/html/{{ $session_data["storage"]["identifier"] }}/loader.js?{{ time() }}" crossorigin="anonymous"></script>
      <script type="text/javascript">
      document.write('\x3Cscript src="' + window.__OPTIONS__.game_bundle_source + '" crossorigin="anonymous">\x3C/script>');
      </script>
      <script type="text/javascript">
      window.DSN = ""; window.__DSN__ = "";
      </script>
    @endif
  @else
  <script src="https://cdn.bgaming-network.com/html/casino.min.js" crossorigin="anonymous"></script>
  <script type="text/javascript">
  window['Casino'] && Casino.init(window.__OPTIONS__)
</script>
@endif

@includeWhen($gateway_session_id, 'northplay::gateway-javascript-session-closer', ['gateway_session_id' => $gateway_session_id])
</body>
</html>