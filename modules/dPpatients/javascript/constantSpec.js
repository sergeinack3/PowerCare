/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

constantSpec = {
  state: 0,
  refresh:0,
  indexUnit: null,

  editConstantSpec: function (spec_id) {
    new Url("patients", "ajax_modal_constant_spec")
      .addParam("constant_spec_id", spec_id)
      .requestModal(null, null, {
        onClose: function () {
          constantSpec.state = 0;
          constantSpec.refreshConstant();
          constantSpec.indexUnit = null;
        },
      });
  },

  refreshConstant : function(callback, reset_cache) {
    if (callback) {
      callback();
    }
    if (!reset_cache) {
      reset_cache = 0;
    }
    new Url("patients", "vw_constant_spec")
      .addParam("refresh", "1")
      .addParam("reset_cache", reset_cache)
      .requestUpdate("table_constant_spec")
  },

  updatefield: function (value_class) {
    new Url("patients", "ajax_constant_spec_change_type")
      .addParam('value_class', value_class)
      .requestUpdate("constant_spec_type_value");
  },


  addEnum: function () {
    var form = getForm("constantSpec_form");
    var list = $V(form.elements.constantSpec_list);
    var value = $V(form.elements.value_list);
    if (!value) {
      return false;
    }
    if (!list) {
      $V(form.elements.constantSpec_list, value);
    } else {
      $V(form.elements.constantSpec_list, list + "|" + value);
    }
    $V(form.elements.value_list, "");
  },

  deleteConstantSpec: function (constant_id) {
    Modal.confirm(
      $T('CConstantSpec-delete-confirmation'),
      {
        onOK: function () {
          new Url("patients", "controllers/do_delete_constant_spec")
            .addParam("constant_id", constant_id)
            .requestUpdate("systemMsg", {
              onComplete: function () {
                constantSpec.refreshConstant();
              }
            });
        }
      });
  },

  addConstantSpec: function (form) {
    new Url("patients", "controllers/do_aed_constant_spec")
      .addFormData(form)
      .requestUpdate("systemMsg", {onComplete : function() {
        //  Control.Modal.close();
          constantSpec.refreshConstant();
        }
      });
  },

  choicePeriod: function (select_name, input_name) {
    var input = document.getElementById(input_name);
    var select = document.getElementById(select_name);
    if (select.value === "") {
      return;
    }
    if (select.value === "other") {
      input.setAttribute("type", "number")
    } else {
      var input = document.getElementById(input_name);
      input.setAttribute("type", "hidden");
      input.setAttribute("value", select.value);
    }
  },

  saveEdit: function (form) {
    new Url("patients", "controllers/do_edit_constant_spec")
      .addFormData(form)
      .requestUpdate("systemMsg", {
        onComplete: function () {
          constantSpec.refreshConstant(function () {
            Control.Modal.close();
          });
        }
      });
  },

  showConfigAlerte: function () {
    var btn_config_alert = document.getElementById("container_config_alert");
    if (!btn_config_alert.classList.contains("hidden")) {
      btn_config_alert.classList.add("hidden");
    }
    var item = null;
    switch (constantSpec.state) {
      case 0 :
        item = document.getElementById("config_alert_1");
        break;
      case 1:
        item = document.getElementById("config_alert_2");
        break;
      case 2:
        item = document.getElementById("config_alert_3");
        break;
    }
    if (!item) {
      return;
    }
    constantSpec.state++;
    item.classList.remove("hidden");
  },

  editAlert: function (name) {
    new Url("patients", "ajax_modal_edit_alert")
      .addParam("name", name)
      .requestModal();
    constantSpec.refresh = 1;
  },

  addAlert : function (form, modal) {
    new Url("patients", "controllers/do_aed_alert")
      .addFormData(form)
      .requestUpdate("systemMsg", {onComplete : function () {
          if (modal) {
            Control.Modal.close();
            constantSpec.refreshConstant();
          }
        }});
  },

  check: function () {
    var name_form = getForm("form_add_alert").getAttribute("name");
    var min = parseInt($V(getForm(name_form).elements.min_value_spec));
    var max = parseInt($V(getForm(name_form).elements.max_value_spec));
    for (var i = 1; i <= 3; i++) {
      var seuil_bas = $V(document.getElementsByName("seuil_bas_" + i)[0]);
      var seuil_haut = $V(document.getElementsByName("seuil_haut_" + i)[0]);
      var comment_bas = $V(document.getElementsByName("comment_bas_" + i)[0]);
      var comment_haut = $V(document.getElementsByName("comment_haut_" + i)[0]);
      var label_seuil_bas = document.getElementById("labelFor_" + name_form + "_seuil_bas_" + i);
      var label_seuil_haut = document.getElementById("labelFor_" + name_form + "_seuil_haut_" + i);
      var label_comment_bas = document.getElementById("labelFor_" + name_form + "_comment_bas_" + i);
      var label_comment_haut = document.getElementById("labelFor_" + name_form + "_comment_haut_" + i);
      var span_seuil_bas = document.getElementById("seuil_bas_alert_" + i);
      var span_seuil_haut = document.getElementById("seuil_haut_alert_" + i);

      label_comment_bas.classList.remove("notNull", "notNullOK");
      label_seuil_bas.classList.remove("notNull", "notNullOK");
      label_comment_haut.classList.remove("notNull", "notNullOK");
      label_seuil_haut.classList.remove("notNull", "notNullOK");

      if (seuil_bas !== "" && comment_bas === "") {
        label_comment_bas.classList.add("notNull");
        label_seuil_bas.classList.add("notNullOK");
      } else if (seuil_bas === "" && comment_bas !== "") {
        label_seuil_bas.classList.add("notNull");
        label_comment_bas.classList.add("notNullOK");
      } else if (seuil_bas !== "" && comment_bas !== "") {
        label_seuil_bas.classList.add("notNullOK");
        label_comment_bas.classList.add("notNullOK");
      }

      if (seuil_haut !== "" && comment_haut === "") {
        label_comment_haut.classList.add("notNull");
        label_seuil_haut.classList.add("notNullOK");
      } else if (seuil_haut === "" && comment_haut !== "") {
        label_seuil_haut.classList.add("notNull");
        label_comment_haut.classList.add("notNullOK");
      } else if (seuil_haut !== "" && comment_haut !== "") {
        label_seuil_haut.classList.add("notNullOK");
        label_comment_haut.classList.add("notNullOK");
      }


      seuil_bas  = parseInt(seuil_bas);
      seuil_haut = parseInt(seuil_haut);
      if (seuil_bas !== undefined && seuil_bas < min) {
        span_seuil_bas.style.display = "inline-block";
        span_seuil_bas.title = $T('CConstantAlert-add-msg failed, bound min %s is smaller than min value', $T('CConstantAlert.level.'+i));
      } else if (seuil_haut !== undefined && seuil_haut > max) {
        span_seuil_haut.style.display = "inline-block";
        span_seuil_haut.title = $T('CConstantAlert-add-msg failed, bound max %s is greater than max value', $T('CConstantAlert.level.'+i));
      } else  if (seuil_bas !== undefined && seuil_haut !== undefined && seuil_haut < seuil_bas) {
        span_seuil_haut.style.display = "inline-block";
        span_seuil_haut.title = $T('CConstantAlert-add-msg failed, bound max %s is not greater than bound min', $T('CConstantAlert.level.'+i));
      }
      else if (seuil_bas !== undefined && seuil_haut !== undefined && seuil_bas > seuil_haut) {
        span_seuil_bas.style.display = "inline-block";
        span_seuil_bas.title = $T('CConstantAlert-add-msg failed, bound min %s is not smaller than bound max', $T('CConstantAlert.level.'+i));
      }
      else {
        span_seuil_bas.style.display = "none";
        span_seuil_haut.style.display = "none";
      }
    }
  },

  deleteAlert : function (constant_name, modal) {
    Modal.confirm(
      $T('CConstantAlert-msg-delete confirm'),
      {
        onOK: function () {
          new Url("patients", "controllers/do_delete_alert")
            .addParam("constant_name", constant_name)
            .requestUpdate("systemMsg", {onComplete: function () {
              if (modal) {
                Control.Modal.close();
                constantSpec.editAlert(constant_name);
              }
              }});
        }
      });
  },

  deleteAlertLevel : function (index) {
    var seuil_bas = document.getElementById("form_add_alert_seuil_bas_"+index);
    var seuil_haut = document.getElementById("form_add_alert_seuil_haut_"+index);
    var comment_bas = document.getElementById("form_add_alert_comment_bas_"+index);
    var comment_haut = document.getElementById("form_add_alert_comment_haut_"+index);
    $V(seuil_bas, "");
    $V(seuil_haut, "");
    $V(comment_bas, "");
    $V(comment_haut, "");
  },

  changeCat : function () {
    var form = getForm("form_calculated_constant");
    var cat  = $V(form.elements.category);
    var list = document.getElementsByName("select_cat_"+cat)[0];
    var elem = document.getElementById("constant_by_cat");
    var selects = elem.querySelectorAll("select");
    selects.forEach(function (element) {
      element.classList.add("hidden");
    });
    list.classList.remove("hidden");
  },

  initCalcul: function() {
    constantSpec.parD = 0;
    constantSpec.parG = 0;
    constantSpec.att = 0;
  },
  
  calculAddOp : function (item) {
    item = " " + item + " ";
    constantSpec.updateFormule(item);
  },

  calculAddNumber : function (item) {
    constantSpec.updateFormule(item);
  },

  calculAddParenthese : function (item) {
    constantSpec.updateFormule(item);
  },
  
  updateFormule : function (val) {
    var form = getForm("form_calculated_constant");
    var formule = form.elements._view_formule;

    $V(formule, $V(formule)+val)
  },

  addFormUnitSecondary : function (numerous) {
    if (constantSpec.indexUnit === null) {
      constantSpec.indexUnit = numerous +1;
    }

    if (constantSpec.indexUnit !== 0) {
      var unit_label = document.getElementById("constantspec_label_unit");
      var coeff_label = document.getElementById("constantspec_label_coeff");

      unit_label.classList.remove("hidden");
      coeff_label.classList.remove("hidden");
    }

    var list_form = document.getElementById("list_unit_secondary");
    var input_coeff = document.createElement("input");
    input_coeff.setAttribute("id", "constantSpec_form_coeff_"+constantSpec.indexUnit);
    input_coeff.setAttribute("type", "text");

    var input_unit  = document.createElement("input");
    input_unit.setAttribute("id", "constantSpec_form_unit_"+constantSpec.indexUnit);
    input_unit.setAttribute("type", "text");

    var tr = document.createElement("tr");
    var td1 = document.createElement("td");
    var td2 = document.createElement("td");
    td1.appendChild(input_unit);
    td2.appendChild(input_coeff);
    tr.appendChild(td1);
    tr.appendChild(td2);

    list_form.appendChild(tr);

    constantSpec.indexUnit++;
  },

  updatecheckField : function () {
    var unit_label = document.getElementById("labelFor_constantSpec_form_unit");
    var unit_field = document.getElementById("primary_unit");
    if (unit_label && unit_field) {
      if ($V(unit_field) !== "") {
        unit_label.classList.remove("notNull");
        unit_label.classList.add("notNullOK");
      }
      else {
        unit_label.classList.remove("notNullOK");
        unit_label.classList.add("notNull");
      }
    }
  },

  deleteUnitSecondary : function (index) {
    var unit  = document.getElementById("constantSpec_form_unit_"+index);
    var coeff = document.getElementById("constantSpec_form_coeff_"+index);

    $V(unit, "");
    $V(coeff, "");
  }

};