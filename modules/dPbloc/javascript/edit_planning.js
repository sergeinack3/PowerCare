/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

EditPlanning = window.EditPlanning || {
  status_images : ["images/icons/status_red.png", "images/icons/status_orange.png", "images/icons/status_green.png"],
  modal: null,
  modal_edit : null,
  modal_gestion : null,
  
  edit: function(plageop_id, date, bloc_id) {
    var url = new Url('bloc', 'inc_edit_planning');
    url.addParam('plageop_id', plageop_id);
    url.addParam('date', date);
    url.addParam('bloc_id', bloc_id);
    EditPlanning.modal_edit = url;
    url.requestModal(800);
    this.modal = url.modalObject;
    url.modalObject.observe("afterClose", function() {
      if (window.MultiSalle) {
        MultiSalle.reloadPlanning();
      }
      else {
        document.location.reload();
      }
    });
  },

  order: function(plageop_id) {
    var url = new Url('bloc', 'vw_edit_interventions');
    url.addParam('plageop_id', plageop_id);
    EditPlanning.modal_gestion = url;
    url.requestModal("100%", "90%");
    url.modalObject.observe("afterClose", function() {
      // Prefer the usage of updateBloc
      if (window.updateBloc) {
        updateBloc();
      }
      else {
        document.location.reload();
      }
    });
    window.url_edit_planning = url;
    this.modal = url.modalObject;
  },
  
  onSubmit: function(form) {
    return onSubmitFormAjax(form, { 
      onComplete: function() {
        EditPlanning.refreshList();
        EditPlanning.modal.close();
      }
    })
  },
  
  refreshList: function() {
    var url = new Url('bloc', 'inc_edit_planning');
    url.requestUpdate('modif_planning');
  },
  
  resfreshImageStatus: function(element){
    if (!element.get('id')) {
      return;
    }
  
    element.title = "";
    element.src   = "style/mediboard_ext/images/icons/loading.gif";
    
    url.addParam("source_guid", element.get('guid'));
    url.requestJSON(function(status) {
      element.src = EditPlanning.status_images[status.reachable];
      });
  },
  
  popPlanning: function(date) {
    var url = new Url("bloc", "view_planning");
    url.addParam("_datetime_min", date + " 00:00:00");
    url.addParam("_datetime_max", date + " 23:59:59");
    url.addParam("salle"    , 0);
    url.popup(900, 550, "Planning");
  },
  
  showAlerte: function(date, type, blocs_ids) {
    var url = new Url("bloc", "vw_alertes");
    url.addParam("date"   , date);
    url.addParam("type"   , type);
    if (blocs_ids) {
      url.addParam('blocs_ids[]', blocs_ids, true);
    }

    url.requestModal(800, 500);
  },
  
  monitorDaySalle: function(salle_id, date) {
    var url = new Url("bloc", "monitor_day_salle");
    url.addParam("salle_id", salle_id);
    url.addParam("date"    , date);
    url.requestModal(900);
  },
  
  lockPlages: function(form) {
    if(confirm('Voulez-vous verrouiller toutes les plages de ce jour ?')) {
      return checkForm(form);
    }
    return false;
  },

  printVacationStatus: function (form) {
    var url = new Url("bloc", "print_list_plages_op_free_cancel");
    url.addElement(form._datetime_min);
    url.addElement(form._datetime_max);
    url.addElement(form._prat_id);
    url.addElement(form._specialite);
    url.addParam("_bloc_id[]", $V(form.elements["_bloc_id[]"]), true);
    url.addParam("_salle_id[]", $V(form.elements["_salle_id[]"]), true);
    url.popup(1000, 500,  $T('CPlageOp-action-Print of free vacations and canceled vacations-desc'));
  },

  exportPlagesList: function () {
    var url = new Url("bloc", "print_list_plages_op_free_cancel", "raw");
    url.addParam("export", 1);
    url.popup(700, 400, "export des vacations");
  }
};
