/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Permet de définir une instance de timeline complète de surveillance.
 * Il correspond au fieldset "Graphique" de l'illustration de la section "Affichage au bloc".
 */
SurveillanceTimeline = Class.create({
  items:             null,
  container:         null,
  readonly:          null,
  type:              null,
  defaultOptions:    null,
  timings:           null,
  /** @var {Integer} */
  timerNowIndicator: null,
  timerCenterNow:    {preop: null, perop: null, sspi: null},
  timingInProgress: true,
  frequency_automatic_graph: 1,
  start:             null,
  defaultStart:      null,
  end:               null,
  defaultEnd:        null,
  fixed:             false,
  print:             0,

  /**
   *
   * @param {HTMLElement} container
   * @param {Boolean}     readonly
   * @param {String}      type
   * @param {Object}      defaultOptions
   * @param {Array}       timings
   * @param {Boolean}     display_current_time
   * @param {Boolean}     print
   * @param {integer}     frequency_automatic_graph
   */
  initialize: function (container, readonly, type, defaultOptions, timings, display_current_time, print, frequency_automatic_graph) {
    this.container = container;
    this.readonly = readonly;
    this.type = type;
    this.defaultOptions = defaultOptions;
    this.timings = timings;
    this.print = print;
    this.items = $H();

    this.start = defaultOptions.start;
    this.defaultStart = defaultOptions.start;
    this.end = defaultOptions.end;
    this.defaultEnd = defaultOptions.end;
    /* False : An operation is completed (Perop) or a wake up room exit (SSPI)
       True : Operation or SSPI in progress*/
    this.timingInProgress = (display_current_time[type] == 0) ? false : true;
    this.display_current_time = display_current_time;
    this.frequency_automatic_graph = frequency_automatic_graph;

    container.store("timeline", this);

    var element_class = '.timeline-action';

    if (this.print == 1) {
      element_class = '.timeline-action-'+ type;
    }

    container.on("click", element_class, function (e) {
      var element = Event.element(e);
      var action = element.get("action");

      switch (action) {
        case "center":
          this.centerAll();
          break;
        case "hide-infos":
          var hide_infos = element.get("hide_infos");
          if (window.reloadSurveillance) {
            window.reloadSurveillance[this.type](hide_infos);
          }
          break;
        case "move-left":
          this.moveAll(0.2);
          break;
        case "move-right":
          this.moveAll(-0.2);
          break;
        case "zoom-in":
          if (!this.fixed) {
            this.zoomAll(-0.2);
          } else {
            alert($T("CSupervisionGraphAxis-msg-Scale is fixed"));
          }
          break;
        case "zoom-out":
          if (!this.fixed) {
            this.zoomAll(0.2);
          } else {
            alert($T("CSupervisionGraphAxis-msg-Scale is fixed"));
          }
          break;
        case "reset":
          this.resetAll();
          break;
      }
    }.bind(this));

    var eventDown = 'mousedown';
    var eventMove = 'mousemove';
    var eventUp = 'mouseup';
    if ('onpointerdown' in window) {
      eventDown = 'pointerdown';
      eventMove = 'pointermove';
      eventUp = 'pointerup';
    }

    if (SurveillanceTimeline._mouseDownHandler) {
      SurveillanceTimeline._mouseDownHandler.stop();
      delete SurveillanceTimeline._mouseDownHandler;
    }

    // Register draggable
    SurveillanceTimeline._mouseDownHandler = document.on(eventDown, ".timeline-draggable", function (event) {
      var draggableElement = Event.element(event);

      if (!draggableElement) {
        return;
      }

      SurveillanceTimeline._draggableElement = draggableElement;

      // Display draggable element
      SurveillanceTimeline._dragHandler = document.on(eventMove, function (event) {
        var target = SurveillancePerop.getTargetGroup(event);

        if (target) {
          var element = target.element;
          var drag = $("timeline-drag");

          if (!drag) {
            drag = DOM.div({id: "timeline-drag"});
          }

          var cell = this.container.down(".vis-foreground .vis-group.timeline-" + SurveillanceTimeline._draggableElement.get("line_guid"));
          cell.insert(drag);

          var pos = SurveillancePerop.getDate(this, event, element);
          var date = pos.date;

          SurveillanceTimeline._draggableElement.date = date;

          drag.show();
          drag.style.right = (pos.width - pos.x) + "px";

          var str = "<strong>#{time}</strong><br />Prévu pour #{orig_datetime}<br />#{quantite} #{unite_prise}".interpolate({
            time:          date.toLocaleTime(),
            orig_datetime: Date.fromDATETIME(draggableElement.get("datetime")).toLocaleTime(),
            quantite:      draggableElement.get("quantite"),
            unite_prise:   draggableElement.get("_unite_prescription")
          });

          drag.update(str);
        }
      }.bind(this));

      // To prevent memory leaks
      if (SurveillanceTimeline._mouseUpHandler) {
        SurveillanceTimeline._mouseUpHandler.stop();
        delete SurveillanceTimeline._mouseUpHandler;
      }

      // Unregister mousemove handler
      SurveillanceTimeline._mouseUpHandler = document.on(eventUp, function (event) {
        // Unregister events
        if (SurveillanceTimeline._dragHandler) {
          SurveillanceTimeline._dragHandler.stop();
          delete SurveillanceTimeline._dragHandler;
        }

        if (SurveillanceTimeline._mouseUpHandler) {
          SurveillanceTimeline._mouseUpHandler.stop();
          delete SurveillanceTimeline._mouseUpHandler;
        }

        var drag = $("timeline-drag");
        if (drag) {
          drag.remove();
        }

        var element = Event.element(event);

        // Mouse up outise of a group, we don't store administration
        if (!element.hasClassName('vis-group') && !element.up('.vis-group')) {
          return;
        }

        var target = SurveillanceTimeline._draggableElement;
        var date = target.date;

        SurveillancePerop.storeAdministrationPerop(
          date.toDATETIME(true),
          target.get("datetime"),
          target.get("quantite"),
          target.get("_unite_prescription"),
          target.get("line_guid"),
          this.type
        );
      }.bind(this));
    }.bind(this));

    if (!window._timelineMouseEvents) {
      window._timelineMouseEvents = true;

      // Hide draggable element
      document.on("mouseout", ".vis-group", function (event) {
        var drag = $("timeline-drag");
        if (drag) {
          drag.hide();
        }
      });

      // mousemove
      document.on('mousemove', '.vis-group', function (event) {
        let element = Event.element(event);
        let element_content = $(element).up('div.vis-content');
        let timeline = SurveillanceTimelineItem.timeline;

        if (element_content.up(".supervision").get("readonly") == 1) {
          return;
        }

        if (timeline) {
          let pos = element_content.cumulativeOffset();
          let width = element_content.getDimensions().width;
          let x = (event.clientX - pos.left);
          let start = timeline.timeAxis.body.range.start;
          let end = timeline.timeAxis.body.range.end;
          let time = start + (end - start) * x / width;
          let date = new Date(time);
          SurveillancePerop.getDateTimeElement(event, element, date.toLocaleDate() + ' ' + date.toTIME(), timeline);

          element_content.down('.vis-foreground').on('mouseout', () => {
            if (SurveillancePerop.show_timings_perop) {
              SurveillancePerop.show_timings_perop.hide();
            }
          });
        }
      });

      document.on('dblclick', '.vis-group', function (event) {
        if (SurveillancePerop.show_timings_perop) {
          SurveillancePerop.show_timings_perop.hide();
        }

        var element = Event.element(event);
        var operation_id = element.up("[data-operation_id]").get("operation_id");

        if (element.up(".supervision").get("readonly") == 1) {
          return;
        }

        var container = element.up('.surveillance-timeline-container');
        var type = container.get('type');

        // Action on item
        var item = SurveillancePerop.getTargetItem(event);
        var target = SurveillancePerop.getTargetGroup(event);
        var element_main = element.up("div.timeline-item");
        var graph_guid = element_main.get('graphguid').split('-')[0];
        var user_id = item ? item.user_id : null;
        var current_user_id = display_current_time['current_user_id'];

        if (item) {
          var period_ok = item.period_ok;

          if ((current_user_id == user_id) && (item.class_group === 'CSupervisionTimedData' || item.class_group === 'CSupervisionTimedPicture')) {
            SurveillancePerop.editObservationResultSet(item.set_id, item.pack_id, item.result_id, container, element_main, type, operation_id);
          }
          if ((current_user_id == user_id) && (item.object_type === 'CAnesthPerop')) {
            SurveillancePerop.editEvenementPerop(item.object_guid, operation_id, null, container, element_main, SurveillanceTimeline.current.type);
          }
          if (item.object_type === 'CPlanificationSysteme' && period_ok) {
            var group = item.group;
            var line_class = group.split("-")[1];
            var line_id = group.split("-")[2];

            SurveillancePerop.editPeropAdministration(operation_id, container, line_class + "-" + line_id, item.id.split("-")[1], type);
          }

          if (target && (target.object_class === "CPrescriptionLineMedicament" || target.object_class === 'CPrescriptionLineMix') && period_ok) {
            var line_id = item.line_id;
            var datetime = item.datetime;
            var group = item.group;
            var line_class = group.split("-")[1];
            var content = item.content_light;
            var quantite = item.quantite;
            var unit = content.split(" ")[1];

            SurveillancePerop.editPeropAdministration(operation_id, container, line_class + "-" + line_id, null, type, datetime, item.id);
          }
        }

        if (!item && (graph_guid.includes('CSupervisionTimedPicture') || graph_guid.includes('CSupervisionTimedData'))) {
          var graph_pack_id = element.up("[data-operation_id]").get("graph_pack_id");
          var date = SurveillanceTimelineItem.date_element;

          SurveillancePerop.createObservationResultSet(date, 'COperation-' + operation_id, graph_pack_id, container, element_main, SurveillanceTimeline.current.type);
        }
      });
    }

    this.initNowIndicator();
    this.insertTimings();
  },

  append: function (key, item) {
    this.items.set(key, item);

    if (item.isGraph && item.scale) {
      var width = item.plot.width();
      var height = item.plot.height();
      var s = item.scale;

      var xRange = s.yaxis_range * (s.time * 60000 / s.value);
      var timeOffset = xRange * (width / height);

      if (timeOffset > 0) {
        var newEnd = this.start + timeOffset;
        if (this.print == 1) {
          newEnd = Math.max(newEnd, this.end);
        }

        this.end = newEnd;
        this.defaultOptions.end = newEnd;
        this.defaultEnd = newEnd;

        this.fixed = true;

        this.applyOffsets(true);

        this.container.select(".timeline-action.zoom-in, .timeline-action.zoom-out")
          .invoke("addClassName", "opacity-50");
      }
    }
  },

  applyOffsets: function (graphsToo, timelineToIgnore) {
    this.items.each(function (pair) {
      var t = pair.value;
      if (t.isGraph) {
        if (timelineToIgnore && timelineToIgnore === t.plot) {
          return;
        }

        var graph = t;

        if (graphsToo) {
          if (!graph.holder || !graph.holder[0] || !graph.holder[0].parentNode || !graph.holder[0].clientWidth) {
            return;
          }

          graph.options.xaxes[0].min = this.start;
          graph.options.xaxes[0].max = this.end;

          if (graphsToo == 2) {
            graph.plot = jQuery.plot(graph.holder, graph.series, graph.options);
          } else {
            var plot = graph.plot;
            var options = plot.getOptions();
            options.xaxes[0].min = this.start;
            options.xaxes[0].max = this.end;

            /* Calling the method jQuery.plot() instead of the methods setupGrid and draw of the object plot don't make the graph disappear and reappear */
            jQuery.plot(graph.holder, graph.series, graph.options);
            /*if (this.print) {
              plot.setData(graph.holder, graph.series, graph.options);
            }*/

            //plot.setupGrid();
            //plot.draw();
          }
        }
      } else {
        if (timelineToIgnore && timelineToIgnore === t.timeline) {
          return;
        }

        t.timeline.setWindow({
          start:     this.start,
          end:       this.end,
          animation: false
        });
      }
    }, this);

    this.updateTimingsOffsets();
  },

  updateOffsetsFromFlot: function (flot) {
    flot.setupGrid();
    flot.draw();

    var xaxis = flot.getXAxes().first();
    if (xaxis) {
      this.start = xaxis.min;
      this.end = xaxis.max;

      this.applyOffsets(true, flot);
    }
  },

  moveAll: function (percentage) {
    this.initNowIndicator();
    this.clearAllIntervals();

    var interval = this.end - this.start;
    this.start -= interval * percentage;
    this.end -= interval * percentage;

    this.applyOffsets(true);
  },

  zoomAll: function (percentage) {
    this.initNowIndicator();
    this.clearAllIntervals();

    var interval = this.end - this.start;
    this.start -= interval * percentage;
    this.end += interval * percentage;

    this.applyOffsets(true);
  },

  resetAll: function (defaultOptions) {
    this.initNowIndicator();
    this.clearAllIntervals();

    this.start = +this.defaultStart;
    this.end = +this.defaultEnd;

    this.applyOffsets(true);
  },

  centerAll: function () {
    this.timingInProgress = true;
    this.initNowIndicator();
    this.clearAllIntervals();

    var min = 60;
    var max = 60;

    if (this.type == 'perop') {
      min = 120;
      max = 120;
    }
    else if (this.type == 'sspi') {
      min = 240;
      max = 240;
    }

    var hour_start = new Date().getTime() - (1 * 60 * min * 1000);
    var date_start = new Date(hour_start);
    var hour_end = new Date().getTime() + (1 * 60 * max * 1000);
    var date_end = new Date(hour_end);

    this.start = +date_start;
    this.end = +date_end;
    this.applyOffsets(true);

    if (this.timingInProgress && this.display_current_time[this.type]) {
      $timeout = this.frequency_automatic_graph * 60000;
      this.timerCenterNow[this.type] = setInterval(this.centerAll.bind(this), $timeout);
    }
  },

  updateNowIndicator: function (forceMove) {
    if (!this.container || !this.container.parentNode || !this.timingInProgress) {
      this.clearAllIntervals();

      if (!this.timingInProgress) {
        this.container.select(".now-indicator").invoke('hide');
      }

      return;
    }

    var nowDate = new Date();
    var now = nowDate.getTime();

    this.container.select(".now-indicator").each(function (e) {
      var wasVisible = e.isVisible();
      var min = this.start;
      var max = this.end;

      if (!forceMove && wasVisible && !this.timingInProgress) {
        // Round to 10 minutes
        var round = Date.minute * 10;
        var offset = (this.end - this.start) / 10;
        var limit = (Math.floor(now / round) * round) + offset;
        if (limit > max) {
          if (this.fixed) {
            this.start += limit - this.end;
            this.end = limit;
          } else {
            this.end = limit;
          }

          max = this.end;
          this.applyOffsets(true);
        }
      }

      if (this.timingInProgress) {
        var pct = (100 * (now - min) / (max - min));
        e.setVisible(pct >= 0 && pct <= 100);
        e.style.paddingLeft = pct + "%";
      }
    }, this);
  },

  updateCurrentTime: function () {
    if (!this.container || !this.container.parentNode) {
      this.clearAllIntervals();
      return;
    }

    this.centerAll();
  },

  initNowIndicator: function () {
    this.timerNowIndicator = setInterval(this.updateNowIndicator.bind(this), 1000); // 1s
  },
  /**
   * Insert the indicators marking bars
   */
  insertTimings: function () {
    if (this.timings) {
      this.timings.each(function (t) {
        this.insertTiming(t);
      }, this);
    }
  },
  /**
   * Create an indicator marking bar for different timings
   *
   * @param timing
   */
  insertTiming: function (timing) {
    if (!timing.value) {
      return;
    }

    var date = Date.fromDATETIME(timing.value);
    var t = date.getTime();
    var cont = this.container.down(".timing-container");
    var bar = DOM.div({
        "data-ts": t,
        className: "timing",
        title:     timing.label
      },
      DOM.span({className: "label"}, timing.label + " (" + date.toLocaleTime().strip() + ")"),
      DOM.div({className: "marking", style: "background:" + timing.color})
    );

    cont.insert(bar);

    var min = this.start;
    var max = this.end;
    var pct = (100 * (t - min) / (max - min));

    bar.setVisible(pct >= 0 && pct <= 100);

    bar.style.paddingLeft = pct + "%";
  },
  /**
   * Update timings to set indicators marking bar
   */
  updateTimingsOffsets: function () {
    this.container.select(".timing-container .timing").each(function (t) {
      var ts = t.get("ts");
      var min = this.start;
      var max = this.end;
      var pct = (100 * (ts - min) / (max - min));

      t.setVisible(pct >= 0 && pct <= 100);
      t.style.paddingLeft = pct + "%";
    }, this);
  },

  updateChildren: function (items) {
    items = items || Object.keys(this.items);

    var c = this.container;
    var operation_id = c.down(".supervision[data-operation_id]").get("operation_id");
    var type = c.down(".supervision[data-type]").get("type");

    var url = new Url("salleOp", "ajax_vw_surveillance_perop_item");
    url.addParam("operation_id", operation_id);
    url.addParam("type", type);
    url.addParam("items", items.join('|'));
    url.requestJSON(function (data) {
      $H(data).each(function (pair) {
        try {
          this.items.get(pair.key).update(pair.value);
        } catch (e) {
          console.error(e);
        }
      }.bind(this));
    }.bind(this));
  },

  updateChildrenSelected: function (items, element_main) {
    items = items || Object.keys(this.items);

    var graph_guid = element_main.get('graphguid');

    var c = this.container;
    var operation_id = c.down(".supervision[data-operation_id]").get("operation_id");
    var type = c.down(".supervision[data-type]").get("type");
    var url = new Url("salleOp", "ajax_vw_surveillance_perop_item");
    url.addParam("operation_id", operation_id);
    url.addParam("type", type);
    url.addParam("items", items.join('|'));
    url.addParam("element_main", element_main);
    url.requestJSON(function (data) {
      $H(data).each(function (pair) {
        try {
          var datas = pair.value;
          if ((element_main && graph_guid == pair.key)) {
            this.items.get(pair.key).update(datas);
          } else if (element_main == null || element_main === undefined) {
            this.items.get(pair.key).update(datas);
          }
        } catch (e) {
          console.error(e);
        }
      }.bind(this));
    }.bind(this));
  },

  updateTimings: function () {
    var c = this.container;
    var operation_id = c.down(".supervision[data-operation_id]").get("operation_id");

    var url = new Url("salleOp", "ajax_vw_surveillance_perop_timings");
    url.addParam("operation_id", operation_id);
    url.requestJSON(function (data) {
      this.timings = data;

      this.container.select(".timing-container .timing").invoke("remove");

      this.insertTimings();
    }.bind(this));
  },

  clearAllIntervals: function () {
   for (var type_timer in this.timerCenterNow) {
      if (this.timerCenterNow[type_timer]) {
        clearInterval(this.timerCenterNow[type_timer]);
      }
    }

    this.timerCenterNow = {preop: null, perop: null, sspi: null};
  }
});

