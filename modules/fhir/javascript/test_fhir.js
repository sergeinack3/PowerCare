/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

TestFHIR = {
  showPDQmRequest: function () {
    new Url("fhir", "ajax_fhir_search")
      .addParam("search_type", "CPDQm")
      .requestUpdate("test_fhir_pdqm");
  },

  showPIXmRequest: function () {
    new Url("fhir", "ajax_fhir_search")
      .addParam("search_type", "CPIXm")
      .requestUpdate("test_fhir_pixm");
  },

  showMHDRequest: function () {
    new Url("fhir", "ajax_fhir_search")
      .addParam("search_type", "CMHD")
      .requestUpdate("test_fhir_mhd");
  },

  showFHIRResources: function () {
    var form = getForm('request_options_fhir')
    var url = new Url("fhir", "ajax_fhir_resources")
    if (form) {
      url.addFormData(form)
    }
    url.requestUpdate("test_fhir_resources");
  },

  request: function (form, search_type) {
    new Url("fhir", "ajax_request_fhir")
      .addFormData(form)
      .addParam("search_type", search_type)
      .requestUpdate("request_" + search_type);

    return false;
  },

  requestWithURI: function (uri, search_type) {
    new Url("fhir", "ajax_request_fhir")
      .addParam("uri", uri)
      .addParam("search_type", search_type)
      .requestUpdate("request_" + search_type);

    return false;
  },

  readPDQm: function (id, format) {
    new Url("fhir", "ajax_request_fhir")
      .addParam("search_type", "CPDQm")
      .addParam("response_type", format)
      .addParam("id", id)
      .requestModal(900, 400);

    return false;
  },

  crudOperations: function (form, interaction) {
    var url = new Url("fhir", "ajax_crud_operations")
      .addFormData(form)
      .addParam("interaction"  , interaction)

    var form_options = getForm('request_options_fhir');
    var response_type = form_options.elements.response_type;
    if (response_type && $V(response_type)) {
      url.addParam("response_type", $V(response_type));
    }

    url.requestUpdate("result_crud_operations");

    return false;
  },

  capabilityStatement: function () {
    var url = new Url("fhir", "ajax_show_capability_statement");

    var form_options = getForm('request_options_fhir');
    var response_type = form_options.elements.response_type;
    if (response_type && $V(response_type)) {
      url.addParam("response_type", $V(response_type));
    }

    url.requestUpdate("result_crud_operations");

    return false;
  },

  showDocument : function (fhir_resource_id) {
    new Url("fhir", "ajax_show_document")
      .addParam("fhir_resource_id", fhir_resource_id)
      .requestModal("80%", "80%");
  },

  getFilesFromNDA : function(form, search_type) {
    new Url("fhir", "ajax_get_files_form_nda")
      .addFormData(form)
      .addParam("search_type", search_type)
      .requestUpdate("list_files_from_nda");

    return false;
  },

  viewMessagesSupported : function(actor_guid, exchange_class) {
    var url = new Url("eai", "ajax_vw_messages_supported");
    url.addParam("actor_guid", actor_guid);
    url.addParam("exchange_class", exchange_class);
    url.requestModal("90%", "85%", {onClose: function() {
        TestFHIR.showFHIRResources()
      }});
  },
};
