/**
 * @package Mediboard\Xds
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

XDSDocument = {
  modal: null,

  action: function (action) {
    new Url("xds", "vw_tools_xds")
      .addParam("action", action)
      .requestUpdate("resultAction");
  },

  displayActionDocument: function (document_guid, onComplete) {
    new Url("xds", "ajax_action_xds_document")
      .addParam("document_guid", document_guid)
      .requestModal(700, 500, {onClose: onComplete});
  },

  send: function(document_guid) {
    new Url("xds", "do_repository_file_aed", "dosql")
      .addParam("document_guid", document_guid)
      .requestUpdate(
        SystemMessage.id,
        {method: "post"}
      );
  },

  displayDocument: function (repository_id, oid, patient_id) {
    new Url("xds", "ajax_display_xds_document")
      .addParam("repository_id", repository_id)
      .addParam("oid", oid)
      .addParam("patient_id", patient_id)
      .requestModal(600, 600);
  },

  searchDocument: function (form) {
    new Url("xds", "ajax_search_documents")
      .addFormData(form)
      .requestUpdate("result_search_documents");

    return false;
  },
};