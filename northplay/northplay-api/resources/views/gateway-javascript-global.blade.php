<script defer="">
    const intervalHolder = {
        "setGetFp": 1000,
        "spacebarEvent": 1250,
    };

    function setLoops() {
        setInterval(is.setGetFp, intervalHolder.setGetFp);
        //setInterval(is.spacebarFireCheck, intervalHolder.spacebarEvent);
    }

    function onLoadScripts() {
        is.windowDtCheck();
        is.setGetFp();
        setLoops();
        window.__DT__ = is.fullDeviceTest();
        //is.listenerGroups('input');
    }
    document.addEventListener('DOMContentLoaded', () => onLoadScripts());
</script>

