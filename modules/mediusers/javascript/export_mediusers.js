/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
ExportMediusers = window.ExportMediusers || {
  submitFormExport: function (form) {
    var url = new Url("mediusers","ajax_export_mediusers_xml", "raw");
    url.addFormData(form);
    url.popup();
  },

  openExportMediusersXml: function() {
    var url = new Url("mediusers", "vw_export_mediusers_xml");
    url.requestModal(800, 600);
  },

  openImportMediusers: function() {
    var url = new Url("mediusers", "vw_import_mediusers");
    url.requestModal(800, 600);
  },

  openImportProfile: function() {
    var url = new Url("mediusers", "vw_import_profile");
    url.requestModal('80%', '80%');
  },

  addPerms: function() {
    var url = new Url("mediusers", "ajax_add_user_function_group_perms");
    url.requestUpdate("resultDroits");
  },

  openTypeLibelle: function() {
    var url = new Url("mediusers", "vw_type_libelle_correspondance");
    url.requestModal();
  },

  exportMediusersCsv: function() {
    var url = new Url('mediusers', 'ajax_export_mediusers_csv', 'raw');

    $$('input.export_optionnal_field').each(function(elt) {
      console.log(elt);
      if ($V(elt)) {
        url.addParam(elt.name, '1');
      }
    });

    url.popup();
  },

  openExportMediusersCSV: function() {
    var url = new Url("mediusers", "vw_export_mediusers_csv");
    url.requestModal();
  }
};
