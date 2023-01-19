/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PlanningEquipement = {
  equipement_id: null,
  sejour_id:     null,

  showMany: function (equipement_ids) {
    equipement_ids.each(function (equipement_id) {
      $('planning-equipement-' + equipement_id).update('');
      new Url(Planification.current_m, 'ajax_planning_equipement').addParam('equipement_id', equipement_id).requestUpdate('planning-equipement-' + equipement_id);
    });
  },

  show: function (equipement_id, sejour_id) {
    this.equipement_id = equipement_id || this.equipement_id;
    this.sejour_id = sejour_id || this.sejour_id;
    if (!this.equipement_id) {
      return;
    }
    $('planning-equipement').update('');
    new Url(Planification.current_m, 'ajax_planning_equipement').addParam('equipement_id', this.equipement_id).addParam('sejour_id', this.sejour_id).requestUpdate('planning-equipement');
  },

  hide: function () {
    $('planning-equipement').update('<div class="small-info">'+$T('CEquipement.none_selected')+'</div>');
  }
};

PlanningTechnicien = {
  surveillance: 0,
  kine_id:      null,
  sejour_id:    null,
  height:       null,
  selectable:   null,
  large:        null,
  show:         function (kine_id, surveillance, sejour_id, height, selectable, large, print, current_day) {
    this.kine_id = kine_id || this.kine_id;
    this.sejour_id = sejour_id || this.sejour_id;
    this.surveillance = surveillance || this.surveillance;
    this.height = height || this.height;
    this.selectable = selectable || this.selectable;
    this.large = large || this.large;

    if (!this.kine_id) {
      return;
    }
    var planning_technicien = $('planning-technicien');
    planning_technicien.update('');

    new Url(Planification.current_m, 'ajax_planning_technicien')
      .addParam('kine_id', this.kine_id)
      .addParam('surveillance', this.surveillance)
      .addParam('sejour_id', this.sejour_id)
      .addParam('height', this.height)
      .addParam('selectable', this.selectable ? 1 : 0)
      .addParam('large', this.large ? 1 : 0)
      .addParam('print', print ? 1 : 0)
      .addParam('current_day', current_day ? 1 : 0)
      .requestUpdate('planning-technicien', {
          onComplete: function () {
            if (print) {
              planning_technicien.select('.planning col')[2].style.width = '1px';
              planning_technicien.select('.week-container')[0].style.overflowY = 'visible';

              for (var i = 0; i < 8; i++) {
                $$('.hour-0' + i)[0].hide();
              }
              for (var j = 18; j < 24; j++) {
                $$('.hour-' + j)[0].hide();
              }

            }
          }
        }
      );
  },

  hide: function () {
    $('planning-technicien').update('');
  },

  /**
   * Toggle between 2 plannings
   *
   * @param form
   */
  toggle: function (form) {
    this.surveillance = this.surveillance == 1 ? 0 : 1;

    if (form) {
      $V(form.surveillance, this.surveillance);
    }

    PlanningTechnicien.show(this.kine_id, this.surveillance, this.sejour_id);
  },

  /**
   * Print planning therapist
   *
   * @param current_day
   * @param surveillance
   */
  print: function (current_day, surveillance) {
    Control.Modal.close();
    var url = new Url(Planification.current_m, 'print_planning_technicien');
    url.addParam('kine_id', this.kine_id);
    url.addParam('surveillance', surveillance);
    if (current_day) {
      url.addParam('current_day', 1);
      url.addParam('day_used', current_day);
    }
    url.popup(700, 700, $T('ssr-planning_reeduc'));
  },

  showEvtsOldNotValide: function (kine_id) {
    new Url('ssr', 'vw_evts_kine_not_valide')
      .addParam('kine_id', kine_id)
      .requestModal();
  }
};

