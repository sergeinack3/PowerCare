/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Group = window.Group || {
  form_name: null,
  field_etab_id: null,
  field_etab_view: null,
  field_etab_adresse: null,
  addedit: function (group_id, callback) {
    var url = new Url('etablissement', 'ajax_vw_groups');
    url.addParam('group_id', group_id);

    url.requestModal("50%", "90%", callback);
  },

  viewStructure: function (group_id) {
    var url = new Url('etablissement', 'vw_structure');
    url.addParam('group_id', group_id);
    url.popup(500, 500, 'structure_etab');
  },

  addeditLegalEntity: function (legal_entity_id) {
    var url = new Url('etablissement', 'ajax_vw_legal_entity');
    url.addParam('legal_entity_id', legal_entity_id);
    url.requestModal("50%", "70%");
  },

  editCEtabExterne: function (etab_id, selected) {
    var url = new Url("etablissement", "ajax_etab_externe");
    url.addParam("etab_id", etab_id);
    url.addParam("selected", selected);
    url.requestModal("50%", "70%");
  },

  uploadSaveUID: function (uid) {
    var uploadForm = getForm("upload-import-file-form");

    var url = new Url("etablissement", "ajax_import_group");
    url.addParam("uid", uid);
    url.addParam("service", $V(uploadForm.elements.type_service));
    url.addParam("function", $V(uploadForm.elements.type_function));
    url.addParam("user", $V(uploadForm.elements.type_user));
    url.addParam("bloc", $V(uploadForm.elements.type_bloc));
    url.addParam("salle", $V(uploadForm.elements.type_salle));
    url.addParam("uf", $V(uploadForm.elements.type_uf));
    url.requestUpdate("import-steps");

    uploadForm.down(".upload-ok").show();
    uploadForm.down(".upload-error").hide();
  },
  uploadError: function () {
    var uploadForm = getForm("upload-import-file-form");

    uploadForm.down(".upload-ok").hide();
    uploadForm.down(".upload-error").show();
  },
  uploadReset: function () {
    var uploadForm = getForm("upload-import-file-form");

    uploadForm.down(".upload-ok").hide();
    uploadForm.down(".upload-error").hide();
  },
  /**
   * Change page
   *
   * @param page
   */
  changePage: function(page) {
    var url = new Url("etablissement", "ajax_vw_etab_externe");
    url.addParam('page', page);
    url.requestUpdate("list_etab_externe");
  },
  /**
   * Reload external etablishment list
   *
   * @param page
   */
  reloadListEtabExternes: function(selected) {
    var url = new Url("etablissement", "ajax_vw_etab_externe");
    url.addParam("selected", selected);
    url.requestUpdate("list_etab_externe");
  },
  /**
   * Get external etablishment list with some filters
   *
   * @param form
   */
  listEtabExternes: function (form) {
    var url = new Url("etablissement", "ajax_vw_etab_externe");
    url.addParam('nom'     , $V(form.nom));
    url.addParam('cp'      , $V(form.cp));
    url.addParam('ville'   , $V(form.ville));
    url.addParam('finess'  , $V(form.finess));
    url.addParam('selected', $V(form.selected));
    url.requestUpdate("list_etab_externe");
  },
  /**
   * Reload external etablishment line
   *
   * @param etab_guid
   * @param selected
   */
  reloadEtabExterneLine: function (etab_guid, selected) {
    var etab_id = etab_guid.split("-")[1];

    var url = new Url("etablissement", "ajax_line_etab_externe");
    url.addParam("etab_id", etab_id);
    url.addParam("selected", selected);
    url.requestUpdate(etab_guid + "-row");
  },
  /**
   * Select external etablishment
   *
   * @param element
   */
  selectEtabExterne: function (element) {
    var form              = document[Group.form_name];
    var etab_id           = element.get('id');
    var etab_nom          = element.get('nom');
    var adresse           = element.get('adresse');
    var adresse_complet   = element.get('adresse_complet');
    var dest_address_etab = $("dest_address_etab");

    $V(form[Group.field_etab_id]  , etab_id);
    $V(form[Group.field_etab_view], etab_nom);

    // Module Transport
    if (dest_address_etab) {
      $V(form["etablissement_destination_class"], "CEtabExterne");

      if (!adresse) {
        $('save_ask_transport').disabled = true;
        alert($T('CTransport-msg-Your establishment does not have an address, please fill in one or change your establishment'));
        return false;
      }
      else {
        $('save_ask_transport').disabled = false;

        $('labelFor_editTransport_transport_output_transfer').className = "notNullOK";

        dest_address_etab.down("td").innerHTML = adresse_complet;

        Control.Modal.close();
      }
    }
  },
  /**
   * Export des établissements externes au format CSV
   */
  exportEtabExterne: function () {
    new Url('etablissement', 'ajax_export_etablissements_externes').popup(400, 150);
  }
};