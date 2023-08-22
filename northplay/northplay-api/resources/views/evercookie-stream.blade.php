<html>

<head>
    <title>init</title>
    <style>
        .flex-container {
        				display: flex;
        				flex-direction: row;
        				background-color: DodgerBlue;
        			}
        
        			.flex-container > div {
        				background-color: #f1f1f1;
        				width: 50%;
        			}
        			.frame-container {
        				width: 100%;
								height: 100%;
								min-height: 500px;
        				overflow: hidden;
        				margin: 10px;
        			}
        
        			.frame-container > iframe {
        				width: 100%;
								height: 100%;
        				overflow: hidden;
        				margin: 5px;
        			}
    </style>
</head>

<body>
    <!-- init -->
    <script type="text/javascript">
        var FPTitle = 'init';
        	var EverTitle = 'init';
        	var FPprev = 'init';
    </script>

    <div class="flex-container">
        <!-- iframe fp -->
        <div class="frame-container" id="fp_holder">
            <iframe onload="start_fp()" id="fp" src={{ $options[ 'fp_iframe_url'] }}></iframe>
        </div>

        <!-- iframe ever -->
        <div  class="frame-container" id="ever_holder">

        </div>

        <!-- event listening -->
        <script defer="">
            var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
            			var eventer = window[eventMethod];
            			var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
            			eventer(messageEvent,function(e) {
            					var key = e.message ? "message" : "data";
            					var data = e[key];
            					console.log(data)
            			},false);
        </script>
    </div>

    <!-- functions -->
    <script defer="">
        function getIframeTitle(value) {
        		// getting our iframe
        		var selectedIframe = document.getElementById(value);
        		// logging the iframe's title
        		if(value === 'fp') {
        			FPTitle = selectedIframe.contentWindow.document.title;
        		} else {
        			EverTitle = selectedIframe.contentWindow.document.title;
        		}
        		document.title = (FPTitle + ':' + EverTitle);
        	}
        	function start_fp() {
        		fp_interval = setInterval(getIframeTitle, 1000, 'fp');
        	}
        	function start_ever() {
        		start_interval = setInterval(getIframeTitle, 1000, 'ever');
        	}
        	function currentTitle() {
        		var currentTitle = (window.document.title);
        		console.log('ever_concat:' + currentTitle);
        	}
        	function fpCheck() {
        		console.log('FPCheck');
        		if(FPTitle !== FPprev) {
        					FPprev = FPTitle;
        					start_ever();
        					clearInterval(start_interval);
        					var everHolder = document.getElementById('ever_holder');
        					everHolder.innerHTML = '';
        					i = document.createElement("iframe");
        					i.id = "ever";
        					i.src = "https://casinoapi.northplay.me/northplay/ever/check?fp=" + FPTitle;
        					everHolder.appendChild(i);
        					start_ever();
        		}
        	}
        	setInterval(fpCheck, 2000);
        	setInterval(currentTitle, 1000);
    </script>

    <pre>```
const MessageHandler = ({ allowedUrl, handleMessage }) => {
useEffect(() => {
	const handleEvent = event => {
		const { message, data, origin } = event;
		if (origin === allowedUrl) {
			handleMessage(message || data);
		}
	};

window.addEventListener('message', handleEvent, false);
	return function cleanup() {
		window.removeEventListener('message', handleEvent);
	};
});

return <React.Fragment />;
};
</pre>
</body>

</html>