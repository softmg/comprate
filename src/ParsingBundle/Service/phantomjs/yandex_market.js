if (phantom.injectJs('baseParser.js')) {
  baseParser.initCustomHeaders = function () {
    this.debug = false;
    this.page.customHeaders = {
      "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
      "Accept-Encoding": "deflate, sdch, br",
      "Accept-Language": "ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4",
      "Cache-Control": "max-age=0",
      "Connection": "keep-alive",
      "Upgrade-Insecure-Requests": 1,
      "X-Compress": null
    };
  };

  baseParser.init();
} else {
  console.log("ERROR to inject baseParser.js");
}

//phantom.exit();