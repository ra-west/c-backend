//#//#// The name of the cache your app uses.
//#//#const CACHE_NAME = "my-app-cache";
//#//#// The list of static files your app needs to start.
//#//#// Note the offline page in this list.
//#//#const PRE_CACHED_RESOURCES = ["/", "styles.css", "app.js", "/offline"];
//#//#
//#//#// Listen to the `install` event.
//#//#self.addEventListener("install", event => {
//#//#  async function preCacheResources() {
//#//#    // Open the app's cache.
//#//#    const cache = await caches.open(CACHE_NAME);
//#//#    // Cache all static resources.
//#//#    cache.addAll(PRE_CACHED_RESOURCES);
//#//#  }
//#//#
//#//#  event.waitUntil(preCacheResources());
//#//#});
//#//#
//#//#self.addEventListener("fetch", event => {
//#//#  async function navigateOrDisplayOfflinePage() {
//#//#    try {
//#//#      // Try to load the page from the network.
//#//#      const networkResponse = await fetch(event.request);
//#//#      console.log(networkResponse);
//#//#      return networkResponse;
//#//#    } catch (error) {
//#//#      // The network call failed, the device is offline.
//#//#      const cache = await caches.open(CACHE_NAME);
//#//#      const cachedResponse = await cache.match("/offline");
//#//#      return cachedResponse;
//#//#    }
//#//#  }
//#//#
//#//#  // Only call event.respondWith() if this is a navigation request
//#//#  // for an HTML page.
//#//#  if (event.request.mode === 'navigate') {
//#//#    event.respondWith(navigateOrDisplayOfflinePage());
//#//#  }
//#//#});
//#//#
//#//#// Listen to the `activate` event to clear old caches.
//#//#self.addEventListener("activate", event => {
//#//#  async function deleteOldCaches() {
//#//#    // List all caches by their names.
//#//#    const names = await caches.keys();
//#//#    await Promise.all(names.map(name => {
//#//#      if (name !== CACHE_NAME) {
//#//#        // If a cache's name is the current name, delete it.
//#//#        return caches.delete(name);
//#//#      }
//#//#    }));
//#//#  }
//#//#
//#//#  event.waitUntil(deleteOldCaches());
//#//#});
