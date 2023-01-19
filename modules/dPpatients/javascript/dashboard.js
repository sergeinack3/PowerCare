/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

dashboard = {
  modal : null,
  openGraph : false,
  check : false,

  addConstante: function (form) {
    var formulaire = getForm("search_constantes");

    new Url("patients", "controllers/do_aed_releve")
      .addFormData(form)
      .requestUpdate("systemMsg", {onComplete : function() {
          Control.Modal.close();
          dashboard.refreshReleve($V(formulaire.elements.patient_id));
        }});
  },
  addMedicaleStatement : function() {
    var form = getForm("search_constantes");
    var patient_id_param = $V(form.elements.patient_id);

    new Url("patients", "ajax_modal_create_releve")
      .addParam("patient_id", patient_id_param)
      .requestModal("50%", "50%");
  },

  deleteReleve : function(releve_id) {
    var formulaire = getForm("search_constantes");
    Modal.confirm(
      $T('CConstantReleve-delete-confirmation'),
      {
        onOK: function () {
          var form             = getForm("search_constantes");
          var onlyActive       = 1;
          if (!$V(form.elements.constant_active)) {
            onlyActive = 0;
          }
          new Url("patients", "ajax_delete_releve")
            .addParam("releve_id", releve_id)
            .addParam("patient_id", $V(formulaire.elements.patient_id))
            .addParam("onlyActive", onlyActive)
            .requestUpdate("result_search_constantes", {onComplete : function () {
                if(dashboard.openGraph){
                  dashboard.refreshGraphic();
                }
              }});
        }
      }
    );
  },

  searchPatient: function () {
    var search_patient         = getForm("search_patient");
    var search_constantes      = getForm("search_constantes");

    var url = new Url("system", "ajax_seek_autocomplete");
    url.addParam("object_class", "CPatient");
    url.addParam("field", "patient_id");
    url.addParam("view_field", "_patient_view");
    url.addParam("input_field", "_target_patient");
    url.autoComplete(search_patient.elements._target_patient, null, {
      minChars: 3,
      method: "get",
      select: "view",
      dropdown: false,
      width: "300px",
      afterUpdateElement : function(input, selected) {
        input.innerHTML = selected.children[0].innerHTML;
        var patient_id = selected.getAttribute("data-id");
        $V(search_constantes.elements.patient_id, patient_id);
        dashboard.refreshReleve(patient_id);
        new Url("patients","controllers/do_aed_session_patient")
          .addParam("patient_id", patient_id)
          .requestUpdate("systemMsg");
      }
    });
  },

  refreshReleve : function (patient_id) {
    var form             = getForm("search_constantes");
    var patient_id_param = patient_id ? patient_id : $V(form.elements.patient_id);
    var onlyActive       = 1;
    if (!$V(form.elements.constant_active)) {
      onlyActive = 0;
    }
    var check = 0;
    if (dashboard.check) {
      check = 1;
      dashboard.check = false;
    }
    new Url("patients", "ajax_search_constantes")
      .addParam("patient_id", patient_id_param)
      .addParam("check", check)
      .addParam("onlyActive", onlyActive )
      .requestUpdate("result_search_constantes");
    if(dashboard.openGraph){
      dashboard.refreshGraphic(patient_id);
    }
  },

  refreshGraphic : function(patient_id) {
    this.showGraphByconstant();
  },

  modifConstantes : function (releve_id) {
    new Url("patients", "ajax_modal_manage_constants")
      .addParam("releve_id", releve_id)
      .requestModal("50%","50%", {onClose : function () {
        if (dashboard.check) {
          dashboard.refreshReleve();
        }
    }});
  },

  deleteConstante : function(value_guid, releve_id){
    Modal.confirm(
      $T('CValueConstanteMedicale-delete-confirmation'),
      {
        onOK: function () {
          Control.Modal.close();
          new Url("patients", "controllers/do_delete_constant_value")
            .addParam("value_guid", value_guid)
            .requestUpdate("systemMsg", {onComplete : function () {
                dashboard.check = true;
                dashboard.modifConstantes(releve_id);
              }});
        }
      }
    );

  },

  updateConstante : function(form, releve_id) {
    Control.Modal.close();
    new Url("patients", "controllers/do_aed_releve")
      .addFormData(form)
      .requestUpdate("systemMsg", {onComplete : function () {
          dashboard.refreshLine(releve_id);
        }});
  },

  refreshLine : function (releve_id) {
    var form = getForm("search_constantes");
    var onlyActive = $V(form.elements.constant_active);
    onlyActive ? onlyActive = 1 : onlyActive = 0;

    new Url("patients", "ajax_update_constante")
      .addParam("releve_id", releve_id)
      .addParam("onlyActive", onlyActive)
      .requestUpdate("display_releve_"+releve_id)
  },

  showGraphByconstant: function(patient_id, constant_name) {
    var form = getForm("search_constantes");
    var form_graph = getForm("form_constant_graph");
    var patient_id_param = patient_id ? patient_id : $V(form.elements.patient_id);

    if(!constant_name) {
      constant_name = $V(form_graph.elements.select_graph)
    }
    this.constant = constant_name;
    new Url("patients", "ajax_graphs_by_constant")
      .addParam("patient_id", patient_id_param)
      .addParam("constant_name", this.constant)
      .requestUpdate("containerGraphs");
  },

  changePeriod : function (val) {
    var form  = getForm("form_add_constante_medicale");
    var valeur;
    val ?  valeur = val : valeur = $V(form.elements._category);
    var item  = $V(form.elements._scope);
    if (!valeur) {
      return;
    }
    new Url("patients","ajax_change_cat_releve")
      .addParam("_category", valeur)
      .addParam("_scope", item)
      .requestUpdate("modal_create_list_constant");
  },

  addConstantToReleve : function(releve_id ,form) {
    new Url("patients", "controllers/do_aed_releve")
      .addFormData(form)
      .requestUpdate("systemMsg", {onComplete: function () {
          Control.Modal.close();
          dashboard.modifConstantes(releve_id);
        }});
  }
};