/** {SurveillanceTimeline} current */
SurveillanceTimeline.current = null;

SurveillanceTimeline.registry = SurveillanceTimeline.registry || [];

SurveillanceTimeline.register = function (stl) {
  SurveillanceTimeline.registry.each(function (s, i) {
    if (!s.container || !s.container.parentNode || !s.container.clientWidth) {
      SurveillanceTimeline.registry.splice(i, 1);
    }
  });

  SurveillanceTimeline.registry.push(stl);
};

SurveillanceTimeline.initLocales = function () {
  if (SurveillanceTimeline._localesInitialized) {
    return;
  }

  App.loadJS(['lib/visjs/vis'], function (vis) {
    vis.moment.defineLocale('fr', {
      months:         'janvier_février_mars_avril_mai_juin_juillet_août_septembre_octobre_novembre_décembre'.split('_'),
      monthsShort:    'janv._févr._mars_avr._mai_juin_juil._août_sept._oct._nov._déc.'.split('_'),
      weekdays:       'dimanche_lundi_mardi_mercredi_jeudi_vendredi_samedi'.split('_'),
      weekdaysShort:  'dim._lun._mar._mer._jeu._ven._sam.'.split('_'),
      weekdaysMin:    'Di_Lu_Ma_Me_Je_Ve_Sa'.split('_'),
      longDateFormat: {
        LT:   'HH:mm',
        LTS:  'HH:mm:ss',
        L:    'DD/MM/YYYY',
        LL:   'D MMMM YYYY',
        LLL:  'D MMMM YYYY HH:mm',
        LLLL: 'dddd D MMMM YYYY HH:mm'
      },
      calendar:       {
        sameDay:  '[Aujourd\'hui à] LT',
        nextDay:  '[Demain à] LT',
        nextWeek: 'dddd [à] LT',
        lastDay:  '[Hier à] LT',
        lastWeek: 'dddd [dernier à] LT',
        sameElse: 'L'
      },
      relativeTime:   {
        future: 'dans %s',
        past:   'il y a %s',
        s:      'quelques secondes',
        m:      'une minute',
        mm:     '%d minutes',
        h:      'une heure',
        hh:     '%d heures',
        d:      'un jour',
        dd:     '%d jours',
        M:      'un mois',
        MM:     '%d mois',
        y:      'un an',
        yy:     '%d ans'
      },
      ordinalParse:   /\d{1,2}(er|)/,
      ordinal:        function (number) {
        return number + (number === 1 ? 'er' : '');
      },
      week:           {
        dow: 1, // Monday is the first day of the week.
        doy: 4  // The week that contains Jan 4th is the first week of the year.
      }
    });

    SurveillanceTimeline._localesInitialized = true;
  });
};
