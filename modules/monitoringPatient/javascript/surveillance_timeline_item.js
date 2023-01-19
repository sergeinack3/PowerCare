/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Permet de définir un élément chronologique d'une SurveillanceTimeline (pas des graphiques, qui n'ont pas de wrapper),
 * ceci permet de simplifier le code de l'appel à la bibliothèque Vis.js.
 */
SurveillanceTimelineItem = Class.create({
  stl:          null,
  key:          null,
  container:    null,
  timeline:     null,
  date_element: null,
  /**
   * Constructor
   *
   * @param {vis} vis
   * @param {SurveillanceTimeline} stl
   * @param {String} key
   * @param {Element} container
   * @param {Object} group_data
   * @param {Object} item_data
   * @param {Object} options
   */
  initialize:   function (vis, stl, key, container, group_data, item_data, options) {
    this.stl = stl;
    this.key = key;
    this.container = container;

    var opts = Object.clone(stl.defaultOptions);
    options = Object.extend(opts, options);

    if (!stl.readonly) {
      options.onMove = (function (item, callback) {
        this.eventHandler(item, callback);
      }).bind(this);

      options.onRemove = (function (item, callback) {
        var params = {
          del: 1
        };

        this.eventHandler(item, callback, params);
      }).bind(this);

      // Horaire au déplacement d'une planification
      options.tooltipOnItemUpdateTime = {
        template: (item) => {
          return item.start.toLocaleTimeString('fr-fr', {timeStyle: 'short'}).replace(':', 'h');
        }
      }
    }

    // On regroupe les sous groupes pour que les planifications soient affichées ensemble
    options.stackSubgroups = true;

    options.template = function (item) {
      if (!item.template) {
        return item.content;
      }

      if (item.template === "perf") {
        var initial_height = 100;
        //var main_element = $$('div[data-id=' + item.id + ']')[0]);

        if (item.initial_height) {
          initial_height = item.initial_height;
        }

        var content = "<div style='height: 101%; width: 100%; position: relative;'>";

        if (item.line_id == item.id.split('-')[1]) {
          content += "<div id='"+item.line_id+"' class='initial_debit' style='height:1%; width: 100%; position: absolute; top:"+ initial_height +"%;' title='Débit : "+ item.initial_debit +" ml/h'></div>";
        }

        var display = "display: none;";

        item.data.each(function (d) {
          if (d.value) {
            display = '';
          }

          if (!d.ponctual) {
            content += '<div class="perf" onmouseover="if (SurveillancePerop.show_timings_perop) { SurveillancePerop.show_timings_perop.hide(); }" ' +
              'data-value="#{value}" style="height: #{height}%; width: #{width}%;" title="#{title}"></div>'.interpolate({
                height: d.height,
                width:  d.width * 100,
                title:  d.title,
                value:  d.value
              });
          }
          else {
            content += '<div class="vis-item-content" data-value="#{value}"><span>#{value}  #{unit}</span><br /> (#{voie}) #{content}</div>'.interpolate({
              value:  d.value,
              unit:  d.unit,
              voie:  d.voie_label
            });
          }
        });

        content += "</div>";

        return content;
      }
    };

    var timeline = new vis.Timeline(container, item_data, group_data, options);

    this.adjustUserSelect(timeline);

    SurveillanceTimelineItem.timeline = timeline;

    // Left click
    timeline.on("click", function (e) {
      SurveillanceTimelineItem.timeline = timeline;
      SurveillanceTimelineItem.date_element = e.time;
    });

    timeline.on("doubleClick", function (e) {
      if (e.what !== "background") {
        return;
      }

      var group_perop = e.group;
      var group = SurveillancePerop.decomposeGroupName(e.group);

      if (group_perop != 'CAnesthPerop') {
        if (!group) {
          return;
        }
      }

      var date = e.time;
      var element = Event.element(e.event);
      var container = element.up('.supervision');
      var operation_id = element.up('[data-operation_id]').get("operation_id");
      var current_date = new Date();

      // conditionnal line no administration
      if (element.hasClassName('hatching')) {
        return;
      }

      var groupObject = this.groupsData.get(e.group);
      var element_main = element.up("div.timeline-item");
      var type = container.get('type');

      SurveillanceTimelineItem.date_element = date;

      // Cas des gestes Perop
      if (group_perop === 'CAnesthPerop' && container.get("readonly") !== "1") {
        SurveillancePerop.getGestePeropContextMenu(operation_id, date.toDATETIME(true), null, null, type);
      }

      // Cas des médicaments / element / line mix
      if (group_perop !== 'CAnesthPerop' && container.get("readonly") !== "1") {

        if (date.toDATETIME(true) < SurveillanceTimeline.date_max_adm) {
          SurveillancePerop.editPeropAdministration(operation_id, element.up('.timeline-container-' + type), groupObject.line_guid, null, type, date.toDATETIME(true));
        }
        else {
          alert($T('CAdministration-You cannot administer in the future'));
          return;
        }
      }
    });

    // Eval scripts in content
    if (group_data instanceof Array) {
      group_data.each(
        function (group) {
          if (group.content) {
            group.content.evalScripts.bind(group.content).defer();
          }
        }
      );
    } else if (group_data instanceof vis.DataSet) {
      group_data.forEach(
        function (group) {
          if (group.content) {
            group.content.evalScripts.bind(group.content).defer();
          }
        }
      );
    }

    if (!stl.readonly) {
      prepareForms.curry(container).defer();

      // clic sur un élements
      timeline.on('select', function (properties) {
        var item = properties.event.target.up('.vis-item');

        if (!item) {
          return;
        }
      });
    }

    var touch = (bowser.tablet || bowser.mobile);

    // Don't update contiuously on tablets or mobiles
    if (!touch) {
      timeline.on('rangechange', function (properties) {
        var stl = this.stl;
        stl.start = properties.start.valueOf();
        stl.end = properties.end.valueOf();

        stl.applyOffsets(properties.byUser, timeline);
        stl.updateNowIndicator(true);
      }.bind(this));
    }

    timeline.on('rangechanged', function (properties) {
      var stl = this.stl;
      stl.start = properties.start.valueOf();
      stl.end = properties.end.valueOf();

      stl.applyOffsets(properties.byUser ? 2 : true, timeline);
      stl.updateNowIndicator(true);
    }.bind(this));

    this.timeline = timeline;

    stl.append(this.key, this);
  },

  /**
   * Gestionnaire d'évènement interactifs avec une timeline
   *
   * @param item
   * @param callback
   * @param params
   */
  eventHandler: function (item, callback, params) {
    if (params && params.del) {
      if (!confirm($T('CSupervisionGraphToPack-msg-Are you sure you want to delete this item'))) {
        return;
      }
    }

    var object_id, module_name, controller, key_name, datetime_name;
    var dateTime = Object.isString(item.start) ? item.start : item.start.toDATETIME();
    var datetime_main = dateTime.replace('+', ' ');
    var container = $$('.surveillance-timeline-container')[0];

    // conditionnal line
    if ((item.conditionnel == '1') && ((datetime_main < item.debut_seg) || (datetime_main > item.fin_seg))) {
      alert($T('CPrescriptionLineSegment-msg-You must be within the bounds of the segment for this administration'));

      SurveillancePerop.refreshContainer(container);
      return false;
    }

    if (/pousse-seringue/.match(item.className)) {
      if (params && params.del == 1) {
        alert($T('CPrescriptionLineMix-Administration of other products will be removed'));
      }
      else {
        alert($T('CPrescriptionLineMix-Administration of other products will be moved'))
      }
    }

    if (item.hasOwnProperty('class_group')) {
      var object_class = item.class_group;

      if (!params) {
        object_id = item.set_id;
      } else {
        object_id = item.result_id;
      }

    } else {

      var guid = item.id;
      if (!guid) {
        return;
      }

      var dateTime = Object.isString(item.start) ? item.start : item.start.toDATETIME();
      var parts = guid.split(/-/);
      var object_class = parts[0];
      object_id = parts[1];
    }

    var limit_date_min = container.get('limit_date_min');
    var modified_item = true;

    if (!params && limit_date_min) {
      modified_item = Prescription.checkDateLimit(datetime_main, limit_date_min)
    }

    var callback = callback.curry(item);
    var planification = 0;
    var object_class_planif, prise_id, quantite;

    switch (object_class) {
      case "CAdministration":
        module_name = "prescription";
        controller = "do_administrations_perop";
        key_name = "administration_id";
        datetime_name = "dateTime";
        break;

      case "CAnesthPerop":
        module_name = "salleOp";
        controller = "do_anesth_perop_aed";
        key_name = "anesth_perop_id";
        datetime_name = "datetime";
        break;

      case "CPlanificationSysteme":
        module_name = "planSoins";
        controller = "do_administration_aed";
        var object_parts = item.group.split('-');
        var line_mix_item_guid = null;

        if (object_parts[1] == 'CPrescriptionLineMix') {
          var line_mix_item_guid = item.line_mix_item_guid.split('-');
        }

        key_name = "object_id";
        object_id = !line_mix_item_guid ? object_parts[2] : line_mix_item_guid[1];
        object_class_planif = !line_mix_item_guid ? object_parts[1] : line_mix_item_guid[0];
        prise_id = item.prise_id;

        if (params && params.del && !item.administration_id) {
          quantite = 0;
          params.del = 0;
        }
        else {
          quantite = item.quantite;
          planification = 1;
        }

        datetime_name = "dateTime";
        break;

      case "CSupervisionTimedData":
      case "CSupervisionTimedPicture":
        module_name = "monitoringPatient";

        if (!params) {
          controller = "do_observation_result_set_aed";
          key_name = "observation_result_set_id";
          datetime_name = "datetime";
        } else {
          controller = "do_observation_result_aed";
          key_name = "observation_result_id";
          datetime_name = "datetime";
        }

        break;
    }

    if (module_name && modified_item) {
      callback = (function(container) {
        container.retrieve("timeline").updateChildren();
      }).curry(container);

      var url = new Url(module_name, controller, "dosql");

      url.addParam(key_name, object_id);

      if ((object_class == "CSupervisionTimedData" && !params) || object_class != "CSupervisionTimedData") {
        dateTime = dateTime.replace(/\+/, ' ');
        url.addParam(datetime_name, dateTime);
      }
      
      if (object_class == "CPlanificationSysteme") {
        var original_datetime = item.administration_id ? item.original_dateTime : item.datetime;

        original_datetime = original_datetime.replace(/\+/, ' ');

        url.addParam('object_class', object_class_planif);
        url.addParam('prise_id', prise_id);
        url.addParam('administration_id', item.administration_id);
        url.addParam('quantite', quantite);
        url.addParam('administrateur_id', item.user_id);
        url.addParam('planification', planification);
        url.addParam('original_dateTime', original_datetime);
      }

      if (params) {
        url.mergeParams(params);
      }

      url.requestUpdate(SystemMessage.id, {
        method:     "post",
        onComplete: callback
      });
    }
    else {
      container.retrieve("timeline").updateChildren()
    }
  },

  /**
   * Méthode appelée lors du rechargement invisible en Ajax des données de la timeline
   *
   * @param data
   */
  update: function (data) {
    var c = this.container;
    var tl = this.timeline;

    c.setStyle({height: c.getHeight() + "px"});
    tl.setData(data);

    // Eval scripts in content
    if (data.groups.length > 1) {
      data.groups.each(function (group) {
        if (group.content) {
          group.content.evalScripts.bind(group.content).defer();
        }
      });
    }

    if (!this.stl.readonly) {
      prepareForms.curry(c).defer();
    }

    tl.redraw();

    // delay it because it seems not drawn synchronously
    c.setStyle.delay(1, {height: ""});
  },

  /**
   * Let the user interact with the inputs in the left part of the timeline
   *
   * @param timeline
   */
  adjustUserSelect: (timeline) => {
    /** @type {!HTMLElement} */
    const leftEl = timeline['dom']['leftContainer'];

    leftEl['hammer'].forEach(function(i) {
      i.destroy();
    });

    /** @type {!HTMLElement} */
    const rootEl = timeline['dom']['root'];

    rootEl.style.userSelect = 'auto';

    rootEl['hammer'].forEach(function(i) {
      i.input.element.removeEventListener(
        i.input['evEl'], i.input['domHandler'], false);
    });
  }
});

