/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Operation = {
  edit: function(operation_id, plage_id, callback) {
    new Url("planningOp", plage_id ? "vw_edit_planning" : "vw_edit_urgence", "tab").
      addParam("operation_id", operation_id).
      redirectOpener();
  },

  editFast: function(operation_id, sejour_id, callback) {
    new Url('planningOp', 'ajax_add_fast_operation')
      .addParam('operation_id', operation_id)
      .addParam('sejour_id', sejour_id)
      .requestModal('600', '400', {onClose: callback});
  },

  print: function(operation_id) {
    new Url("planningOp", "view_planning").
      addParam("operation_id", operation_id).
      popup(700, 550, "Admission");
  },

  modalCallback: function() {
    document.location.reload();
  },

  editModal: function(operation_id, plage_id, callback) {
    callback = callback || this.modalCallback;
    var url = new Url("planningOp", plage_id ? "vw_edit_planning" : "vw_edit_urgence", "action");
    url.addParam("operation_id", operation_id);
    url.addParam("dialog", 1);
    url.modal({
      width     : "100%",
      height    : "100%",
      afterClose: callback
    });
  },
  /**
   * Open the bloc folder
   *
   * @param operation_id
   * @param callback
   * @param fragment
   */
  dossierBloc: function(operation_id, callback, fragment) {
    callback = callback || this.modalCallback;
    var url = new Url("salleOp", "ajax_vw_operation");
    url.addParam("operation_id", operation_id);
    url.addParam("fragment"    , fragment);
    url.modal({
      width     : "100%",
      height    : "100%",
      afterClose: callback
    });
  },

  showDossierSoins: function(sejour_id, default_tab, callback) {
    callback = callback || this.modalCallback;
    var url = new Url("soins", "viewDossierSejour");
    url.addParam("sejour_id", sejour_id);
    url.addParam("modal", "1");
    url.addParam("default_tab", default_tab);
    url.requestModal("100%", "100%", {
      onClose: callback
    });
    modalWindow = url.modalObject;
  },

  useModal: function() {
    this.edit = this.editModal;
  },

  switchOperationsFromSalles : function(salle1, salle2, date, callback) {
    var url = new Url("planningOp", "controllers/do_switch_operations_from_2_salles");
    url.addParam("salle_1", salle1);
    url.addParam("salle_2", salle2);
    url.addParam("date", date);
    if (confirm("Etes vous sur de vouloir échanger les interventions de ces deux salles")) {
      url.requestUpdate("systemMsg", {onComplete: callback});
    }
  },
  /**
   * Check if the operation is within the bounds of stays
   *
   * @param oFormOperation
   * @param oFormSejour
   */
  checkOperationInStay: function(oFormOperation, oFormSejour) {
    // DHE hors plage
    var operation_timing = $V(oFormOperation.date) + " " + $V(oFormOperation._time_urgence);
    var sejour_entree_min = ($V(oFormSejour._min_entree_prevue) == 0) ? '00' : $V(oFormSejour._min_entree_prevue);
    var sejour_sortie_min = ($V(oFormSejour._min_sortie_prevue) == 0) ? '00' : $V(oFormSejour._min_sortie_prevue);
    var sejour_entree_hour = ($V(oFormSejour._hour_entree_prevue) < 10) ? '0' + $V(oFormSejour._hour_entree_prevue).toString() : $V(oFormSejour._hour_entree_prevue);
    var sejour_sortie_hour = ($V(oFormSejour._hour_sortie_prevue) < 10) ? '0' + $V(oFormSejour._hour_sortie_prevue).toString() : $V(oFormSejour._hour_sortie_prevue);
    var datetime_entree = $V(oFormSejour._date_entree_prevue) + " " + sejour_entree_hour + ":" + sejour_entree_min + ":00";
    var datetime_sortie = $V(oFormSejour._date_sortie_prevue) + " " + sejour_sortie_hour + ":" + sejour_sortie_min + ":00";

    // DHE avec plage
    if (oFormOperation._locale_date) {
      operation_timing = Date.fromLocaleDate($V(oFormOperation._locale_date).split(' ')[0]).toDATE();

      datetime_entree = $V(oFormSejour._date_entree_prevue);
      datetime_sortie = $V(oFormSejour._date_sortie_prevue);
    }

    if (($V(oFormOperation.annulee) == '0') && ((operation_timing < datetime_entree) || (operation_timing > datetime_sortie))) {
      var msg = "/!\\ " + $T('COperation-msg-Please modify the hours of the stay so that the intervention is within the limits of the stay');

      alert(msg);
      return false;
    }

    return true;
  }
};

Libelle = {
  modal: null,
  edit: function(libelle_id) {
    var url = new Url('dPplanningOp', 'ajax_edit_libelle');
    url.addParam('libelle_id', libelle_id);
    url.requestModal(500);
    url.modalObject.observe('afterClose', function() {
      getForm('search_libelle').onsubmit();
    })
  },
  refreshlistLibelle: function (operation_id) {
    var url = new Url('dPplanningOp', 'ajax_vw_libelles_op');
    url.addParam('operation_id', operation_id);
    url.requestUpdate('libelles');
  }
};

LiaisonOp = {
  modal: null,
  url: null,
  edit: function(operation_id, callback) {
    callback = callback || Prototype.emptyFunction;
    var url = new Url('dPplanningOp', 'vw_libelles_op');
    url.addParam('operation_id', operation_id);
    url.requestModal(500, 300, {onClose: callback});
    this.url = url;
  },
  onDeletion: function(form) {
    if (!form.libelleop_id.value) {
      form.libelleop_id.value = 1;
    }
    return confirmDeletion(form, { typeName: 'l\'affectation de libellé'},
      { onComplete: function(){
        LiaisonOp.url.refreshModal();
      }}
    );
  },
  submit: function(form) {
    return onSubmitFormAjax(form, {
      onComplete : function() {
        LiaisonOp.url.refreshModal();
      }
    });
  }
};
