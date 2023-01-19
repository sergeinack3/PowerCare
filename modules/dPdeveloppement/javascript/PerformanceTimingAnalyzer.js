/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PerformanceTimingAnalyzer = {
  analyze: function (form) {
    var input = form.elements.export;
    var file = input.files[0];

    if (file.name.indexOf(".json") === -1) {
      alert("Veuillez choisir un fichier JSON");
      return false;
    }

    var reader = new FileReader();
    reader.readAsText(file);

    reader.onloadend = function (e) {
      var blob = e.target.result;
      var report = blob.evalJSON();
      PerformanceTimingAnalyzer.loadReport(report);
    };

    return false;
  },

  loadReport: function (report) {
    var container = $('profiling-report');

    container.update();
    container.insert(DOM.h2({},
      DOM.small({style: "float: right;"},
        "v. " + report.version,
        " - ",
        Date.fromDATETIME(report.date).toLocaleDateTime(),
        " - ",
        report.platform,
        " - ",
        "<em>" + report.userAgent + "</em>"
      ),
      report.label
    ));

    var table = DOM.table({
      className: "main tbl"
    });
    table.insert(DOM.tr({},
      DOM.th({className: "narrow"}, "Date"),
      DOM.th({className: "narrow"}),
      DOM.th({className: "narrow"}, "n"),
      DOM.th({}, "Page"),
      DOM.th({}, "Module"),
      DOM.th({}, "Script")
    ));
    report.pages.each(function (page) {
      if (!page.timeline.length) {
        return;
      }

      var navigation = {
        navigate: ["style/mediboard_ext/images/buttons/link.png", "Navigation"],
        reload: ["style/mediboard_ext/images/buttons/change.png", "Rafraîchissement"],
        back_forward: ["style/mediboard_ext/images/buttons/back-forward.png", "Retour/avanc."]
      };

      var nav = null;
      var date = new Date();
      date.setTime(page.time);

      var navType = page.timeline[0].pageInfo.navigation;
      if (navType !== undefined) {
        nav = navigation[navType];
      }

      var item = DOM.tr({},
        DOM.td({}, date.toLocaleDateTime()),
        DOM.td({}, nav ? DOM.img({src: nav[0], title: nav[1]}) : ""),
        DOM.td({}, page.timeline.length),
        DOM.td({style: "text-align: right;"},
          DOM.span({title: page.view.m}, $T("module-" + page.view.m + "-court"))
        ),
        DOM.td({},
          DOM.a({
            href: "#0",
            title: page.view.a,
            style: "font-weight: bold;"
          }, $T("mod-" + page.view.m + "-tab-" + page.view.a)).observe("click", function (event) {
            Event.stop(event);
            PerformanceTimingAnalyzer.showTimingDetails(page.timeline, report.label);
          })
        ),
        DOM.td({className: "compact", title: page.url}, page.url.truncate(90))
      );

      table.insert(item);
    });

    container.insert(table);
  },

  showTimingDetails: function(timeline) {
    timeline = timeline || MbPerformance.timeline;
    
    var container = DOM.div({className: "profiling-timeline"});
    $$("body")[0].insert(container);

    PerformanceTimingAnalyzer.buildTimingDetails(container, timeline, function(tl) {
      Modal.open(container, {
        showClose: true,
        width: -10,
        height: -10
      });
    });
  },
  
  buildTimingDetails: function(container, timeline, callback){
    App.loadJS(['lib/visjs/vis'], function(vis) {
      PerformanceTimingAnalyzer.currentTimeline = timeline || PerformanceTimingAnalyzer.currentTimeline;

      var legendTitles = {
        "bar-network": "Réseau",
        "bar-server": "Serveur",
        "bar-client": "Navigateur",
        "bar-type-session": "Session",
        "bar-mem": "Mémoire serveur"
      };
      var legend = "<div class='legend'>";
      $H(legendTitles).each(function (pair) {
        legend += "<div class='legend-item'><div class='bar " + pair.key + "'></div> " + pair.value + "</div> ";
      });
      legend += "</div>";
      
      container.insert(legend);

      var navStart;
      var groups = [];
      var items = [];

      // Draw each bar
      PerformanceTimingAnalyzer.currentTimeline.each(function (d, i) {
        var perfTiming = d.perfTiming;
        var perfOffset;
        var serverTiming = d.serverTiming;
        var serverOffset;

        groups.push({
          id: "group-" + i,
          content: "<span style='float: right;' class='compact'>#{size} Kio, DB: #{db} ms</span><strong>#{m}</strong><br /><span class='compact'>#{a}</span>".interpolate({
            size: (serverTiming.size / 1024).toFixed(2),
            db: (serverTiming.db * 1000).toFixed(2),
            m: $T("module-" + d.pageInfo.m + "-court"),
            a: $T("mod-" + d.pageInfo.m + "-tab-" + d.pageInfo.a)
          }),
          title: d.pageInfo.a
        });

        if (d.type === "page") {
          perfOffset = perfTiming.navigationStart;
          serverOffset = perfTiming.navigationStart;
          navStart = perfTiming.navigationStart;
        }
        else {
          perfOffset = 0;
          serverOffset = navStart;
        }

        Object.keys(MbPerformance.markingTypes).each(function (type) {
          PerformanceTimingAnalyzer.drawBar(items, "group-" + i, type, perfTiming, perfOffset, serverTiming, serverOffset);
        });
      });
      
      var options = {
        min: 0,
        end: 10000,
        verticalScroll: true,
        stack: false,
        margin: 0,
        showCurrentTime: false,
        format: {
          minorLabels: {
            millisecond:'x',
            second:     'x',
            minute:     'x',
            hour:       'x',
            weekday:    'x',
            day:        'x',
            month:      'x',
            year:       'x'
          },
          majorLabels: {
            millisecond:'mm:ss',
            second:     'mm:ss',
            minute:     'mm:ss',
            hour:       'mm:ss',
            weekday:    'mm:ss',
            day:        'mm:ss',
            month:      'mm:ss',
            year:       ''
          }
        }
      };
      
      var div = DOM.div({className: "profiling-timeline-container"});
      container.insert(div);

      callback(new vis.Timeline(div, new vis.DataSet(items), new vis.DataSet(groups), options));
    });
  },

  drawBar: function(items, group, type, perfTiming, perfOffset, serverTiming, serverOffset){
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

      var title = t.label+"\n"+t.desc+"\nDébut: "+Math.round(start)+"ms\nDurée: "+Math.round(length)+"ms";

      items.push({
        group: group,
        title: title,
        start: start,
        end: start + length,
        className: "bar bar-"+t.resp+" bar-type-"+type+(t.sub ? " sub" : "")
      });

      if (t.getMemory) {
        var mem = t.getMemory(serverTiming, perfTiming);
        
        var memMB = mem / 1024 / 1024;
        var memoryThreshold = 64;
        
        // 100 == green, 0 = red in HSL
        var color = 100 - Math.min(100, Math.round(100 * (memMB / memoryThreshold)));
        
        items.push({
          group: group,
          title: t.label+"\n"+t.desc+"\n"+Number(mem/1024).toLocaleString()+" Kio",
          start: start,
          end: start + length,
          className: "bar bar-mem",
          style: "background: hsl("+color+", 100%, 50%);"
        });
      }
    }
  }
};
