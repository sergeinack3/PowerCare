/**
 * @package Mediboard\Includes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 *
 * @param {String} type
 * @param {Object} pageInfo
 * @param {Number} time
 * @param {Number} duration
 * @param {PerformanceResourceTiming,PerformanceTiming} perfTiming
 * @param {Object} serverTiming
 *
 * @constructor
 */
function MbPerformanceTimeEntry(type, pageInfo, time, duration, perfTiming, serverTiming){
  this.type = type;
  this.pageInfo = pageInfo;

  this.time = time;
  this.duration = duration;

  this.perfTiming = perfTiming;
  this.serverTiming = serverTiming;
}

MbPerformance = {
  version: "0.2",

  /** {MbPerformanceTimeEntry[]} timeline */
  timeline: [],
  currentTimeline: null,
  timers: {},
  intervalTimer: null,
  profiling: false,
  pageDetail: null,
  timingSupport: window.performance && window.performance.timing,
  timeOffset: null,
  types: {
    page:   1,
    ajax:   2,
    mark:   3,
    chrono: 4
  },
  responsabilityColors: {
    network: "#0000FF",
    server:  "#00FF00",
    client:  "#FF0000",
    other:   "#999999"
  },
  navigationMap: {
    0: "navigate",
    1: "reload",
    2: "back_forward"
  },
  markingTypes: {
    redirect: {
      color: "rgba(184,125,0,0.2)",
      resp:  "other",
      label: "Redirection",
      desc:  "Temps de la redirection",
      start: "redirectStart",
      end:   "redirectEnd"
    },

    fetch: {
      color: "rgba(255,41,41,0.2)",
      resp:  "client",
      label: "Cache",
      desc:  "Temps de recherche dans le cache",
      start: "fetchStart",
      end:   "domainLookupStart"
    },

    // network request
    networkRequest: {
      color: "rgba(41,144,255,0.2)",
      resp:  "network",
      label: "Requête",
      desc:  "Temps d'initialisation de la connexion en envoi de la requête",
      start: "domainLookupStart",
      end:   "requestStart"
    },
    domainLookup: {
      sub: true,
      color: "rgba(41,144,255,0.2)",
      resp:  "network",
      label: "DNS",
      desc:  "Résolution du nom de domaine (DNS)",
      start: "domainLookupStart",
      end:   "domainLookupEnd"
    },
    connect: {
      sub: true,
      color: "rgba(41,144,255,0.2)",
      resp:  "network",
      label: "Connexion",
      desc:  "Initialisation de la connexion",
      start: "connectStart",
      end:   "connectEnd"
    },
    request: {
      sub: true,
      color: "rgba(41,144,255,0.2)",
      resp:  "network",
      label: "Requête",
      desc:  "Envoi de la requête",
      start: "connectEnd",
      end:   "requestStart"
    },

    // Server
    /*server: {
      color: "rgba(14,168,0,0.2)",
      resp:  "server",
      label: "Serveur",
      desc:  "Temps passé sur le serveur",
      start: "requestStart",
      end:   "responseStart"
    },*/
    handler: {
      color: "rgba(14,168,0,0.2)",
      resp:  "server",
      label: "Serveur",
      desc:  "Temps de la requête serveur",
      getValue: function(serverTiming, perfTiming){
        if (!serverTiming.handlerEnd || !serverTiming.handlerStart) {
          return null;
        }

        return (serverTiming.handlerEnd - serverTiming.handlerStart);
      },
      getStart: function(serverTiming, perfTiming){
        if (!serverTiming.handlerStart) {
          return null;
        }

        return serverTiming.handlerStart;
      }
    },
    handlerInit: {
      sub: true,
      color: "rgba(14,168,0,0.2)",
      resp:  "server",
      label: "Serveur init.",
      desc:  "Initialisation du serveur web",
      getValue: function(serverTiming, perfTiming){
        return serverTiming.start - serverTiming.handlerStart;
      },
      getStart: function(serverTiming, perfTiming){
        return serverTiming.handlerStart;
      }
    },
    frameworkInit: {
      sub: true,
      color: "rgba(14,168,0,0.2)",
      resp:  "server",
      label: "Fx init.",
      desc:  "Initialisation du framework",
      getValue: function(serverTiming, perfTiming){
        return serverTiming.steps.find(function(step){ return step.label === "init"; }).dur;
      },
      getStart: function(serverTiming, perfTiming){
        return serverTiming.steps.find(function(step){ return step.label === "init"; }).time;
      },
      getMemory: function(serverTiming, perfTiming){
        return serverTiming.steps.find(function(step){ return step.label === "init"; }).mem;
      }
    },
    session: {
      sub: true,
      color: "rgba(14,168,0,0.2)",
      resp:  "server",
      label: "Session",
      desc:  "Ouverture et verrou de la session",
      getValue: function(serverTiming, perfTiming){
        return serverTiming.steps.find(function(step){ return step.label === "session"; }).dur;
      },
      getStart: function(serverTiming, perfTiming){
        return serverTiming.steps.find(function(step){ return step.label === "session"; }).time;
      },
      getMemory: function(serverTiming, perfTiming){
        return serverTiming.steps.find(function(step){ return step.label === "session"; }).mem;
      }
    },
    framework: {
      sub: true,
      color: "rgba(14,168,0,0.2)",
      resp:  "server",
      label: "Framework",
      desc:  "Suite du chargement du framework",
      getValue: function(serverTiming, perfTiming){
        var total = 0;
        serverTiming.steps.each(function(step){
          if (["init", "session", "app"].indexOf(step.label) == -1) {
            total += step.dur;
          }
        });
        return total;
      },
      getStart: function(serverTiming, perfTiming){
        var start = 0;
        serverTiming.steps.each(function(step){
          if (step.label == "session") {
            start = step.time + step.dur;
          }
        });
        return start;
      },
      getMemory: function(serverTiming, perfTiming){
        var mem = 0;
        serverTiming.steps.each(function(step){
          if (["init", "session", "app"].indexOf(step.label) == -1) {
            mem = Math.max(mem, step.mem);
          }
        });
        return mem;
      }
    },
    app: {
      sub: true,
      color: "rgba(14,168,0,0.2)",
      resp:  "server",
      label: "App.",
      desc:  "Code applicatif (dépend de la page affichée) et construction de la page",
      getValue: function(serverTiming, perfTiming){
        return serverTiming.steps.find(function(step){ return step.label === "app"; }).dur;
      },
      getStart: function(serverTiming, perfTiming){
        return serverTiming.steps.find(function(step){ return step.label === "app"; }).time;
      },
      getMemory: function(serverTiming, perfTiming){
        return serverTiming.steps.find(function(step){ return step.label === "app"; }).mem;
      }
    },
    output: {
      sub: true,
      color: "rgba(14,168,0,0.2)",
      resp:  "server",
      label: "Sortie",
      desc:  "Sortie texte (output buffer)",
      getValue: function(serverTiming, perfTiming){
        if (!serverTiming.handlerEnd || !serverTiming.handlerStart) {
          return null;
        }

        var serverTime = serverTiming.handlerEnd - serverTiming.handlerStart;
        var total = 0;
        serverTiming.steps.each(function(step){
          total += step.dur;
        });

        return serverTime - total - (serverTiming.start - serverTiming.handlerStart); // (- server init)
      },
      getStart: function(serverTiming, perfTiming){
        var start = 0;
        serverTiming.steps.each(function(step){
          if (step.label == "app") {
            start = step.time + step.dur;
          }
        });
        return start;
      }
    },

    // response
    response: {
      color: "rgba(41,144,255,0.2)",
      resp:  "network",
      label: "Réponse",
      desc:  "Temps de réponse",
      getValue: function(serverTiming, perfTiming){
        return perfTiming.responseEnd - serverTiming.handlerEnd;
      },
      getStart: function(serverTiming, perfTiming){
        return serverTiming.handlerEnd;
      }
    },
    otherInfra: {
      sub: true,
      color: "rgba(41,144,255,0.2)",
      resp:  "network",
      label: "Autre infra",
      desc:  "Autre temps, acheminement de la requête et de la page",
      getValue: function(serverTiming, perfTiming){
        return perfTiming.responseStart - serverTiming.handlerEnd;
      },
      getStart: function(serverTiming, perfTiming){
        return serverTiming.handlerEnd;
      }
    },
    download: {
      sub: true,
      color: "rgba(41,144,255,0.2)",
      resp:  "network",
      label: "Téléchargement",
      desc:  "Temps de téléchargement de la réponse",
      start: "responseStart",
      end:   "responseEnd"
    },

    // client
    dom: {
      color: "rgba(255,41,41,0.2)",
      resp:  "client",
      label: "Page",
      desc:  "Temps de lecture de la page",
      start: "domLoading",
      end:   "domComplete"
    },
    domInit: {
      sub: true,
      color: "rgba(255,41,41,0.2)",
      resp:  "client",
      label: "Init. DOM",
      desc:  "Temps d'initialisation de l'arbre DOM",
      start: "responseEnd",
      end:   "domLoading"
    },
    domLoading: {
      sub: true,
      color: "rgba(255,41,41,0.2)",
      resp:  "client",
      label: "Constr. DOM",
      desc:  "Temps de construction de l'arbre DOM",
      start: "domLoading",
      end:   "domContentLoadedEventStart"
    },
    domContentLoadedEvent: {
      sub: true,
      color: "rgba(255,41,41,0.2)",
      resp:  "client",
      label: "Charg. DOM",
      desc:  "Temps de l'évènement d'exécution des scripts suivant le chargement de l'arbre DOM",
      start: "domContentLoadedEventStart",
      end:   "domComplete"
    },
    loadEvent: {
      sub: true,
      color: "rgba(255,41,41,0.2)",
      resp:  "client",
      label: "Charg. contenu",
      desc:  "Temps de téléchargement des contenus externes (images, etc)",
      start: "domComplete",
      end:   "loadEventEnd"
    }
  },

  addEvent: function(eventName, callback) {
    if (window.addEventListener) {
      window.addEventListener(eventName, callback, false);
    }
    else {
      window.attachEvent("on"+eventName, callback);
    }
  },

  parseServerTiming: function(str) {
    var timing = /D=(\d+) t=(\d+)/.exec(str);
    if (!timing) {
      return null;
    }

    return {
      duration: timing[1] / 1000,
      start:    timing[2] / 1000
    };
  },

  toggleProfiling: function(){
    var cookie =  new CookieJar();
    var profiling = cookie.get("profiling");

    this.profiling = false;

    if (!MbPerformance.timingSupport) {
      alert("Votre navigateur ne permet pas d'activer le profilage de performances.");
      return;
    }

    if (profiling == 1) {
      cookie.put("profiling", 0);
      clearTimeout(this.intervalTimer);
    }
    else {
      if (confirm("Vous allez activer le mode 'profilage de performances' de Mediboard, ce qui peut ralentir Mediboard, voulez-vous continuer ?\n\nUn rechargement de la page sera nécessaire pour afficher le graphique.")) {
        cookie.put("profiling", 1);
      }
    }
  },

  /**
   * Don't use cookiejar as it may not be ready yet
   *
   * @returns {String,null}
   */
  readCookie: function(cookieName) {
    cookieName = cookieName || "mediboard-profiling";
    var value = new RegExp(cookieName+"=([^;]+)").exec(document.cookie);
    if (!value) {
      return null;
    }

    return decodeURI(value[1]);
  },

  init: function(){
    if (!MbPerformance.timingSupport) {
      return;
    }

    // defer, but not with defer() because prototype is not here yet !
    try {
      MbPerformance.profiling = MbPerformance.readCookie() == '"1"';
      MbPerformance.addEvent("load", function(){
        setTimeout(function(){
          MbPerformance.startPlotting();
        }, 1000);
      });
    }
    catch (e) {}
  },

  startPlotting: function(){
    if (MbPerformance.profiling) {
      MbPerformance.plot();

      MbPerformance.addEvent("unload", function(){
        var pages = store.get("profiling-pages") || [];

        pages.push(MbPerformance.getCurrentPageData());

        store.set("profiling-pages", pages);
      });
    }
  },

  append: function(entry){
    this.timeline.push(entry);
  },

  logScriptEvent: function(type, pageInfo, serverTiming, time, duration) {
    var perfTiming, offset = MbPerformance.timeOffset;

    switch (type) {
      case "page":
        perfTiming = {};

        $H(performance.timing).each(function(pair){
          var value = pair.value;
          if (value === 0 || Object.isString(value) || pair.key === "duration") {
            perfTiming[pair.key] = value;
          }
          else {
            perfTiming[pair.key] = value - offset;
          }
        });

        if (performance.navigation) {
          pageInfo.navigation = MbPerformance.navigationMap[performance.navigation.type];
        }
        break;

      case "ajax":
        perfTiming = MbPerformance.searchEntry(pageInfo.id);

        if (perfTiming) {
          duration = perfTiming.duration;
        }
        break;
    }

    var timeEntry = new MbPerformanceTimeEntry(type, pageInfo, time, duration, perfTiming, serverTiming);

    MbPerformance.append(timeEntry);
  },

  searchEntry: function(id) {
    var entries = performance.getEntries();

    if (entries && entries.length) {
      for (var i = 0, l = entries.length; i < l; i++) {
        var entry = entries[i];
        if (entry.initiatorType === "xmlhttprequest" && entry.name.indexOf("__uniqueID=|"+id+"|") > -1) {
          return entry;
        }
      }
    }

    return null;
  },

  mark: function(label){
    //this.append(new MbPerformanceTimeEntry("mark", label, performance.now()));
  },

  getTimelineCSV:  function(timeline){
    var navStart;
    var datum = [];
    var columns = [];

    // Draw each bar
    (timeline || MbPerformance.timeline).each(function(d, key){
      var perfTiming = d.perfTiming;
      var perfOffset;
      var serverTiming = d.serverTiming;
      var serverOffset;
      var data = {
        i: key,
        m: d.pageInfo.m,
        mView: $T("module-"+d.pageInfo.m+"-court"),
        a: d.pageInfo.a,
        aView: $T("mod-"+d.pageInfo.m+"-tab-"+d.pageInfo.a),
        pageSize: (serverTiming.size / 1024).toFixed(2),
        dbTime: (serverTiming.db * 1000).toFixed(2)
      };

      if (columns.length == 0) {
        columns = Object.keys(data);
        Object.keys(MbPerformance.markingTypes).each(function(type) {
          columns.push(type+"_start");
          columns.push(type+"_duration");

          var t = MbPerformance.markingTypes[type];
          if (t.getMemory) {
            columns.push(type+"_memory");
          }
        });
      }

      if (d.type == "page") {
        perfOffset   = perfTiming.navigationStart;
        serverOffset = perfTiming.navigationStart;
        navStart     = perfTiming.navigationStart;
      }
      else {
        perfOffset = 0;
        serverOffset = navStart;
      }

      Object.keys(MbPerformance.markingTypes).each(function(type) {
        MbPerformance.getCSVtiming(type, data, perfTiming, perfOffset, serverTiming, serverOffset);
      });

      datum.push(data);
    });

    return {
      columns: columns,
      data: datum
    };
  },

  getCSV: function(){
    var allPages = MbPerformance.getPagesData();
    var allData = [];
    var columns = null;

    allPages.each(function(page, pageKey){
      var data = MbPerformance.getTimelineCSV(page.timeline);

      if (!columns) {
        columns = data.columns;
        columns.unshift("t");
        columns.unshift("page");
      }

      data.data.each(function(d){
        d.page = pageKey;
        d.t = page.time;

        allData.push(d);
      });
    });

    var finalData = [];
    allData.each(function(row){
      var data = [];
      columns.each(function(colName){
        data.push(row[colName]);
      });

      finalData.push(data.join(";"));
    });

    finalData.unshift(columns.join(";"));

    return finalData.join("\n");
  },

  getCSVtiming: function(type, data, perfTiming, perfOffset, serverTiming, serverOffset){
    var t = MbPerformance.markingTypes[type];

    if (t.sub && perfTiming && (perfTiming[t.end] && perfTiming[t.start] || t.getValue && t.getStart)) {
      var start, length;

      if (t.getValue) {
        start  = t.getStart(serverTiming, perfTiming) - serverOffset;
        length = t.getValue(serverTiming, perfTiming);
      }
      else {
        start  = perfTiming[t.start] - perfOffset;
        length = perfTiming[t.end]   - perfTiming[t.start];
      }

      data[type+"_start"] = start;
      data[type+"_duration"] = length;

      if (t.getMemory) {
        data[type+"_memory"] = t.getMemory(serverTiming, perfTiming);
      }
    }
  },

  plot: function(){
    var profilingToolbar = jQuery("#profiling-toolbar");

    if (!profilingToolbar.size()) {
      profilingToolbar = jQuery('<div id="profiling-toolbar"></div>').hide().appendTo("body");
      
      // Download report
      jQuery('<button class="download" title="Télécharger le rapport au format JSON">JSON</button>').click(function(){
        var label = MbPerformance.getProfilingName();
        if (label) {
          var data = MbPerformance.dump(label);
          if (data) {
            MbPerformance.download(Object.toJSON(data), data.label+".json");
          }
        }
      }).appendTo(profilingToolbar);

      // Download CSV
      jQuery('<button class="download" title="Télécharger le rapport au format CSV">CSV</button>').click(function(){
        var label = MbPerformance.getProfilingName();
        if (label) {
          var data = MbPerformance.getCSV();

          if (data) {
            MbPerformance.download(data, label+".csv");
          }
        }
      }).appendTo(profilingToolbar);

      // Remove report
      jQuery('<button class="trash notext" title="Supprimer le rapport courant"></button>').click(function(){
        MbPerformance.removeProfiling();
      }).appendTo(profilingToolbar);

      // Show help
      jQuery('<button class="help notext" title="Aide"></button>').click(function(){
        MbPerformance.showHelp();
      }).appendTo(profilingToolbar);

      // Show toolbar
      jQuery('<button id="profiler-toggle" class="gantt notext" title="Profilage de performances en cours"></button>').click(function(){
        profilingToolbar.toggle();
      }).appendTo("body");

      // Show toolbar
      jQuery('<div id="profiler-overview"><div class="profiler-buttons"></div><div id="profiler-timebar"></div></div>').appendTo("body");
    }

    var markings = [];
    var overview = jQuery("#profiler-overview");

    var timeBar = [];
    var timeBarTotal = 0;
    var timeBarContainer = jQuery("#profiler-timebar");

    $H(MbPerformance.markingTypes).each(function(pair){
      MbPerformance.addMarking(pair.key, markings);
    });

    // Don't redraw markings bar
    if (!MbPerformance.markingsDrawn) {
      markings.each(function(marking){
        var resp = MbPerformance.responsabilityColors[marking.resp];
        var line = jQuery('<div title="'+marking.desc+'" class="marking '+(marking.sub ? 'sub' : '')+'" style="background: '+marking.color+'; border-color: '+resp+';">'+(marking.sub ? '&nbsp;- ' : '')+marking.label+"<span style='float:right;'>"+Math.round(marking.value)+" ms</span></div>");
        overview.append(line);

        if (!marking.sub) {
          timeBarTotal += marking.value;
          timeBar.push({
            type: marking.resp,
            value: marking.value
          });
        }
      });

      timeBar.each(function(time){
        timeBarContainer.append("<div style='width: "+(100 * (time.value / timeBarTotal))+"%; background-color: "+MbPerformance.responsabilityColors[time.type]+";'></div>");
      });
    }

    MbPerformance.markingsDrawn = true;
  },

  addMarking: function(key, markings){
    var timing = MbPerformance.timeline[0].perfTiming;

    if (!timing) {
      return;
    }

    var ref = timing.navigationStart;
    var type = MbPerformance.markingTypes[key];

    if (timing[type.start] == 0 && !type.getValue) {
      return;
    }

    var marking = {
      label: type.label,
      desc:  type.desc,
      color: type.color,
      resp:  type.resp,
      sub:   type.sub,
      key:   key,
      lineWidth: 1
    };

    if (type.getValue) {
      marking.value = type.getValue(MbPerformance.pageDetail, timing);
      marking.xaxis = {
        from: 0,
        to:   0
      };
    }
    else {
      marking.xaxis = {
        from: timing[type.start] - ref,
        to:   timing[type.end]   - ref
      };
      marking.value = timing[type.end] - timing[type.start];
    }

    markings.push(marking);
  },

  timeStart: function(label) {
    if (!this.profiling) {
      return;
    }

    this.timers[label] = performance.now()/* - MbPerformance.timeOffset*/;
  },

  timeEnd: function(label, ajaxId) {
    if (!this.profiling) {
      return;
    }

    if (!this.timers[label]) {
      return;
    }

    var now = performance.now()/* - MbPerformance.timeOffset*/;
    var time = this.timers[label];
    var timeEntry;

    delete this.timers[label];

    (function(timeEntry, ajaxId){
      if (!ajaxId) {
        timeEntry = MbPerformance.timeline[0];
      }
      else {
        timeEntry = MbPerformance.timeline.find(function(t){
          return t.pageInfo.id == ajaxId;
        });
      }

      if (timeEntry) {
        timeEntry.perfTiming = timeEntry.perfTiming || {};

        if (label == "eval") {
          timeEntry.perfTiming.domLoading = time;
          timeEntry.perfTiming.domContentLoadedEventStart = time;
          timeEntry.perfTiming.domContentLoadedEventEnd = now;
          timeEntry.perfTiming.domComplete = now;
        }
      }
    }).delay(2, timeEntry, ajaxId);
  },

  getProfilingName: function(){
    return prompt("Libellé du profilage", "Profilage du "+(new Date()).toLocaleDateTime());
  },

  dump: function(label){
    var struct = {
      version: MbPerformance.version,
      date: (new Date()).toDATETIME(),
      label: label,
      userAgent: navigator.userAgent,
      platform: navigator.platform,
      screen: window.screen,
      plugins: [],
      pages: []
    };

    if (navigator.plugins) {
      $A(navigator.plugins).each(function(plugin){
        struct.plugins.push({
          name: plugin.name,
          filename: plugin.filename,
          description: plugin.description
        });
      });
    }

    struct.pages = MbPerformance.getPagesData();

    return struct;
  },

  getPagesData: function(){
    var pages = store.get("profiling-pages") || [];
    pages.push(MbPerformance.getCurrentPageData());

    return pages;
  },

  removeProfiling: function(){
    if (confirm("Supprimer le rapport de profilage courant ? Pensez à le télécharger auparavant si vous souhaitez le garder.")) {
      store.remove("profiling-pages");
    }
  },

  showHelp: function(){
    var url = new Url("developpement", "vw_performance_profiling_help");
    url.popup(900, 600, "Aide du profilage de performances");
  },

  getCurrentPageData: function(){
    return {
      timeline: MbPerformance.timeline,
      url: document.location.href,
      time: Date.now(),
      view: {
        m: App.m,
        a: App.tab || App.a
      }
    };
  },

  download: function(data, fileName) {
    if (data == null) {
      return;
    }

    var form = DOM.form({
      target: "_blank",
      action: "?m=system&a=download_data",
      method: "post"
    }, DOM.input({
      type: "hidden",
      name: "m",
      value: "system"
    }), DOM.input({
      type: "hidden",
      name: "a",
      value: "download_data"
    }), DOM.input({
      type: "hidden",
      name: "filename",
      value: fileName
    }), DOM.input({
      type: "hidden",
      name: "data",
      value: data
    }));

    $$("body")[0].insert(form);
    form.submit();
  }
};

if (window.performance && performance.setResourceTimingBufferSize) {
  performance.onresourcetimingbufferfull = function(){
    console.error("Resource timing buffer full");
  };

  performance.setResourceTimingBufferSize(500);
  performance.clearResourceTimings();
}

MbPerformance.init();
