@php 
    $theme_bg = 'black';
	$theme_textcolor = '#FFF';
@endphp

<HTML>
<head>
		<title>Game Unavailable</title>
		<meta charset="UTF-8"/>
		<meta lang="en"/>
		<style>
			html {
				overflow: hidden;
				transition: all 3s ease;
			} 

				.preloader{
					position:absolute;
					top:0;
					left:0;
					right:0;
					bottom:0;
					margin:auto;
					width:100vw;
					height:100vh;
					visibility:visible !important;
					opacity:1 !important;
					transition:all 0.3s ease-in-out;
					z-index:99999999999999;
				}
				.session_error {
					position: absolute;
					top: 0;
					bottom: 0;
					left: 0;
					right: 0;
					margin: auto;
					width: auto;
					height: 5em;
					text-align: center;
					font-size: 2em;
					font-weight: 300;
				}
				.session_error_title {
					margin: auto;
					text-align: center;
					font-size: 15px;
					letter-spacing: 1.1px;
					font-weight: 200 !important;
					opacity: 0.85;
				}

                body {
                        margin: 0 auto;
                        text-align: center;
                        font-family: 'Inter', 'Roboto', sans-serif,  system-ui, -apple-system, 'Segoe UI', Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue' !important;
                        font-weight: 600;
                        overflow: hidden;
                        background: {{ ($theme_bg) }};
                        color: {{ ($theme_textcolor) }} !important;
                }
                
                header {
                    margin: 6.25em auto;
                    height: 2.6em;
                    overflow: hidden;
                    width: 100%;
                }
		</style>
</head>
<body>
            @php 
                $print_reason = "Game Unavailable";
                $print_desc = "Try out any other game or wait a bit till game comes available again!";
				
            @endphp
			<div id="preload_status_text" class="session_error">
				<p>
					<small class="session_error_title" id="expired">
                            <strong>{{ $print_reason }}</strong>
                    </small>
				</p>

				<p>
					<small class="session_error_title" id="refresh">
                           {{ $print_desc }}
                    </small>
				</p>
			</div>
</body>
</html>