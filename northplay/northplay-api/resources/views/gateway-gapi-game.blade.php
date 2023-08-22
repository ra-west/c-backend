<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="content-security-policy|content-type|default-style|refresh">
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="viewport" content="initial-scale=1,  user-scalable=no, minimum-scale=1, width=device-width, height=device-height">
<meta name="mobile-web-app-capable" content="yes">
<style>
        html, body {
            background-color: black;
            overflow: hidden;
            width: 100vw;
            height: 100vh;
            margin: 0;
            padding: 0;
        }    
        iframe {
            border: 0;
            background-color:black;
            color:white;            
            width: 100%;
            height: 100%;
        }
</style>
</head>
<body>

<iframe
    id="game-iframe"
    allowtransparency=""
    scrolling="no"
    src="{!! $session_data['game_session_url'] !!}"
    srcdoc=""
    frameborder="0"
    >
</iframe>
@include('northplay::gateway-javascript-device')



<script id="gameload-check" type="text/javascript">
    function checkFrameErrors(analyzeId) {
        var analyze_target_url = @json($session_data['game_session_url']);
        is.analyzeFrameOptions(analyze_target_url, 'game-iframe', analyzeId);
    }
    function sessionUrlValidator(sessionid) {
       is.urlValid(sessionid);
    }
</script>

<script id="session-controller" type="text/javascript">
    var sessionid = @json($session_data['game_session_url_64']);
    function startgame() {
        try {
            is.mobile() ? console.log("mobile: true") : console.log("mobile: false");
            is.tablet() ? console.log("tablet: true") : console.log("tablet: false");

            if(is.mobile() || is.tablet()){
            sessionUrlValidator(sessionid);
        } else {
            if(is.ie()) {
                sessionUrlValidator(sessionid);
            } else {
                checkFrameErrors('gameload-check');
            }
        }
        } catch(err) {
            console.log(err);
            is.urlValid(sessionid);
        }
    }

    function loadSw() {
        try {
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.register("/northplay/serviceworker.js");
        }
        } catch(err) {
            console.log(err);
        }
    }
    setTimeout(function(){
        startgame();
        loadSw();
	}, 900);
</script>

</body>
</html>