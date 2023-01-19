/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Tdb = {
  vue_alternative: false,

  searchGrossesse: function () {
    new Url("maternite", "ajax_modal_search_grossesse")
      .addParam("lastname", $V("_seek_patient"))
      .requestModal("95%", "95%");
  },

  editRdvConsult: function (_id, grossesse_id, patient_id, callback) {
    new Url("cabinet", "edit_planning")
      .addParam("consultation_id", _id)
      .addParam("grossesse_id", grossesse_id)
      .addParam("pat_id", patient_id)
      .addParam("dialog", 1)
      .addParam("modal", 1)
      .addParam("callback", "afterEditConsultMater")
      .requestModal("900", "600", {
        onClose: function () {
          if (Object.isFunction(callback)) {
            callback();
          }
        }
      });
  },

  editConsult: function (consultation_id, callback) {
    new Url("cabinet", "ajax_full_consult")
      .addParam("consult_id", consultation_id)
      .modal({
        width:      "95%",
        height:     "95%",
        afterClose: function () {
          if (Object.isFunction(callback)) {
            callback();
          }
        }
      });
  },

  editGrossesse: function (_id, patient_id, callback) {
    new Url("maternite", "ajax_edit_grossesse")
      .addParam("grossesse_id", _id)
      .addParam("parturiente_id", patient_id)
      .addParam("with_buttons", 1)
      .addParam("creation_mode", 0)
      .requestModal("70%", "97%", {onClose: function () {
          if (Object.isFunction(callback)) {
            callback();
          }
          if (Tdb.vue_alternative) {
            Tdb.views.listTermesPrevus();
          } else {
            Tdb.views.listGrossesses();
          }
        }});
  },

  editSejour: function (_id, grossesse_id, patiente_id, callback) {
    new Url("planningOp", "vw_edit_sejour")
      .addParam("sejour_id", _id)
      .addParam("grossesse_id", grossesse_id)
      .addParam("patient_id", patiente_id)
      .addParam("dialog", 1)
      .modal({
        width:      "95%",
        height:     "95%",
        afterClose: function () {
          if (Object.isFunction(callback)) {
            callback();
          }
          if (Tdb.vue_alternative) {
            Tdb.views.listTermesPrevus();
          } else {
            Tdb.views.listGrossesses();
          }
        }
      })
  },

  editD2S: function (sejour_id, op_id) {
    new Url("soins", "viewDossierSejour")
      .addParam("sejour_id", sejour_id)
      .addParam("operation_id", op_id)
      .addParam("modal", 0)
      .modal({width: "95%", height: "95%"});
  },

  dossierAccouchement: function (op_id) {
    new Url("salleOp", "ajax_vw_operation")
      .addParam("operation_id", op_id)
      .modal({
        width:      "95%",
        height:     "95%",
        afterClose: function() {
          Tdb.views.listAccouchements();
          Tdb.views.listHospitalisations();
        }
      });
  },

  editAccouchement: function (_id, sejour_id, grossesse_id, patiente_id, callback, salle_id) {
    new Url("planningOp", "vw_edit_urgence")
      .addParam("operation_id", _id)
      .addParam("sejour_id", sejour_id)
      .addParam("grossesse_id", grossesse_id)
      .addParam("pat_id", patiente_id)
      .addParam("hour_urgence", new Date().getHours())
      .addParam("min_urgence", 0)
      .addParam("salle_id", salle_id)
      .modal({
        width:      "95%",
        height:     "95%",
        afterClose: function () {
          if (callback) {
            callback();
          }
          if (Tdb.vue_alternative) {
            Tdb.views.listTermesPrevus(true);
          } else {
            Tdb.views.listGrossesses(true);
          }
        }
      });
  },

  changeSalleFor: function (op_id, salle_id) {
    var form = getForm("changeSalleForOp");
    $V(form.operation_id, op_id);
    $V(form.salle_id, salle_id);
    form.onsubmit();
  },

  changeAnesthFor: function (op_id, anesth_id) {
    var form = getForm("changeAnesthForOp");
    $V(form.operation_id, op_id);
    $V(form.anesth_id, anesth_id);
    form.onsubmit();
  },

  changeStatusConsult: function (consult_id, status) {
    var form = getForm("changeStatusConsult");
    $V(form.consultation_id, consult_id);
    $V(form.chrono, status);
    form.onsubmit();
  },

  checklistsOpenSalle: function (date) {
    var url = new Url("maternite", "ajax_checklist_maternite");
    url.addParam("date", date);
    url.requestModal('50%', '50%');
  },

  filtreNaissances: function (date) {
    var url = new Url("maternite", "vw_tdb_naissances");
    url.addParam("date", date);
    url.requestModal("100%", "100%");
  },

  selectServices: function (view) {
    var url = new Url("hospi", "ajax_select_services");
    url.addParam("view", view);
    url.addParam("ajax_request", 1);
    url.requestModal(null, null, {maxHeight: "90%"});
  },

  listNaisances: function (form) {
    var url = new Url("maternite", "ajax_vw_list_naissances");
    url.addParam("_date_min", $V(form._date_min));
    url.addParam("_date_max", $V(form._date_max));
    url.addParam("_datetime_min", $V(form._datetime_min));
    url.addParam("_datetime_max", $V(form._datetime_max));
    url.addParam("date_guthrie_min", $V(form.date_guthrie_min));
    url.addParam("date_guthrie_max", $V(form.date_guthrie_max));
    url.addParam("pediatre_id", $V(form.praticien_id));
    url.addParam("state", $V(form.state));
    url.addParam("page", $V(form.page));
    url.addParam("col_order", $V(form.col_order));
    url.addParam("col_way", $V(form.col_way));
    url.requestUpdate("tdb_naissances");
  },

  changePage: function (page) {
    var url = new Url("maternite", "ajax_vw_list_naissances");
    url.addParam("page", page);
    url.requestUpdate("tdb_naissances");
  },

  emptyDates: function (element, context) {
    var form = getForm('tdbNaissances');
    var status = element.value;

    if (!status) {
      return false;
    }

    if (status == "consult_pediatre" || context == 'sejour') {
      $V(form._date_min, '');
      $V(form._date_min_da, '');
      $V(form._date_max, '');
      $V(form._date_max_da, '');
    }

    if (status == "consult_pediatre" || context == 'naissance') {
      $V(form._datetime_min, '');
      $V(form._datetime_min_da, '');
      $V(form._datetime_max, '');
      $V(form._datetime_max_da, '');
    }

    return false;
  },

  printPediatricConsult: function (order_col, order_way) {
    var form = getForm("tdbNaissances");

    var url = new Url("maternite", "print_pediatric_consult");
    url.addFormData(form);
    url.addParam("order_col", order_col);
    url.addParam("order_way", order_way);
    url.popup(1200, 800, 'Impression de la liste des consultations pédiatres');
  },

  printPediatricNurse: function (order_col, order_way) {
    var form = getForm("tdbNaissances");

    var url = new Url("maternite", "print_pediatric_nurse");
    url.addFormData(form);
    url.addParam("order_col", order_col);
    url.addParam("order_way", order_way);
    url.popup(1200, 800, 'Impression de la liste des puéricultrices');
  },

  printTransmissionSheet: function (order_col, order_way) {
    var form = getForm("tdbNaissances");

    var url = new Url("maternite", "print_transmission_sheet");
    url.addFormData(form);
    url.addParam("order_col", order_col);
    url.addParam("order_way", order_way);
    url.popup(1200, 800, 'Impression de la fiche de transmission');
  },
    /**
     *  Print the births list
     *
     * @param form
     */
  printNaissances: function (form) {
    new Url("maternite", "ajax_vw_list_naissances")
    .addParam("_date_min", $V(form._date_min))
    .addParam("_date_max", $V(form._date_max))
    .addParam("_datetime_min", $V(form._datetime_min))
    .addParam("_datetime_max", $V(form._datetime_max))
    .addParam("date_guthrie_min", $V(form.date_guthrie_min))
    .addParam("date_guthrie_max", $V(form.date_guthrie_max))
    .addParam("pediatre_id", $V(form.praticien_id))
    .addParam("state", $V(form.state))
    .addParam("page", $V(form.page))
    .addParam("print", 1)
    .addParam("col_order", $V(form.col_order))
    .addParam("col_way", $V(form.col_way))
    .popup(1200, 800, $T('CNaissance-Dashboard of birth|pl'));
  },

  listPediatres: function () {
    var form = getForm("tdbNaissances");
    var url = new Url("system", "ajax_seek_autocomplete");
    url.addParam("object_class", "CMediusers");
    url.addParam('show_view', true);
    url.addParam("limit", 200);
    url.addParam("input_field", "_prat_autocomplete");
    url.autoComplete(form.elements._prat_autocomplete, null, {
      minChars:           2,
      method:             "get",
      select:             "view",
      dropdown:           true,
      width:              "180px",
      afterUpdateElement: function (field, selected) {
        var prat_id = selected.getAttribute('id').split('-')[2];
        $V(field.form['praticien_id'], prat_id);
      },
      callback:           function (input, queryString) {
        queryString += "&where[users_mediboard.actif]=1";
        queryString += "&where_complex[users_mediboard.type]=IN('3', '13')";
        queryString += "&where[users_mediboard.spec_cpam_id]=12";

        return queryString;
      }
    });
  },

  views: {
    see_finished: 1,
    date:         "",

    toggleFinished: function () {
      Tdb.views.see_finished = +!Tdb.views.see_finished;
      Tdb.views.listAccouchements();
    },

    initListGrossesses: function () {
      new Url("maternite", "ajax_tdb_grossesses")
        .addParam("date", Tdb.views.date)
        .periodicalUpdate("grossesses", {
          frequency: 120, onSuccess: function () {
            Tdb.views.listConsultations(true);
          }
        });
    },

    initListTermesPrevus: function () {
      new Url("maternite", "ajax_tdb_termes_prevus")
        .addParam("date", Tdb.views.date)
        .periodicalUpdate("termes_prevus", {
          frequency: 120, onSuccess: function () {
            Tdb.views.listHospitalisations(true);
          }
        });
    },

    listTermesPrevus: function (cascade) {
      new Url("maternite", "ajax_tdb_termes_prevus")
        .addParam("date", Tdb.views.date)
        .requestUpdate("termes_prevus", {
          onSuccess: function () {
            if (cascade) {
              Tdb.views.listHospitalisations(cascade);
            }
          }
        });
    },

    listGrossesses: function (cascade) {
      new Url("maternite", "ajax_tdb_grossesses")
        .addParam("date", Tdb.views.date)
        .requestUpdate("grossesses", {
          onSuccess: function () {
            if (cascade) {
              Tdb.views.listConsultations(cascade);
            }
          }
        });
    },

    listConsultations: function (cascade, elt) {

      if(elt){
        elt = elt.checked ? 1 : 0;
      }else{
        elt = 0;
      }
      new Url("maternite", "ajax_tdb_consultations")
        .addParam("date", Tdb.views.date)
        .addParam("show_all_consult", elt)
        .requestUpdate("consultations", {
          onSuccess: function () {
            if (cascade) {
              Tdb.views.listHospitalisations(cascade);
            }
          }
        });
    },

    listHospitalisations: function (cascade) {
      new Url("maternite", "ajax_tdb_hospitalisations")
        .addParam("date", Tdb.views.date)
        .requestUpdate("hospitalisations", {
          onSuccess: function () {
            if (cascade) {
              Tdb.views.listAccouchements();
            }
          }
        });
    },

    listAccouchements: function () {
      new Url("maternite", "ajax_tdb_accouchements")
        .addParam("date", Tdb.views.date)
        .addParam("see_finished", Tdb.views.see_finished)
        .requestUpdate("accouchements");
    },

    filterByText: function (target) {
      var value = $V($("_seek_patient"));

      var tables = ["admissions", "grossesses_tab", "consultations_tab", "hospitalisation_tab", "accouchements_tab"];
      if (target) {
        tables = [target];
      }
      tables.each(function (table_id) {
        var table = $(table_id);
        if (!table) {
          return;
        }
        var elt = (table_id == "hospitalisation_tab" || table_id == "admissions") ? "tbody" : "tr";
        table.select(".CPatient-view").each(function (e) {
          if (value && !e.getText().like(value)) {
            e.up(elt).hide();
          } else {
            e.up(elt).show();
          }
        });
      });
    }
  },
  /**
   * Choose sort column
   *
   * @param order_col
   * @param order_way
   */
  sortBy: function (order_col, order_way) {
    var form = getForm('tdbNaissances');
    var url = new Url("maternite", "ajax_vw_list_naissances");
    url.addParam('order_col', order_col);
    url.addParam('order_way', order_way);
    url.addParam("_datetime_min", $V(form._datetime_min));
    url.addParam("_datetime_max", $V(form._datetime_max));
    url.requestUpdate("tdb_naissances");
  },
  /**
   * Show the prenancy dashboard (history, perinatal folder, forms)
   *
   * @param {int} grossesse_id
   * @param {boolean} show_header
   */
  showPrenancyDashboard: function (grossesse_id, show_header) {
    new Url("maternite", "ajax_vw_tdb_grossesse")
      .addParam("grossesse_id", grossesse_id)
      .addParam("show_header", show_header)
      .requestModal("95%", "95%");
  },
};
