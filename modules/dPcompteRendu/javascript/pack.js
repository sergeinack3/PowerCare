/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Pack = {
  edit: function(pack_id) {
    Form.onSubmitComplete = pack_id == "0" ? Pack.onSubmitComplete : Prototype.emptyFunction;

    var url = new Url("compteRendu", "ajax_edit_pack");
    url.addParam("pack_id", pack_id);
    url.requestModal(600, {onClose: Pack.refreshList});
  },
  
  onSubmitComplete: function (guid, properties) {
    Control.Modal.close();
    var id = guid.split("-")[1];
    Pack.edit(id);
  },

  onSubmit: function(form) {
    return onSubmitFormAjax(form)
  },

  confirmDeletion: function(form) {
    var options = {
      typeName: "Pack ",
      objName: $V(form.nom)
    };

    confirmDeletion(form, options, Control.Modal.close);
  },

  filter: function() {
    Pack.refreshList();
    return false;
  },

  onSubmitModele: function(form) {
   return onSubmitFormAjax(form, function() {
     Pack.refreshListModeles();
     $V(form.modele_id, "", false);
   });
  },

  refreshList: function() {
    var form = getForm("Filter");
    var url = new Url("compteRendu", "ajax_list_pack");
    url.addFormData(form);
    url.requestUpdate("list-packs");
  },

  refreshListModeles: function() {
    var form = getForm("Edit-CPack");
    var url = new Url("compteRendu", "ajax_list_modeles_links");
    url.addElement(form.pack_id);
    url.requestUpdate("list-modeles-links");
  },

  refreshFormModeles: function() {
    var form = getForm("Edit-CPack");

    // Nothing on creation
    if ($V(form.pack_id).empty()) {
      return;
    }

    // Request
    var url = new Url("compteRendu","ajax_form_modeles_links");
    url.addParam("filter_class", $V(form.object_class));
    url.addParam("object_guid", Pack.makeGuid(form));
    url.addParam("pack_id", $V(form.pack_id));
    url.requestUpdate("form-modeles-links");
  },
  
  makeGuid: function(form) {
    var object_guid = 'instance';

    if (form.user_id     && $V(form.user_id    ) != "") object_guid = "CMediUsers-" + $V(form.user_id    );
    if (form.function_id && $V(form.function_id) != "") object_guid = "CFunctions-" + $V(form.function_id);
    if (form.group_id    && $V(form.group_id   ) != "") object_guid = "CGroups-"    + $V(form.group_id   );

    return object_guid;
  },

  changeClass: function(input) {
    Pack.refreshFormModeles(input.value, Pack.makeGuid(input.form));
  },

  toggleFusion: function() {
    var els = getForm("Edit-CPack").elements;
    if (els.merge_docs.value == 1) {
      els.category_id.value = '';
    }
    $('CPack_category_id').toggle();
  },

  /**
   * Choisir les documents par défaut dans un pack
   *
   * @param value
   */
  chooseDocument: function(value) {
    if (value == true) {
      $('selected_doc').style.display = "";
      $$('td.selected_doc').each(function (elt) {
        elt.style.display = "";
      })
    }
    else{
      $('selected_doc').style.display = "none";
        $$('td.selected_doc').each(function (elt) {
          elt.style.display = "none";
        })
    }
  },

  /**
   * Modifier le champ is_eligible_selection_document lors de la sélection de fast_edit ou merge_docs
   *
   * @param form
   */
  changeTypeEligibleDocument: function (form) {
    if (form.fast_edit.value == 1 || form.merge_docs.value == 1) {
      $V(form.is_eligible_selection_document ,"0");
      $('tr_eligible').style.display = "none";
    }
    else{
      $('tr_eligible').style.display = "";
    }
  }
};



