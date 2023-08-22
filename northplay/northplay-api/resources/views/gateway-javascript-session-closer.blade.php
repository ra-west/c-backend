<script defer="" type="text/javascript">
    var currentGatewaySessionId = "{{$gateway_session_id}}";
    function session_close_check() {
    var sessionCloseCheckInterval = setInterval(() => {
          if(is.getCookie("gateway_session_close")) {
                    if(is.getCookie("gateway_session_close") === currentGatewaySessionId) {
                        if(is.getCookie("gateway_session_close_reason")) {
                            window.location.replace('/northplay/gw/session-error?reason='+is.getCookie("gateway_session_close_reason"));
                        } else {
                            window.location.replace('/northplay/gw/session-error');
                        }
                    }
                }
        }, 2000);
    };
    session_close_check();
</script>