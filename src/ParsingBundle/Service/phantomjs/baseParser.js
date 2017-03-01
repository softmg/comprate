'use strict';
var baseParser = {
  phantom: phantom,
  STATUS_SUCCESS : 'success',
  webpage : require('webpage'),
  system : require('system'),
  fs : require('fs'),
  resourceTimeout: 3000,
  response : {status: false},
  page: null,
  url: null,
  refererUrl: null,
  userAgent: null,
  CookieJar: null,
  numloads: 0,
  isRefererPage: false,
  debug: true,

  init: function () {
    this.page = this.webpage.create();
    this.url = this.system.args[1];
    this.refererUrl = this.system.args[2];
    this.userAgent = this.system.args[3];
    this.CookieJar = this.system.args[4];
    this.postString = this.system.args[5];
    this.page.settings.userAgent = this.userAgent;
    this.page.settings.loadImages = false;

    //this.url = this.parseUrl(this.url);

    this.initCustomHeaders();
    this.checkTimeout();
    this.checkError();
    this.initCookie();

    if (this.refererUrl !== 'no-referer' && this.refererUrl) {
      this.isRefererPage = true;
    }

    this.page.onLoadFinished = this.onLoadFinish;

    /* alex: add ability to POST request */
    if (this.postString) {
      this.page.open(this.url, 'POST', this.postString, this.onLoadPage);
    } else {
      //console.log(this.url);
      this.page.open(this.isRefererPage ? this.refererUrl : this.url, this.onLoadPage);
    }
  },

  initCustomHeaders: function () {
    this.page.customHeaders = {
      "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
      "Accept-Encoding": "deflate, sdch, br",
      "Accept-Language": "ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4",
      "Cache-Control": "max-age=0",
      "Connection": "keep-alive",
      "Upgrade-Insecure-Requests": 1,
      "X-Compress": null
    };
  },

  /* check proxy timeout */
  checkTimeout: function () {
    var self = this;

    this.page.settings.resourceTimeout = this.resourceTimeout; // ms
    this.page.onResourceTimeout = function(request) {
      self.response.status = 'proxytimeout';
      //console.log(JSON.stringify(self.response));
      //this.phantom.exit();
    };
    setTimeout(function() {
      if (!self.numloads) {
        self.response.status = 'proxytimeout';
        //console.log(JSON.stringify(self.response));
        //this.phantom.exit();
      }
    }, this.resourceTimeout);
  },

  /* debug this.phantom errors */
  checkError: function () {
    this.page.onError = function (msg, trace) {
      if (this.debug) {
        console.log(msg);
      }
      var self = this;
      trace.forEach(function (item) {
        if (self.debug) {
          console.log('  ', item.file, ':', item.line);
        }
      });
    };

    this.page.onConsoleMessage = function (msg) {
      if (JSON.parse(msg)){
          console.log(msg);
      }
    };

    phantom.onError = function (msg, trace) {
      var msgStack = ['PHANTOM ERROR: ' + msg];
      if (trace && trace.length) {
        msgStack.push('TRACE:');
        trace.forEach(function (t) {
          msgStack.push(' -> ' + (t.file || t.sourceURL) + ': ' + t.line + (t.function ? ' (in function ' + t.function + ')' : ''));
        });
      }
      if (this.debug) {
        console.error(msgStack.join('\n'));
      }
      phantom.exit(1);
    };
  },

  /* save and read cookie */
  initCookie: function () {
    var self = this;

    this.page.onResourceReceived = function () {
      self.fs.write(self.CookieJar, JSON.stringify(phantom.cookies), "w");
    };

    if (this.fs.isFile(this.CookieJar)) {
      var cookieStr = this.fs.read(this.CookieJar).trim();
      if (cookieStr) {
        Array.prototype.forEach.call(JSON.parse(this.fs.read(this.CookieJar)), function (x) {
          phantom.addCookie(x);
        });
      }
    }
  },

  parseUrl: function (url) {
    url = url.replace(/[\u00A0-\u9999<>\&]/gim, function (i) {
      return '&#' + i.charCodeAt(0) + ';';
    });

    return url;
  },

  onLoadFinish: function (status) {
    this.numloads++;
  },

  onLoadPage: function (status) {
    /* alex: because rewrite this on webpage object*/
    var self = baseParser;

    /* alex: don't understand why */
    //self.page.customHeaders = {};

    self.numloads++;

    if (self.isRefererPage) {
      self.isRefererPage = false;

      self.loadRefererPage(status);
    } else {
      self.loadMainPage(status);
    }
  },

  /* load referer page, inject link to main page and click on it */
  loadRefererPage: function (status) {
    if (status = this.checkResponseStatus(status)) {
      //console.log(this.url);
      //console.log("https://market.yandex.ru/search.xml?text=Противень глубокий из углеродистой стали REGENT inox 32x21x3,5 см 93-CS-EA-2-01");
      //phantom.exit();
      this.page.open(this.url, this.onLoadPage);
    } else {
      this.response.status = status;
      this.response.currentUrl = this.page.url;
      this.response.title = this.page.title;
    }

   // this.page.evaluate(function (href) {
      
      //console.log(JSON.stringify(this.numloads));
      //this.phantom.exit();
      // Inject and Click a Link to our target
      // Create and append the link
      // var link = document.createElement('a');
      // link.setAttribute('href', href);
      // document.body.appendChild(link);
      // // Dispatch Click Event on the link
      // var evt = document.createEvent('MouseEvents');
      // evt.initMouseEvent('click', true, true, window, 1, 1, 1, 1, 1, false, false, false, false, 0, link);
      // link.dispatchEvent(evt);
      //
      // console.log('link click');
   // }, this.url);
  },

  loadMainPage: function (status) {
    if (status === 'success') {
      if (
          this.page.content.indexOf('<div class="header__code">403</div>') != -1 ||
          this.page.content.indexOf('<h1>403 Forbidden</h1>') != -1 ||
          this.page.content.indexOf('Ошибка 404') != -1
      ) {
        status = false;
      }
    }

    if (status) {
      /* inside page evaluate we can not use objects and closures*/
      this.page.evaluate( function(info) {
        var response = {};
        response.status = info.status;
        response.currentUrl = info.url;
        response.title = info.title;
        response.pageContent = document.documentElement.innerHTML;
        response.cookies = info.cookies;

        console.log(JSON.stringify(response));
      }, {
        cookies: this.phantom.cookies,
        url: this.page.url,
        title: this.page.title,
        status: status
      });

    } else {
      this.response.cookies = this.phantom.cookies;
      this.response.url = this.page.url;
      this.response.title = this.page.title;

      console.log(JSON.stringify(this.response));
    }

    this.phantom.exit();
  },

  /* check if response is success */
  checkResponseStatus: function (status) {
    if (status === self.STATUS_SUCCESS) {
      if (
          self.page.content.indexOf('<div class="header__code">403</div>') != -1 ||
          self.page.content.indexOf('<h1>403 Forbidden</h1>') != -1
      ) {
        status = false;
      }
    }

    return status;
  }
};