Planification = window.Planification || {
  sejour_id:  null,
  selectable: null,
  height:     null,
  current_m:  'ssr',
  scroll:     function () {
    $('planification').scrollTo();
  },

  refreshActivites: function (sejour_id) {
    if (!$('activites-sejour')) {
      return;
    }
    this.sejour_id = sejour_id || this.sejour_id;
    new Url(Planification.current_m, 'ajax_activites_sejour').addParam('sejour_id', this.sejour_id).addParam('current_m', Planification.current_m).requestUpdate('activites-sejour');
  },

  refreshSejour: function (sejour_id, selectable, height, print, large, current_day, id_planning) {
    var planning_sejour = id_planning ? $(id_planning) : $('planning-sejour');
    if (!planning_sejour) {
      return;
    }
    this.sejour_id = sejour_id || this.sejour_id;
    this.selectable = selectable || this.selectable;
    this.height = height || this.height;

    planning_sejour.update('<div class="small-info">'+$T('ssr-dbl_click_acces_planning')+'</div>');

    new Url(Planification.current_m, 'ajax_planning_sejour').addParam('sejour_id', this.sejour_id).addParam('selectable', this.selectable ? 1 : 0).addParam('height', this.height).addParam('print', print ? 1 : 0).addParam('large', large ? 1 : 0).addParam('current_day', current_day ? 1 : 0).requestUpdate('planning-sejour', {
      onComplete: function () {
        if (print) {
          planning_sejour.select('.planning col')[2].style.width = '1px';
          planning_sejour.select('.week-container')[0].style.overflowY = 'visible';

          for (var i = 0; i < 8; i++) {
            $$('.hour-0' + i)[0].hide();
          }
          for (var j = 18; j < 24; j++) {
            $$('.hour-' + j)[0].hide();
          }
        }
      }
    });
  },

  refresh: function (sejour_id) {
    this.sejour_id = sejour_id || this.sejour_id;
    Planification.refreshActivites(this.sejour_id);
    Planification.refreshSejour(this.sejour_id, true);
  },
  /**
   * Show the planning week
   *
   * @param date
   * @param view
   * @param sejour_id
   */
  showWeek: function (date, view, sejour_id) {
    Planification.sejour_id = sejour_id;

    var url = new Url(Planification.current_m, 'ajax_week_changer');
    if (date) {
      url.addParam('date', date);
    }
    url.addParam('view', view);
    url.addParam('sejour_id', sejour_id);
    url.requestUpdate('week-changer', {onComplete: function () {
      if (view == 'planif') {
        Planification.refreshSejour();
        PlanningTechnicien.show();
        PlanningEquipement.show();
      }
      else if (view == 'remplacement') {
        Planification.refreshlistSejour('','kine');
        Planification.refreshlistSejour('','reeducateur');
        $('replacement-kine').update();
        $('replacement-reeducateur').update();
      }
      else if (view == 'kine' || view == 'plateau') {
        Planification.onCompleteShowWeek();
      }
      else if (view == 'groupe_patient') {
        GroupePatient.date_planning = date;
        GroupePatient.refreshPlanning(null, date);
      }
    }});
  },
  printPlanningSejour:    function (sejour_id, current_day, use_pdf, full_screen) {
    var url = new Url(Planification.current_m, 'print_planning_sejour');
    if (use_pdf == '1') {
      url = new Url(Planification.current_m, 'print_planning_sejour', 'raw');
    }
    url.addParam('sejour_id', sejour_id);
    if (current_day) {
      url.addParam('current_day', 1);
      url.addParam('day_used', current_day);
    }
    url.addNotNullParam('full_screen', full_screen);

    if (full_screen) {
      url.popup('100%', '100%', 'Planning du patient');
    } else {
      url.popup(use_pdf ? 1000 : 700, 700, 'Planning du patient');
    }
  },
  printAllPlanningSejour: function (date, current_day) {
    new Url(Planification.current_m, 'print_planning_all_sejour', 'raw')
      .addParam('date', date)
      .addParam('current_day', current_day)
      .popup(1000, 700, 'Planning du patient');
  },

  checkPlanificationPatient: function (form) {
    if ((form.select('input.days:checked').length == 0) || !$V(form._heure_deb) || !$V(form.duree)) {
      $('warning_conflit_planification').innerHTML = '';
      return false;
    }
    $V(form._type_seance, $V(form.type_seance));
    new Url('ssr', 'ajax_alert_conflit_planification')
      .addFormData(form)
      .requestUpdate('warning_conflit_planification');
  },

  showPlanificationPatient: function (sejour_id) {
    new Url(Planification.current_m, 'vw_planification_sejour')
      .addParam('sejour_id', sejour_id)
      .requestModal('90%', '90%');
  },

  searchConflitsRemplacement: function (remplacer_id, conge_id, sejour_id) {
    Planification.refreshReplacerPlanning(remplacer_id);
    new Url('ssr', 'ajax_alert_conflit_remplacement')
      .addParam('remplacer_id', remplacer_id)
      .addParam('plage_id', conge_id)
      .addParam('sejour_id', sejour_id)
      .requestUpdate('warning_conflit_remplacement');
  },
  toggleOtherCsarr:           function (elem, uniq_id) {
    var form = elem.form;
    var id_csarr = uniq_id ? uniq_id : '';
    $('other_csarr' + id_csarr).setVisible($V(elem));
    $V(form.code_csarr, '');
    $(form.code_csarr).tryFocus();
  },
  toggleOtherPresta:          function (elem, uniq_id) {
    var form = elem.form;
    var id_presta = uniq_id ? uniq_id : '';
    $('other_presta_ssr' + id_presta).setVisible($V(elem));
    $V(form.code_presta_ssr, '');
    $(form.code_presta_ssr).tryFocus();
  },
  removeCodes:                function (form) {
    form.select('input[name^=\'csarrs\']').each(function (e) {
      e.checked = false;
    });
  },
  countCodesCsarr:            function (form) {
    var csarr_count = $V(form.code_csarr) ? 1 : 0;
    csarr_count += form.select('input.checkbox-csarrs:checked').length;
    csarr_count += form.select('input.checkbox-other-csarrs').length;

    $$('input[name=\'type_seance\']').each(function (input) {
      input.disabled = csarr_count != 0 ? true : false;
    });
  },
  updateFieldCodesSSR:        function (selected, type, uniq_id, trame) {
    var type_pl = type + 's';
    if (type == 'presta_ssr') {
      type_pl = 'prestas_ssr';
    }
    var name_form = uniq_id ? 'editEvenementSSR-' + uniq_id : 'editEvenementSSR';
    var code_selected = selected;
    if (!Object.isString(selected)) {
      code_selected = selected.childElements()[0].innerHTML;
    }
    var acte_id = type === 'presta_ssr' ? '-' + Math.ceil(Math.random() * 100000) : '';

    var element_quantity = null;

    if (code_selected.className != 'empty') {
      if (type === 'presta_ssr') {
        var type_qte = trame ? 'prestas' : type_pl;
        var element_quantity = DOM.input({
          type:      'number',
          min:       '1',
          id:        name_form + '__' + type_qte + '_quantity[' + code_selected + acte_id + ']',
          name:      '_' + type_qte + '_quantity[' + code_selected + acte_id + ']',
          value:     '1',
          className: 'checkbox-other-' + type_qte + '_quantity',
          style:     'width: 32px;'
        });
      }

      $('other_' + type + (uniq_id ? uniq_id : '')).insert({
        bottom:
          DOM.span({},
            DOM.input({
              type:      'hidden',
              id:        name_form + '__' + type_pl + '[' + code_selected + acte_id + ']',
              name:      '_' + type_pl + '[' + code_selected + acte_id + ']',
              value:     code_selected,
              className: 'checkbox-other-' + type_pl
            }),
            DOM.button({
              className: 'cancel notext',
              type:      'button',
              onclick:   'Planification.deleteCode(this)'
            }),
            DOM.label({}, code_selected), DOM.span({id: '_quantity_' + code_selected + acte_id} , element_quantity ? '(x ' : '')
          )
      });

      $('_quantity_' + code_selected + acte_id).insert(element_quantity);
      $('_quantity_' + code_selected + acte_id).insert(DOM.span({} , element_quantity ? ')' : ''));

      if (type == 'csarr') {
        Planification.countCodesCsarr(getForm(name_form));
      }
      var input = $(name_form + '_code_' + type);
      if (input) {
        input.value = '';
        input.tryFocus();
      }
    }
  },
  deleteCode:                 function (elem) {
    $(elem).up().remove();
    Planification.countCodesCsarr(getForm('editEvenementSSR'));
  },
  /**
   * Calculate the duration of the session
   *
   * @param form
   */
  calculateDuration:          function (form) {
    var duration = 0;
    $V(form.duree, '');

    form.select('input.checkbox-csarrs:checked').each(function (elt) {
      var duree = elt.get('duree');

      duration += duree ? parseInt(duree) : 0;
    });

    $V(form.duree, duration);
  },
  /**
   * Filter the CsARR codes by type seance
   *
   * @param form
   */
  filterCodesCsARR: function (form) {
    $$('.label-csarrs').invoke('hide');
    var type_seance = $V(form.type_seance);

    form.select('input.checkbox-csarrs').each(function (elt) {
      var type_seance_elt = elt.get('type_seance');

      if (!type_seance_elt || (type_seance_elt === type_seance)) {
        elt.up('label').show();
      }
      else {
        elt.up('label').hide();
      }
    });
  },
  /**
   * Refresh the sejour list
   *
   * @param selected_sejour_id
   * @param type
   */
  refreshlistSejour: function (selected_sejour_id, type) {
    var url = new Url(Planification.current_m, "ajax_vw_list_sejours");
    url.addParam("type", type);
    url.requestUpdate("sejours-" + type, {
      onComplete: function () {
        var line = $('replacement-' + type + '-' + selected_sejour_id);
        if (line) {
          line.addUniqueClassName('selected');
        }
      }
    });
  },
  /**
   * Refresh the replacement
   *
   * @param sejour_id
   * @param plage_conge_id
   * @param type
   */
  refreshReplacement: function (sejour_id, plage_conge_id, type) {
    var url = new Url(Planification.current_m, "ajax_vw_replacement");
    url.addParam("sejour_id", sejour_id);
    url.addParam("plage_id", plage_conge_id);
    url.addParam("type", type);
    url.requestUpdate("replacement-" + type);
  },
  /**
   * Refresh the replacer planning
   *
   * @param replacer_id
   */
  refreshReplacerPlanning: function (replacer_id) {
    var url = new Url(Planification.current_m, "ajax_planning_technicien");
    url.addParam("kine_id", replacer_id);
    url.requestUpdate("replacer-planning");
  },
  /**
   * Print the division
   */
  printRepartition: function () {
    var url = new Url(Planification.current_m, "vw_idx_repartition");
    url.addParam("readonly", 1);
    url.popup("Repartition des patients");
  },
  onCompleteShowWeek:         Prototype.emptyFunction
};

// Warning: planning.js has to be included first
PlanningEvent.onMouseOver = function (event) {
  var matches = event.className.match(/CEvenementSSR-([0-9]+)/);
  if (matches) {
    ObjectTooltip.createEx(event, matches[0]);
  }
};

