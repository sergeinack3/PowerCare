/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SalleOp = window.SalleOp || {
  operation_id:   null,
  sejour_id:      null,
  praticien_id:   null,
  operateur_ids:  null,
  date:           null,
  salle_id:       null,
  listOpUrl:      null,
  plages_by_prat: false,

  printFicheAnesth: function (dossier_anesth_id, operation_id) {
    new Url('cabinet', 'print_fiche')
      .addParam('dossier_anesth_id', dossier_anesth_id)
      .addParam("operation_id", operation_id)
      .popup(700, 500, 'printFiche');
  },

  loadPosesDispVasc: function () {
    new Url('planningOp', 'ajax_list_pose_disp_vasc')
      .addParam('operation_id', this.operation_id)
      .addParam('sejour_id', this.sejour_id)
      .addParam('operateur_ids', this.operateur_ids)
      .requestUpdate('list-pose-dispositif-vasculaire');
  },

  reloadBloodSalvage: function () {
    new Url('bloodSalvage', 'httpreq_vw_bloodSalvage')
      .addParam('op', this.operation_id)
      .requestUpdate('bloodsalvage_form');
  },

  reloadImeds: function () {
    new Url('Imeds', 'httpreq_vw_sejour_results')
      .addParam('sejour_id', this.sejour_id)
      .requestUpdate('Imeds_tab');
  },

  reloadTimingTab: function () {
    this.reloadTiming();
    this.reloadPersonnel();
  },

  reloadTiming: function () {
    if (!window.ActesCCAM) {
      return;
    }
    new Url('salleOp', 'httpreq_vw_timing')
      .addParam('operation_id', this.operation_id)
      .addParam('submitTiming', 'submitTiming')
      .requestUpdate('timing', ActesCCAM.refreshList.curry(this.operation_id, this.praticien_id));
  },

  reloadPersonnel: function (operation_id) {
    new Url('salleOp', 'httpreq_vw_personnel')
      .addParam('operation_id', operation_id || this.operation_id)
      .requestUpdate('listPersonnel');
  },

  infoExamen:     function (field) {
    var buttons = field.up('td').select('button');

    buttons.each(function (_button) {
      _button.style.visibility = 'hidden'
    });

    if ($V(field) == 1) {
      var type_examen = field.name;
      new Url('planningOp', 'ajax_info_examen')
        .addParam('operation_id', this.operation_id)
        .addParam('type_examen', type_examen)
        .requestModal(600, 500, {title: $T('COperation-' + type_examen)});
      buttons.each(function (_button) {
        _button.style.visibility = 'visible'
      });
    }

    onSubmitFormAjax(field.form);
  },
  /**
   * Print the block card
   *
   * @param operation_id
   */
  printFicheBloc: function (operation_id) {
    new Url("dPsalleOp", "print_feuille_bloc")
      .addParam("operation_id", operation_id ? operation_id : this.operation_id)
      .popup(700, 700, 'FeuilleBloc');
  },

  printPlanSoins: function () {
    new Url("planSoins", "offline_plan_soins")
      .addParam("sejours_ids", this.sejour_id)
      .addParam("mode_dupa", 1)
      .pop(1000, 600);
  },

  printBon: function (type) {
    new Url('salleOp', 'print_bon', 'raw')
      .addParam('operation_id', this.operation_id)
      .addParam('type', type)
      .popup(1000, 700);
  },

  topHoraires: function (operation_id) {
    new Url('salleOp', 'ajax_tops_horaires')
      .addParam('operation_id', operation_id)
      .requestModal(
        '95%', '80%',
        {
          onClose: function () {
            if (window.SalleOp) {
              SalleOp.reloadTimingTab();
            }
            if (window.HPlanning) {
              HPlanning.display(getForm('timeline_filters'));
            }
          }
        }
      );
  },

  /**
   * Set periodical update if needed, else
   *
   * @param {Element} elt Active tab
   * @param {Url}     url Url to call
   */
  setUpdater:    function (elt, url) {
    var updater_sspi_id = (elt._updater) ? elt._updater.updater.parameters.sspi_id : null;
    var url_sspi_id = url.oParams.sspi_id;

    if (elt.id === 'reveil' && (updater_sspi_id !== url_sspi_id)) {
      elt._periodicallyUpdated = false;

      if (elt._updater) {
        elt._updater.stop();
      }
    }

    if (!elt._periodicallyUpdated) {
      elt._updater = url.periodicalUpdate(elt, {frequency: 90});
      elt._periodicallyUpdated = true;
    } else {
      url.requestUpdate(elt);
    }
  },
  loadDocuments: function (sejour_id, operation_id) {
    new Url("hospi", "httpreq_documents_sejour")
      .addParam("sejour_id", sejour_id)
      .addParam("operation_id", operation_id)
      .addParam("with_patient", 1)
      .addParam("op_with_sejour", 1)
      .requestUpdate("docs");
  },

  manageProtocolesOp: function (operation_id, mode) {
    new Url('planningOp', 'viewMaterielOperation')
      .addParam('operation_id', operation_id ? operation_id : this.operation_id)
      .addParam('mode', mode)
      .requestModal('80%', '80%');
  },

  loadOperation: function (operation_id, tr, load_checklist, fragment_tab) {
    new Url('salleOp', 'ajax_vw_operation')
      .addParam('operation_id', operation_id)
      .addParam('date', SalleOp.date)
      .addParam('salle_id', SalleOp.salle_id)
      .addNotNullParam('fragment', fragment_tab)
      .addNotNullParam('load_checklist', load_checklist)
      .requestUpdate('operation_area');

    if (tr) {
      $('listplages').select('tr').invoke('removeClassName', 'selected');
      tr.addClassName('selected');
    }
  },

  periodicalUpdateListePlages: function (hide_finished) {
    SalleOp.listOpUrl = new Url('salleOp', SalleOp.plages_by_prat ? 'httpreq_liste_op_prat' : 'httpreq_liste_plages')
      .addParam('date', SalleOp.date)
      .addParam('hide_finished', hide_finished);

    SalleOp.listOpUrl.periodicalUpdate('listplages', {frequency: 90});
  },

  refreshListOp: function () {
    if (SalleOp.listOpUrl) {
      SalleOp.listOpUrl.requestUpdate('listplages');
    }
  },

  preparationSalles:        function () {
    new Url('planningOp', 'vw_preparation_salles')
      .requestModal('90%', '90%', {onClose: (this.refreshListOp).bind(this)});
  },
  /**
   * Submit the timing anesth
   *
   * @param form
   */
  submitTimingAnesth:       function (form) {
    onSubmitFormAjax(form, function () {
      reloadAnesth($V(form.operation_id));
    });
  },
  /**
   * Show the intervention information
   *
   * @param intervention_id
   * @param modif_operation
   * @param show_cormack
   * @param type
   * @param open_modal
   */
  showInformations:         function (intervention_id, modif_operation, show_cormack, type, open_modal) {
    var url = new Url("dPsalleOp", "vw_informations");
    url.addParam("intervention_id", intervention_id);
    url.addParam("show_cormack", show_cormack);
    url.addParam("modif_operation", modif_operation);

    if (open_modal) {
      url.requestModal("70%", "90%", {onClose: SalleOp.showAnesthAndCormack.curry(intervention_id, type)});
    } else {
      url.requestUpdate("infos_interv");
    }

  },
  /**
   * Show the intervention type and the Cormack score information
   *
   * @param intervention_id
   * @param type
   */
  showAnesthAndCormack:     function (intervention_id, type) {
    var url = new Url("dPsalleOp", "ajax_vw_type_anesth_cormack");
    url.addParam("intervention_id", intervention_id);
    url.requestUpdate('show_type_anesth_cormack_' + type);
  },
  /**
   * Edit the 'sortie de salle' timing
   *
   * @param operation_id
   */
  editSortieSalle:          function (operation_id, form) {
    var url = new Url("dPsalleOp", "ajax_vw_edit_sortie_salle");
    url.addParam("operation_id", operation_id);
    url.requestModal("30%", "30%", {
      onClose: function () {
        if (form.name.include('anesthTiming')) {
          reloadAnesth(operation_id, 'perop-anesth', 'perop');
        } else if (form.name.include('editSortieBlocOutFrm')) {
          refreshTabReveil('out');
        } else {
          SalleOp.reloadTiming(operation_id);
        }
      }
    });
  },
  /**
   * Reload the prescription anesth
   *
   * @param prescription_id
   * @param operation_id
   */
  reloadPrescriptionAnesth: function (prescription_id, operation_id) {
    reloadPrescription(prescription_id);
    reloadAnesth(operation_id);
  },
  /**
   * Check the operation timings is correct
   *
   * @param entree_sejour
   * @param sortie_sejour
   * @param element
   * @param operation_id
   * @param last_timing
   */
  checkTimingOperation:     function (entree_sejour, sortie_sejour, element, operation_id, last_timing) {
    var timing_entree_salle = null;
    var timing_sortie_salle = null;
    var element_value = element.value ? element.value : new Date();
    var date = new Date(element_value);
    var format_date = date.toLocaleDateTime();
    var type_graph = 'perop';

    if (getForm('timing' + operation_id + '-entree_salle') || getForm('timing' + operation_id + '-sortie_salle')) {
      timing_entree_salle = $V(getForm('timing' + operation_id + '-entree_salle').entree_salle);
      timing_sortie_salle = $V(getForm('timing' + operation_id + '-sortie_salle').sortie_salle);
    }

    if ($('surveillance_preop') && $('surveillance_preop').hasClassName("active")) {
      type_graph = 'preop';
    } else if ($('surveillance_sspi') && $('surveillance_sspi').hasClassName("active")) {
      type_graph = 'sspi';
    }

    if (element_value && entree_sejour && sortie_sejour && (entree_sejour > element_value || sortie_sejour < element_value)) {
      alert($T('COperation-msg-Please note the date entered %s is not within the limits of the stay', format_date));

      if (element.form.name.include('anesthTiming')) {
        reloadAnesth(operation_id, type_graph + '-anesth', type_graph);
      } else if (element.form.name.include('editEntreeReveilReveilFrm')) {
        refreshTabReveil('reveil');
      } else {
        SalleOp.reloadTiming(operation_id);
      }

      return false;
    }

    // Check if the entry is upper than the previous timing
    if (element_value && last_timing && (last_timing > element_value)) {
      alert($T('COperation-msg-Please the entry is lower than the previous timing'));
      if (element.form.name.include('anesthTiming')) {
        reloadAnesth(operation_id, type_graph + '-anesth', type_graph);
      } else if (element.form.name.include('editEntreeReveilReveilFrm')) {
        refreshTabReveil('reveil');
      } else {
        SalleOp.reloadTiming(operation_id);
      }

      return false;
    }

    // Check if the entering room is ok
    if (timing_sortie_salle && !timing_entree_salle && element_value) {
      alert($T('COperation-msg-Please note please enter a room entrance before entering a room exit'));
      if (element.form.name.include('anesthTiming')) {
        reloadAnesth(operation_id, type_graph + '-anesth', type_graph);
      } else if (element.form.name.include('editEntreeReveilReveilFrm')) {
        refreshTabReveil('reveil');
      } else {
        SalleOp.reloadTiming(operation_id);
      }

      return false;
    }

    return true;
  },

  /**
   * Rafraichit le personnel dans le partogramme
   *
   * @param operation_id
   * @param modif_operation
   */
  refreshPersonnelPartogramme: function(operation_id, modif_operation) {
    new Url("salleOp", "ajax_vw_inc_personnel_partogramme")
      .addParam("operation_id", operation_id)
      .addParam("modif_operation", modif_operation)
      .requestUpdate("personnel_partogramme");
  },

  /**
   * Supprime le personnel dans le partogramme
   *
   * @param form
   * @param options
   * @param salleOp_id
   */
  deletePersonnelPartogramme: function(form, options, salleOp_id) {
    confirmDeletion(form, {options}, function() {
      this.refreshPersonnelPartogramme(salleOp_id)
    }.bind(this))
  },

  /**
   * Enregistre et rafraichie le personnel en salle
   *
   * @param form
   */
  submitPersonnel: function(form){
    return onSubmitFormAjax(form,function () {
      this.refreshPersonnelPartogramme('{{$selOp->_id}}', '{{$modif_operation}}')
    })
  },
};
