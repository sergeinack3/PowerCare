/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

HistoryViewer = {
  loadedPaths: {},
  loadedLogs: {},

  makeSpan: function (objects, object_class, object_id) {
    return DOM.span(
      {
        onmouseover: "ObjectTooltip.createEx(this, '" + object_class + "-" + object_id + "')"
      },
      objects[object_class][object_id]
    )
  },

  displayHistory: function (object_class, object_id) {
    var url = new Url("system", "view_full_history");
    url.addParam("object_class", object_class);
    url.addParam("object_id", object_id);
    url.pop("100%", "100%", "history-"+object_class+"-"+object_id);
  },

  loadHistory: function (path, object_class, object_id) {
    var url = new Url("system", "ajax_full_history");
    url.addParam("object_class", object_class);
    url.addParam("object_id", object_id);
    url.addNotNullParam("path", path);

    var types = {
      "create": "far fa-file-alt",
      "store":  "far fa-edit",
      "merge":  "fas fa-compress",
      "delete": "far fa-trash-alt"
    };

    url.requestJSON(function (obj) {
      var target = $("history-logs");
      //var currentLog = target.firstChild;

      if (obj.history.length == 0) {
        $$("input[value='" + path + "']")[0].up('label').addClassName("opacity-30");
      }

      obj.history.each(function (row) {
        var changes = DOM.table({className: "main layout"});

        if (HistoryViewer.loadedLogs[row.id]) {
          return;
        }

        if (row.changes) {
          $H(row.changes).each(function (pair) {
            var v = pair.value;

            var beforevalue = v.rawbefore;
            if (v.objbefore) {
              beforevalue = HistoryViewer.makeSpan(obj.objects, v.objbefore.class, v.objbefore.id);
            }
            else if (v.viewbefore) {
              beforevalue = DOM.span({title: v.rawbefore}, v.viewbefore);
            }

            var aftervalue = v.rawafter;
            if (v.objafter) {
              aftervalue = HistoryViewer.makeSpan(obj.objects, v.objafter.class, v.objafter.id);
            }
            else if (v.viewafter) {
              aftervalue = DOM.span({title: v.rawafter}, v.viewafter);
            }

            changes.insert(
              DOM.tr({},
                DOM.td({className: "fieldname", title: pair.key}, $T(row.object_class + "-" + pair.key)),
                DOM.td({className: "before"}, beforevalue || DOM.span({className: "opacity-20"}, "&empty;")),
                DOM.td({className: "narrow"}, "&raquo;"),
                DOM.td({className: "after"},  aftervalue  || DOM.span({className: "opacity-20"}, "&empty;"))
              )
            );
          });
        }

        //        while (currentLog && parseInt(currentLog.get("data-log_id")) > parseInt(row.id)) {
        //          currentLog = currentLog.nextSibling;
        //        }

        var newRow = DOM.tr({"data-log_id": row.id, "data-path": path || "", className: "type-" + row.type},
          DOM.td({className: "type-cell"}, DOM.i({className: types[row.type]})),
          DOM.td({}, row.date),
          DOM.td({}, $T(row.object_class)),
          DOM.td({}, HistoryViewer.makeSpan(obj.objects, row.object_class, row.object_id)),
          DOM.td({}, HistoryViewer.makeSpan(obj.objects, 'CMediusers', row.user_id)),
          DOM.td({className: "changes"}, changes)
        );

        target.insert(newRow);

        //        if (currentLog) {
        //          currentLog.insert({
        //            before: newRow
        //          });
        //        }
        //        else {
        //          target.insert(newRow);
        //        }
      });

      var elements = target.childElements().sort(function (a, b) {
        return parseInt(b.get("log_id")) - parseInt(a.get("log_id"));
      });

      target.update();

      elements.each(function (e) {
        target.insert(e);
      });
    });
  }
};
