/**
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SupervisionGraph = {
  currentGraphId: null,

  list: function (callback) {
    new Url("monitoringPatient", "ajax_list_supervision")
      .requestUpdate("supervision-list", callback ? callback : Prototype.emptyFunction);
  },

  editGraph: function (id) {
    new Url("monitoringPatient", "ajax_edit_supervision_graph")
      .addParam("supervision_graph_id", id)
      .requestUpdate("supervision-graph-editor");
    return false;
  },

  callbackEditGraph: function (id) {
    SupervisionGraph.list(SupervisionGraph.editGraph.curry(id));
  },

  // Axes
  listAxes: function (graph_id) {
    SupervisionGraph.currentGraphId = graph_id;

    new Url("monitoringPatient", "ajax_list_supervision_graph_axes")
      .addParam("supervision_graph_id", graph_id)
      .requestUpdate("supervision-graph-axes-list");

    SupervisionGraph.preview(SupervisionGraph.currentGraphId);
  },

  editAxis: function (id, graph_id) {
    var url = new Url("monitoringPatient", "ajax_edit_supervision_graph_axis");
    url.addParam("supervision_graph_axis_id", id);

    if (graph_id) {
      SupervisionGraph.currentGraphId = graph_id;
      url.addParam("supervision_graph_id", graph_id);
    }

    url.requestUpdate("supervision-graph-axis-editor");

    return false;
  },

  callbackEditAxis: function (id, obj) {
    SupervisionGraph.listAxes(obj.supervision_graph_id);
    SupervisionGraph.editAxis(id, obj.supervision_graph_id);
    SupervisionGraph.preview(SupervisionGraph.currentGraphId);
  },

  editTimedData: function (id) {
    new Url("monitoringPatient", "ajax_edit_supervision_timed_data")
      .addParam("supervision_timed_data_id", id)
      .requestUpdate("supervision-graph-editor");

    return false;
  },

  callbackEditTimedData: function (id) {
    SupervisionGraph.list(SupervisionGraph.editTimedData.curry(id));
  },

  editTimedPicture: function (id) {
    new Url("monitoringPatient", "ajax_edit_supervision_timed_picture")
      .addParam("supervision_timed_picture_id", id)
      .requestUpdate("supervision-graph-editor");

    return false;
  },

  callbackEditTimedPicture: function (id) {
    SupervisionGraph.list(SupervisionGraph.editTimedPicture.curry(id));
  },

  editInstantData: function (id) {
    new Url("monitoringPatient", "ajax_edit_supervision_instant_data")
      .addParam("supervision_instant_data_id", id)
      .requestUpdate("supervision-graph-editor");

    return false;
  },

  callbackEditInstantData: function (id) {
    SupervisionGraph.list(SupervisionGraph.editInstantData.curry(id));
  },

  // Series
  listSeries: function (axis_id) {
    new Url("monitoringPatient", "ajax_list_supervision_graph_series")
      .addParam("supervision_graph_axis_id", axis_id)
      .requestUpdate("supervision-graph-series-list");
  },

  editSeries:         function (id, axis_id) {
    new Url("monitoringPatient", "ajax_edit_supervision_graph_series")
      .addParam("supervision_graph_series_id", id)
      .addNotNullParam("supervision_graph_axis_id", axis_id)
      .requestModal(500, 530);

    return false;
  },
  callbackEditSeries: function (id, obj) {
    SupervisionGraph.listSeries(obj.supervision_graph_axis_id);
    SupervisionGraph.listAxes(SupervisionGraph.currentGraphId);
    Control.Modal.close();
  },

  // Axis labels
  listAxisLabels: function (axis_id) {
    new Url("monitoringPatient", "ajax_list_supervision_graph_axis_labels")
      .addParam("supervision_graph_axis_id", axis_id)
      .requestUpdate("supervision-graph-axis-labels-list");
  },

  editAxisLabel: function (id, axis_id) {
    new Url("monitoringPatient", "ajax_edit_supervision_graph_axis_label")
      .addParam("supervision_graph_axis_label_id", id)
      .addNotNullParam("supervision_graph_axis_id", axis_id)
      .requestModal(400, 300);

    return false;
  },

  callbackAxisLabel: function (id, obj) {
    SupervisionGraph.listAxisLabels(obj.supervision_graph_axis_id);
    Control.Modal.close();
  },

  preview: function (graph_id) {
    if (!graph_id) {
      return;
    }

    new Url("monitoringPatient", "ajax_preview_supervision_graph")
      .addParam("supervision_graph_id", graph_id)
      .requestUpdate("supervision-graph-preview");
  },

  editTable: function (id) {
    new Url("monitoringPatient", "ajax_edit_supervision_table")
      .addParam("supervision_table_id", id)
      .requestUpdate("supervision-graph-editor");
    return false;
  },

  callbackEditTable: function (id) {
    SupervisionGraph.list(SupervisionGraph.editTable.curry(id));
  },

  listTableRows: function (table_id) {
    new Url("monitoringPatient", "ajax_list_supervision_table_rows")
      .addParam("supervision_table_id", table_id)
      .requestUpdate("supervision-table-rows-list");
  },

  editTableRow: function (row_id, table_id) {
    var url = new Url("monitoringPatient", "ajax_edit_supervision_table_row");
    url.addParam("supervision_table_row_id", row_id);

    if (table_id) {
      url.addParam("supervision_table_id", table_id);
    }

    url.requestUpdate("supervision-table-row-editor");

    return false;
  },

  callbackEditTableRow: function (id, obj) {
    SupervisionGraph.listTableRows(obj.supervision_table_id);
    SupervisionGraph.editTableRow(id);
  },

  editPack: function (id) {
    new Url("monitoringPatient", "ajax_edit_supervision_graph_pack")
      .addParam("supervision_graph_pack_id", id)
      .requestUpdate("supervision-graph-editor");
    return false;
  },

  editPackTimings: function () {
    $$("#edit-timing-fields input.color").each(function (e) {
      e.colorPicker({
        change: function (color) {
          var form = this.form;
          var fieldsElement = form.timing_fields;
          var fields = fieldsElement.value ? fieldsElement.value.evalJSON() : {};

          if (color) {
            fields[this.get("timing")] = color.toHexString();
          } else {
            delete fields[this.get("timing")];
          }

          fieldsElement.value = Object.toJSON(fields);
        }.bind(e)
      });
    });

    Modal.open("edit-timing-fields", {
      showClose: true, width: 620, height: 350
    });
  },

  callbackEditPack: function (id) {
    SupervisionGraph.list(SupervisionGraph.editPack.curry(id));
  },

  editGraphToPack: function (id, pack_id, graph_class) {
    new Url("monitoringPatient", "ajax_edit_supervision_graph_to_pack")
      .addParam("graph_class", graph_class)
      .addNotNullParam("supervision_graph_to_pack_id", id)
      .addNotNullParam("supervision_graph_pack_id", pack_id)
      .requestModal(400, 400);
    return false;
  },

  listGraphToPack: function (pack_id) {
    if (!pack_id) {
      return;
    }
    new Url("monitoringPatient", "ajax_list_supervision_graph_to_pack")
      .addParam("supervision_graph_pack_id", pack_id)
      .requestUpdate("graph-to-pack-list");
    return false;
  },

  graphToPackCallback: function (id, obj) {
    Control.Modal.close();
    SupervisionGraph.listGraphToPack(obj.pack_id);
  },

  chosePredefinedPicture: function (timed_picture_id) {
    new Url("monitoringPatient", "ajax_vw_supervision_pictures")
      .addParam("timed_picture_id", timed_picture_id)
      .requestModal(400, 400, {
        onClose: SupervisionGraph.editTimedPicture.curry(timed_picture_id)
      });
  },
  /**
   * Add onchange into inputs contexts
   */
  addListenerContexts: function () {
    var form = getForm('edit-supervision-graph-pack');

    form.select("input.set-checkbox[type:checkbox]").each(function (input) {
      input.addEventListener('change', function (elt) {
        SupervisionGraph.selectPreopBox(elt);
      });
    });
  },
  /**
   * Check if the preop checkbox is checked when I selected preop or SSPI checkbox
   *
   * @param element
   */
  selectPreopBox: function (element) {
    var element_value = element.target.value;
    var form = element.target.form;

    if (element_value == 'preop' || element_value == 'sspi') {
      if (element.target.checked) {
        $('show_main_pack').show();
      }
      else {
        $('show_main_pack').hide();
      }
    }
    else {
      $('show_main_pack').hide();
    }
  },
  /**
   * Change elements' rank
   */
  changeRank: function (supervision_graph_to_pack_id, rank, sortable, pack_id) {
    var wish_rank = 0;

    if (sortable) {
      wish_rank = (sortable == 'down') ? parseInt(rank) + 1 : parseInt(rank) - 1;
    }

    new Url('monitoringPatient', 'do_change_rank_graph', 'dosql')
      .addParam('supervision_graph_to_pack_id', supervision_graph_to_pack_id)
      .addParam('rank', rank)
      .addParam('wish_rank', wish_rank)
      .requestUpdate('systemMsg', {onComplete: SupervisionGraph.listGraphToPack.curry(pack_id), method: 'post'});
  },
  /**
   * Show the column field
   *
   * @param element
   */
  showColumn: function (element) {
    var form = element.form;

    if (element.value == 'set') {
      $('column_list').show();
    }
    else {
      $('column_list').hide();
    }
  }
};
