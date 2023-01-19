/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Ccda = {

  showxml : function(name) {
    new Url("cda", "ajax_show_xml_type")
      .addParam("name", name)
      .requestUpdate("xmltype-view");
  },

  showCDA : function(form) {
    new Url("cda", "ajax_showCDA", "raw")
      .pop("100%", "100%", "CDA", null, null, { "message" : $V(form.message)});

    return false;
  },

  highlightMessage : function(form) {
    return Url.update(form, "highlighted");
  },

  action : function(action) {
    new Url("cda", "vw_toolsdatatype")
      .addParam("action", action)
      .requestUpdate("resultAction");
  },

  createCDA : function(form) {
    new Url("cda", "ajax_create_cda_vsm")
      .addParam("object_class", $V(form.elements.object_class))
      .addParam("object_id", $V(form.elements.object_id))
      .requestModal("80%", "80%");
  },

  createCDA_LDL : function(form) {
    new Url("cda", "ajax_create_ldl")
      .addParam("object_class", $V(form.elements.object_class))
      .addParam("object_id", $V(form.elements.object_id))
      .addParam("type_ldl", $V(form.elements.type_ldl))
      .requestModal("80%", "80%");
  },

  createCDA_vaccination : function(form) {
    new Url("cda", "ajax_create_vaccination")
      .addParam("object_class", $V(form.elements.object_class))
      .addParam("object_id", $V(form.elements.object_id))
      .addParam("injection_id", $V(form.elements.injection_id))
      .requestModal("80%", "80%");
  },

  createIHE_XDM_CDA_LVL1 : function(form) {
    new Url("cda", "ajax_create_IHE_XDM_CDA_LVL1")
        .addParam("object_class", $V(form.elements.object_class))
        .addParam("object_id", $V(form.elements.object_id))
        .requestModal("80%", "80%");
  },

  createIHE_XDM_CDA_LVL3 : function(form) {
    new Url("cda", "ajax_create_IHE_XDM_CDA_LVL3")
      .addParam("file_id", $V(form.elements.file_id))
      .requestModal("80%", "80%");
  },

  submitSaisieInsc : function(form) {
    var birthDate = form["birthDate"].value;
    var firstName = form["firstName"].value;
    var nir       = form["nir"].value;
    var nirKey    = form["nirKey"].value;

    new Url("cda", "ajax_test_insc_saisi")
      .addParam("birthDate"  , birthDate)
      .addParam("firstName"  , firstName)
      .addParam("nir"        , nir)
      .addParam("nirKey"     , nirKey)
      .addParam("accept_utf8", 1)
      .requestUpdate("test_insc");

    return false;
  },

  generateVSM : function(object_id, object_class) {
    new Url("cda", "ajax_create_cda_vsm")
      .addParam("object_id"  , object_id)
      .addParam("object_class"  , object_class)
      .requestModal("80%", "80%");

    return false;
  },

  generateLDL : function(object_id, object_class, type_ldl) {
    new Url("cda", "ajax_create_ldl")
      .addParam("object_id"  , object_id)
      .addParam("object_class"  , object_class)
      .addParam("type_ldl"  , type_ldl)
      .requestUpdate("systemMsg");

    return false;
  },

  manageBase64 : function (name_form) {
    var form = getForm(name_form);

    var url = new Url('cda', 'ajax_manage_base64', 'dosql');
    url.addParam('message', form.elements.message.value);
    url.addParam('encode', form.elements.encode.value);
    url.requestUpdate('result_base64' , {method: 'post'});

    return false;
  }
};
