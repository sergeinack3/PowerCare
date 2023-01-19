/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Prestation = {
  sendPresta: function (sejours_ids) {
    new Url("hospi", "ajax_send_presta")
      .addParam("sejours_ids", sejours_ids)
      .requestUpdate("systemMsg");
  },

  sendAllPresta:             function (table_id) {
    var sejours_ids = $(table_id).select("input[name=print_doc]:checked").pluck("value").join(",");

    Prestation.sendPresta(sejours_ids);
  },
  savePrestationIdHospiPref: function (prestation_id, requestOptions) {
    var form = getForm("editPrefPresta");
    $V(form.elements["pref[prestation_id_hospi]"], prestation_id);
    return onSubmitFormAjax(form, requestOptions);
  },


  editPrestation: function (prestation_id, object_class) {
    new Url("hospi", "ajax_edit_prestation")
      .addParam("prestation_id", prestation_id)
      .addParam("object_class", object_class)
      .requestUpdate("edit_prestation");
  },

  refreshList: function (prestation_guid) {
    new Url("hospi", "ajax_list_prestations")
      .addParam("prestation_guid", prestation_guid)
      .requestUpdate("list_prestations");
  },

  afterEditPrestation: function (id, obj) {
    this.editPrestation(id, obj._class);
    this.refreshList(obj._guid)
  },

  editItem: function (item_id, object_class, object_id, rank) {
    var url = new Url("hospi", "ajax_edit_item_prestation")
      .addParam("item_id", item_id);
    if (!Object.isUndefined(object_class) && !Object.isUndefined(object_id)) {
      url.addParam("object_class", object_class)
        .addParam("object_id", object_id);
    }

    if (!Object.isUndefined(rank)) {
      url.addParam("rank", rank);
    }

    url.requestUpdate("edit_item");
  },

  refreshItems: function (object_class, object_id, item_id) {
    new Url("hospi", "ajax_list_items_prestation")
      .addParam("object_class", object_class)
      .addParam("object_id", object_id)
      .addParam("item_id", item_id)
      .requestUpdate("list_items");
  },

  afterEditItem: function (id, obj) {
    this.editItem(id);
    this.refreshItems(obj.object_class, obj.object_id, id);
  },

  updateSelected: function (guid, classname) {
    this.removeSelected(classname);
    $(classname + "_" + guid).addClassName("selected");
  },

  removeSelected: function (classname) {
    var tr = $$("tr." + classname + ".selected")[0];
    if (tr) {
      tr.removeClassName("selected");
    }
  },

  reorderItem: function (item_id_move, direction, prestation_class, prestation_id) {
    var form = getForm("reorderItemPrestation" + item_id_move);
    $V(form.direction, direction);
    onSubmitFormAjax(form, this.refreshItems.curry(prestation_class, prestation_id));
  },

  editSousItem: function (sous_item_id, item_prestation_id) {
    new Url("hospi", "ajax_edit_sous_item")
      .addParam("sous_item_id", sous_item_id)
      .addParam("item_prestation_id", item_prestation_id)
      .requestModal('450px');
  },

  delSousItem: function (sous_item_prestation_id, object_class, object_id, item_id) {
    var form = getForm("delSousItemForm");
    $V(form.sous_item_prestation_id, sous_item_prestation_id);
    onSubmitFormAjax(form, this.refreshItems.curry(object_class, object_id, item_id));
  },

  exportPrestation: function () {
    new Url("hospi", "vw_export_prestation")
      .requestModal();
  },

  importPrestation: function () {
    new Url("hospi", "vw_import_prestation")
      .requestModal('70%', '70%');
  },

  /**
   * Get the right rank when the item's modified
   *
   * @param item_id
   * @param rank
   */
  getRankPrestationItem: function (item_id, rank, position) {
    var form = getForm('edit_item');
    if (item_id && ($V(form.item_prestation_id) == item_id)) {
      if (position == 'up') {
        rank = parseInt(rank) - 1;
      }
      else if (position == 'down') {
        rank = parseInt(rank) + 1;
      }

      console.log(rank);

      $V(form.rank, rank);
    }
  }
};
