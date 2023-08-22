<div id="visitor"></div>
<script type="text/javascript">
    
  // Initialize the agent at application startup.
  const fpPromise = import('/northplay/oppa.js')
    .then(FingerprintJS => FingerprintJS.load())

  // Get the visitor identifier when you need it.
  fpPromise
    .then(fp => fp.get())
    .then(result => {
      // This is the visitor identifier:
      const visitorId = result.visitorId;
      document.getElementById('visitor').innerHTML = visitorId;
      console.log(visitorId)
    })
    .catch(error => console.error(error))
</script>