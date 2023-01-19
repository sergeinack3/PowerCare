/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Soins = {
  inModal:                 false,
  callbackClose:           null,
  dossier_addictologie_id: null, //Si on est dans le module addictologie ou non (défaut : non)

  filterFullName: function (input, table, other_view) {
    table = table ? $(table) : input.up('table');
    table.select('tr').invoke('show');

    var term = $V(input);
    if (!term) {
      return;
    }

    var view = '.CPatient-view';

    if (other_view) {
      view = 'td > a > span.CPatient-view';
    }

    table.select(view).each(
      function (e) {
        if (!e.innerHTML.like(term)) {
          e.up('tr').hide();
        }
      }
    );
  },

  selectServices: function (view, ajax_request, show_np) {
    new Url('hospi', 'ajax_select_services')
      .addParam('view', view)
      .addParam('ajax_request', ajax_request)
      .addParam('show_np', Object.isUndefined(show_np) ? 1 : show_np)
      .requestModal(null, null, {maxHeight: '90%'});
  },

  reponsableJour: function (date, service_id, name_form) {
    new Url('hospi', 'vw_edit_responsable_jour')
      .addParam('date', date)
      .addParam('service_id', service_id)
      .requestModal(
        400, 300,
        {
          onClose: function () {
            if (name_form) {
              getForm(name_form).submit();
            } else {
              document.location.reload();
            }
          }
        }
      );
  },

  addReponsableJour: function (form) {
    return onSubmitFormAjax(
      form,
      {
        onComplete: function () {
          Soins.refreshModalReponsableJour($V(form.date), $V(form.service_id));
        }
      }
    );
  },

  refreshModalReponsableJour: function (date, service_id) {
    new Url('hospi', 'vw_edit_responsable_jour')
      .addParam('date', date)
      .addParam('service_id', service_id)
      .requestUpdate('modale_edit_responsable_jour');
  },

  paramUserSejour: function (sejour_id, service_id, name_form, date) {
    new Url('planningOp', 'vw_affectations_sejour')
      .addParam('sejour_id', sejour_id)
      .addParam('service_id', service_id)
      .addParam('date', date)
      .modal(
        {
          width:   '50%', height: '70%',
          onClose: function () {
            if (!name_form || name_form != 'no_refresh') {
              if (name_form == "refresh_list_sejours") {
                PersonnelSejour.refreshListeSoignant();
              } else if (name_form) {
                getForm(name_form).submit();
              } else {
                refreshLineSejour(sejour_id);
              }
            }
          }
        }
      );
  },

  showTasks: function (element, tooltip_id, sejour_id, source, unfinished_only) {
    new Url('soins', 'ajax_vw_tasks_sejour')
      .addParam('sejour_id', sejour_id)
      .addParam('mode_realisation', true)
      .addParam('source', source)
      .addParam('unfinished_only', (unfinished_only) ? unfinished_only : 1)
      .requestUpdate(tooltip_id, {
        onComplete: function () {
          ObjectTooltip.createDOM(element, tooltip_id, {duration: 0});
        }
      });
  },

  showTasksNotCreated: function (element, tooltip_id, sejour_id) {
    new Url('soins', 'ajax_vw_lines_vithout_task')
      .addParam('sejour_id', sejour_id)
      .requestUpdate(tooltip_id, {
        onComplete: function () {
          ObjectTooltip.createDOM(element, tooltip_id, {duration: 0});
        }
      });
  },

  feuilleTransmissions: function (service_id) {
    new Url('soins', 'vw_feuille_transmissions')
      .addParam('service_id', service_id)
      .popup(900, 600);
  },

  reloadSejoursReeducation: function (sejour_id) {
    new Url('soins', 'ajax_list_sejours_reeducation')
      .addFormData(getForm('filterSejoursReeducation'))
      .addNotNullParam('sejour_id', sejour_id)
      .requestUpdate(sejour_id ? ('line_sejour_' + sejour_id) : 'sejours_area');
  },

  traiterAlerteReeducation: function (alerte_id, sejour_id) {
    var form = getForm('handleAlerte');
    $V(form.alert_id, alerte_id);

    onSubmitFormAjax(form, Soins.reloadSejoursReeducation.curry(sejour_id));
  },

  /**
   * Affiche les séjours à imprimer
   *
   * @param {date} date            date du jour souhaité
   * @param {string} content_class Objet concerné par le filtre
   * @param {string} content_id    ID de l'objet
   * @param {bool} show_affectation
   * @param {bool} only_non_checked
   */
  printDisplayedSejours: function (date, content_class, content_id, show_affectation, only_non_checked) {
    new Url('soins', 'vwSejours')
      .addParam("date", date)
      .addParam(content_class === 'CMediusers' ? "praticien_id" : 'function_id', content_id)
      .addParam('show_affectation', show_affectation)
      .addParam('only_non_checked', only_non_checked)
      .addParam('services_ids', null)
      .addParam('service_id', null)
      .addParam('mode', 'day')
      .addParam("print", true)
      .popup(800, 600);
  },

  viewBilanService: function (service_id, date) {
    new Url('hospi', 'vw_bilan_service')
      .addParam('service_id', service_id)
      .addParam('date', date)
      .popup(800, 500, 'Bilan par service');
  },
  /**
   * Ask to go to an external service
   *
   * @param affectation_id
   * @param from_placement
   */
  askDepartEtablissement: function (affectation_id, from_placement) {
    new Url('hospi', 'ajax_vw_depart_etablissement')
      .addParam('affectation_id', affectation_id)
      .addParam('from_placement', from_placement)
      .requestModal(800, 400);
  },

  askRetourEtablissement: function (affectation_id, affectation_perm_id, ask_cloture_sejour, from_placement) {
    if (ask_cloture_sejour && !confirm($T('CSejour-Ask close and create new sejour'))) {
      return;
    }

    new Url('hospi', 'ajax_retour_etablissement')
      .addParam('affectation_id', affectation_id)
      .addParam('affectation_perm_id', affectation_perm_id)
      .addParam('from_placement', from_placement)
      .requestModal(800, 400);
  },

  editTask: function (task_id, sejour_id, prescription_line_element_id) {
    new Url('soins', 'ajax_modal_task')
      .addParam("task_id", task_id)
      .addParam("sejour_id", sejour_id)
      .addParam("prescription_line_element_id", prescription_line_element_id)
      .requestModal(600, 350)
  },

  editRDV: function (patient_id, sejour_id, prescription_line_element_id) {
    new Url('cabinet', 'edit_planning')
      .addParam('consultation_id', null)
      .addParam('sejour_id', sejour_id)
      .addParam('pat_id', patient_id)
      .addParam('line_element_id', prescription_line_element_id)
      .addParam('dialog', 1)
      .modal(
        {
          width:   1000, height: 700,
          onClose: function () {
            if (window.PlanSoins) {
              PlanSoins.loadTraitement.curry(sejour_id, PlanSoins.date, '', 'administration');
            }
          }
        }
      );
  },

  refreshTask: function (prescription_line_element_id) {
    new Url('soins', 'ajax_update_task_icon')
      .addParam('prescription_line_element_id', prescription_line_element_id)
      .requestUpdate('show_task_' + prescription_line_element_id);
  },

  showModalAllTrans: function (sejour_id) {
    Soins.inModal = true;
    Soins.loadSuivi(sejour_id, null, null, null, 1);
  },

  loadSuivi: function (sejour_id, user_id, function_id, cible, show_header, show_cancelled, other_sejour_id) {
    if (!sejour_id) {
      return;
    }

    Soins.updateNbTrans(sejour_id);

    var show_obs = $('_show_obs_view') ? ($('_show_obs_view').checked ? 1 : 0) : null;
    var _degre_obs = $('_degre_obs') ? $('_degre_obs').value : null;
    var _type_obs = $('_type_obs') ? $('_type_obs').value : null;
    var _etiquette_obs = $('_etiquette_obs') ? $('_etiquette_obs').value : null;
    var show_trans = $('_show_trans_view') ? ($('_show_trans_view').checked ? 1 : 0) : null;
    var _lvl_trans = $('_lvl_trans') ? $('_lvl_trans').value : null;
    var show_const = $('_show_const_view') ? ($('_show_const_view').checked ? 1 : 0) : null;
    var show_adm_cancelled = $('_show_adm_cancelled_view') ? ($('_show_adm_cancelled_view').checked ? 1 : 0) : null;
    var only_macrocible = $('_only_macrocible_view') ? ($('_only_macrocible_view').checked ? 1 : 0) : null;
    var show_diet = $('_show_diet_view') ? ($('_show_diet_view').checked ? 1 : 0) : null;
    var show_rdv_externe = $('_show_rdv_externe_view') ? ($('_show_rdv_externe_view').checked ? 1 : 0) : null;
    var show_call = $('_show_call_view') ? ($('_show_call_view').checked ? 1 : 0) : null;

    var urlSuivi = new Url('hospi', 'httpreq_vw_dossier_suivi');

    urlSuivi.addParam('sejour_id', sejour_id);
    urlSuivi.addParam('user_id', user_id);
    urlSuivi.addParam('function_id', function_id);
    urlSuivi.addParam('cible', cible);
    urlSuivi.addNotNullParam('other_sejour_id', other_sejour_id);

    if (show_obs != null) {
      urlSuivi.addParam('_show_obs', show_obs);
    }

    if (_degre_obs != null) {
      urlSuivi.addParam('_degre_obs', _degre_obs);
    }

    if (_type_obs != null) {
      urlSuivi.addParam('_type_obs', _type_obs);
    }

    if (_etiquette_obs != null) {
      urlSuivi.addParam('_etiquette_obs', _etiquette_obs);
    }

    if (show_trans != null) {
      urlSuivi.addParam('_show_trans', show_trans);
    }

    if (!Object.isUndefined(_lvl_trans)) {
      urlSuivi.addParam('_lvl_trans', _lvl_trans);
    }

    if (show_const != null) {
      urlSuivi.addParam('_show_const', show_const);
    }

    if (!Object.isUndefined(show_header) && show_header !== "" && show_header != null) {
      urlSuivi.addParam('show_header', show_header);
    }

    if (!Object.isUndefined(show_cancelled)) {
      urlSuivi.addParam('show_cancelled', show_cancelled);
    }

    if (show_adm_cancelled != null) {
      urlSuivi.addParam('show_adm_cancelled', show_adm_cancelled);
    }

    if (only_macrocible != null) {
      urlSuivi.addParam('only_macrocible', only_macrocible);
    }

    if (show_diet != null) {
      urlSuivi.addParam('_show_diet', show_diet);
    }

    if (show_rdv_externe != null) {
      urlSuivi.addParam('show_rdv_externe', show_rdv_externe);
    }

    if (show_call != null) {
      urlSuivi.addParam('show_call', show_call);
    }

    if (Soins.inModal) {
      if (Soins.modalSuivi) {
        Soins.modalSuivi.modalObject.close();
        // Le onClose remet à false le flag, on le repasse donc à true, car
        // on réouvre la modale des transmissions
        Soins.inModal = true;
      }

      Soins.modalSuivi = urlSuivi.requestModal('100%', '100%', {
        onClose: function () {
          Soins.inModal = false;
          Soins.loadLiteSuivi(sejour_id);
          Soins.compteurAlertesObs(sejour_id);
        }
      });
    } else {
      if ($('dossier_suivi_lite')) {
        Soins.loadLiteSuivi(sejour_id);
      } else {
        urlSuivi.requestUpdate("dossier_suivi");
      }
    }
  },

  loadLiteSuivi: function (sejour_id) {
    new Url('soins', 'ajax_vw_dossier_suivi_lite')
      .addParam('sejour_id', sejour_id)
      .requestUpdate('dossier_suivi_lite');
  },

  updateNbTrans: function (sejour_id) {
    new Url('hospi', 'ajax_count_transmissions')
      .addParam('sejour_id', sejour_id)
      .requestJSON(
        function (count) {
          try {
            if ($('dossier_suivi')) {
              Control.Tabs.setTabCount('dossier_suivi', count);
            }
          } catch (e) {
          }
        }
      );
  },

  showModalTasks: function (sejour_id) {
    Soins.updateTasks(sejour_id);
    Modal.open('tasks', {
      showClose: true, width: 800, height: 600, onClose: function () {
        if (window.PlanSoins) {
          PlanSoins.reloadSuiviSoin(sejour_id);
        }
      }
    });
  },

  /**
   * Show external appointment list in care folder
   *
   * @param sejour_id
   */
  showRDVExternal: function (sejour_id) {
    new Url('soins', 'ajax_vw_rdv_externe')
      .addParam('sejour_id', sejour_id)
      .requestUpdate('rdv_externe');
  },

  /**
   * Show external appointment list
   *
   * @param sejour_id
   * @param light
   */
  showModalRDVExternes: function (sejour_id, light) {
    Soins.showRDVExternal(sejour_id);
    Modal.open('rdv_externe', {
      showClose: true, showReload: true, width: 800, height: 600, onClose: function () {
        if (light) {
          Soins.refreshRDVExternesLight(sejour_id, light);
        } else if (window.PlanSOins) {
          PlanSoins.reloadSuiviSoin(sejour_id);
        }
      }
    });
  },

  /**
   * Show external appointment list
   *
   * @param sejour_id
   * @param light
   */
  refreshRDVExternesLight: function (sejour_id, light) {
    new Url('soins', 'ajax_vw_rdv_externe')
      .addParam('sejour_id', sejour_id)
      .addParam('light', light)
      .requestUpdate('dossier_suivi_rdv_externe');
  },

  updateObjectifs: function (sejour_id) {
    new Url('soins', 'ajax_vw_objectifs')
      .addParam('sejour_id', sejour_id)
      .requestUpdate('objectif_soin');
  },

  editObjectif: function (objectif_soin_id, sejour_id) {
    new Url('soins', 'ajax_form_objectif')
      .addParam('objectif_soin_id', objectif_soin_id)
      .addParam('sejour_id', sejour_id)
      .requestModal(
        '80%', '95%',
        {
          onClose: function () {
            if (Soins.dossier_addictologie_id) {
              Control.Modal.refresh();
            } else {
              Soins.updateObjectifs(sejour_id);
            }
          }
        }
      );
  },

  addTransmission: function (sejour_id, user_id, transmission_id, object_id, object_class, libelle_ATC, cible_id, refreshTrans, update_plan_soin, focus_area, select_diet) {
    var url = new Url('hospi', 'ajax_transmission');
    url.addParam('sejour_id', sejour_id);
    url.addParam('user_id', user_id);
    if (refreshTrans) {
      url.addParam('refreshTrans', refreshTrans);
    }
    if (update_plan_soin) {
      url.addParam('update_plan_soin', update_plan_soin);
    }

    if (transmission_id != undefined) {
      // Plusieurs transmissions
      if (typeof (transmission_id) == 'object') {
        $H(transmission_id).each(function (trans) {
          url.addParam(trans['0'], trans['1']);
        });
      } else {
        url.addParam('transmission_id', transmission_id);
      }
    }
    if (object_id != undefined && object_class != undefined) {
      url.addParam('object_id', object_id);
      url.addParam('object_class', object_class);
    }
    if (libelle_ATC != undefined) {
      url.addParam('libelle_ATC', libelle_ATC);
    }
    url.addParam('cible_id', cible_id);
    url.addParam('focus_area', focus_area);
    url.addParam('select_diet', select_diet);
    url.requestModal('90%', '95%');
  },

  addMacrocible: function (sejour_id, macrocible_id, cible_id) {
    new Url('hospi', 'ajax_macrocible')
      .addParam('sejour_id', sejour_id)
      .addNotNullParam('macrocible_id', macrocible_id)
      .addParam('cible_id', cible_id)
      .requestModal(800, 400);
  },

  loadSuiviClinique: function (sejour_id, modal) {
    var url = new Url('soins', 'ajax_vw_suivi_clinique');
    url.addParam('sejour_id', sejour_id);
    if (modal == 1 || window.urlSuiviClinique) {
      if (window.urlSuiviClinique) {
        window.urlSuiviClinique.modalObject.close();
      }
      window.urlSuiviClinique = url;
      url.requestModal(1100, null, {
        onClose: function () {
          window.urlSuiviClinique = null;
        }
      });
    } else {
      url.requestUpdate('suivi_clinique');
    }
  },

  createConsultEntree: function (close_modal) {
    var form = getForm('addConsultation');
    $V(form.type, 'entree');
    onSubmitFormAjax(form, close_modal ? Control.Modal.close : null);
    $V(form.type, "");
  },

  /**
   *
   * @param sejour_id  id du sejour
   * @param consult_id id de la consultation
   * @param fragment
   * @param area_focus
   * @param callback   fonction a exécuter à la fermeture de la modal
   */
  modalConsult: function (sejour_id, consult_id, fragment, area_focus, callback) {
    new Url('cabinet', 'ajax_short_consult')
      .addParam('sejour_id', sejour_id)
      .addParam('consult_id', consult_id)
      .addParam('area_focus', area_focus)
      .setFragment(fragment)
      .modal({
        width: '90%', height: '98%', onClose: function () {
          Soins.loadSuivi(sejour_id);

          callback = callback ? callback : Soins.callbackClose;
          if (Object.isFunction(callback)) {
            callback();
          }
        }
      });
  },

  addObservation: function (sejour_id, user_id, observation_id, select_diet) {
    var url = new Url('hospi', 'ajax_observation');
    url.addParam('sejour_id', sejour_id);
    url.addParam('user_id', user_id);
    url.addParam('select_diet', select_diet);
    if (observation_id != undefined) {
      url.addParam('observation_id', observation_id);
    }
    url.requestModal('95%', '95%', {showClose: false});
  },

  deleteObservation: function (button, callback) {
    var form = button.form;

    Modal.confirm($T("CObservationMedicale-delete"),
      {
        onOK: function () {
          $V(form.del, '1');
          callback(form, 1);
        }
      });
  },

  editReevalObjectif: function (objectif_reeval_id, objectif_soin_id) {
    new Url('soins', 'ajax_edit_reeval_objectif')
      .addParam('objectif_reeval_id', objectif_reeval_id)
      .addParam('objectif_soin_id', objectif_soin_id)
      .requestModal('60%', '60%');
  },

  updateReevals: function (objectif_soin_id) {
    new Url('soins', 'ajax_vw_reevaluations_objectif')
      .addParam('objectif_soin_id', objectif_soin_id)
      .requestUpdate('reevals-CObjectifSoin-' + objectif_soin_id);
  },

  printObjectifsSoins: function (sejour_id) {
    new Url('soins', 'print_objectifs_soins')
      .addParam('sejour_id', sejour_id)
      .addParam('dossier_addictologie_id', Soins.dossier_addictologie_id)
      .popup(800, 800);
  },

  /**
   * Show filter or print all external rdv
   *
   * @param print
   * @param form_filter
   */
  printAllExternalRDV: function (print, form_filter) {
    var form = getForm('updateActivites');
    var url = new Url('soins', 'print_all_rdv_externes');
    url.addParam('print', print);
    url.addParam('service_id', [$V(form.service_id)].flatten().join(','));

    if (form_filter) {
      url.addFormData(form_filter);
    }

    if (!print) {
      url.requestModal(400, null);
    } else {
      url.popup(900, 600);
    }
  },

  loadObservations: function (sejour_id, type, other_sejour_id, function_id) {
    if (!$('obs')) {
      return;
    }

    new Url('soins', 'ajax_list_observations')
      .addParam('sejour_id', sejour_id)
      .addNotNullParam('other_sejour_id', other_sejour_id)
      .addNotNullParam('function_id', function_id)
      .addNotNullParam('type', type)
      .requestUpdate('obs');
  },

  editRDVExterne: function (rdv_externe_id, sejour_id) {
    new Url('soins', 'ajax_edit_rdv_externe')
      .addParam('rdv_externe_id', rdv_externe_id)
      .addParam('sejour_id', sejour_id)
      .requestModal(600, 450);
  },

  updateTasks: function (sejour_id) {
    new Url('soins', 'ajax_vw_tasks_sejour')
      .addParam('sejour_id', sejour_id)
      .requestUpdate('tasks');
  },

  openSurveillanceTab: function () {
    var elt = $$('a[href="#constantes-medicales"]')[0];
    elt.click();
  },

  toggleLockCible: function (transmission_id, lock, sejour_id) {
    var form = getForm("lockTransmission");
    $V(form.transmission_medicale_id, transmission_id);
    $V(form.locked, lock);
    onSubmitFormAjax(form, {
      onComplete: function () {
        Soins.loadSuivi(sejour_id);
      }
    });
  },

  showTrans: function (transmission_id, from_compact) {
    new Url('hospi', 'ajax_list_locked_trans')
      .addParam('transmission_id', transmission_id)
      .addParam("from_compact", from_compact)
      .requestModal(850, null, {maxHeight: '550'});
  },

  mergeTrans: function (transmissions_ids) {
    new Url('system', 'object_merger')
      .addParam('objects_class', 'CTransmissionMedicale')
      .addParam('objects_id', transmissions_ids)
      .popup(800, 600, 'merge_transmissions');
  },

  compteurAlertesObs: function (sejour_id) {
    new Url('hospi', 'ajax_count_alert_obs', 'raw')
      .addParam("sejour_id", sejour_id)
      .requestJSON(
        function (count) {
          var spans_ampoule = $$('.span-alerts-medium-observation-CSejour-' + sejour_id);

          spans_ampoule.each(
            function (span) {
              if (count) {
                span.show();
                span.down('span').innerHTML = count;
              } else {
                span.hide();
              }
            }
          );
        }
      );
  },

  loadSuiviSoins:               function (sejour_id, date) {
    new Url('soins', 'ajaxViewDossierSuivi')
      .addParam('sejour_id', sejour_id)
      .addNotNullParam('date', date)
      .requestUpdate('dossier_traitement');
  },
  /**
   * Open the Stay status in popup
   *
   * @param sejour_id
   */
  popEtatSejour:                function (sejour_id) {
    new Url("hospi", "vw_parcours")
      .addParam("sejour_id", sejour_id)
      .requestModal(1000, 700, $T('CSejour-_etat'));
  },
  /**
   * Impression des séjours sans sortie reelle en mode dégradé
   */
  printSejoursSansSortieReelle: function () {
    var real_exit = $$('.real-exit');
    // On ajoute la classe 'not-printable' sur les sejours avec une sortie reelle
    real_exit.invoke('addClassName', 'not-printable');
    // On enleve la classe 'modal_view' car elle est surchargée par la fonction !important
    real_exit.invoke('removeClassName', 'modal_view');
    window.print();
    real_exit.invoke('addClassName', 'modal_view');
    real_exit.invoke('removeClassName', 'not-printable');
  },

  /**
   * Recharge les infos du patient
   *
   * @param patient_id
   */
  reloadPatientInfosBanner: function(patient_id) {
    new Url('soins', 'reloadPatientBanner')
      .addParam("patient_id", patient_id)
      .requestUpdate('infos_patient_soins');
  },

  /**
   * Open full page modal of the stay forms
   *
   * @param sejour_id
   * @param detail
   */
  openFormsWithSejourContext: function (sejour_id) {
    new Url('soins', 'displayFormsWithContext')
      .addParam('sejour_id', sejour_id)
      .requestModal('100%', '100%');
  },
};
