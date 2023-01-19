/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

GroupePatient = {
  current_m:                   'ssr',
  form:                        null,
  categorie_groupe_patient_id: null,
  tabs_planning_groupe:        null,
  date_planning:               null,
  /**
   * Edit the group category
   *
   * @param categorie_groupe_patient_id
   */
  editGroupCategory: function (categorie_groupe_patient_id) {
    new Url(GroupePatient.current_m, 'ajax_edit_categorie_groupe_patient')
      .addNotNullParam('categorie_groupe_patient_id', categorie_groupe_patient_id)
      .addNotNullParam('current_mod', GroupePatient.current_m)
      .requestModal(null, null, {onClose: GroupePatient.refreshAllPlannings});
  },
  /**
   * Edit the group range
   *
   * @param plage_groupe_patient_id
   * @param categorie_groupe_patient_id
   */
  editGroupPlage: function (plage_groupe_patient_id, categorie_groupe_patient_id) {
    new Url(GroupePatient.current_m, 'ajax_edit_plage_groupe_patient')
      .addNotNullParam('plage_groupe_patient_id', plage_groupe_patient_id)
      .requestModal('50%', '90%', {
        onClose: function () {
          //GroupePatient.tabs_planning_groupe.setActiveTab('planningGroupe-' + categorie_groupe_patient_id);
          GroupePatient.refreshPlanning(categorie_groupe_patient_id);
        }
      });
  },
  /**
   * Refresh the planning view
   *
   * @param categorie_groupe_patient_id
   * @param date
   */
  refreshPlanning: function (categorie_groupe_patient_id, date) {
    var categorie_id = categorie_groupe_patient_id ? categorie_groupe_patient_id : GroupePatient.categorie_groupe_patient_id;
    var day_used = date ? date : GroupePatient.date_planning;

    new Url(GroupePatient.current_m, 'ajax_vw_planning_categorie_groupe_patient')
      .addNotNullParam('categorie_groupe_patient_id', categorie_id)
      .addParam('day_used', day_used)
      .requestUpdate('planningGroupe-' + categorie_id, {
        onComplete: function () {
          ViewPort.SetAvlHeight('planningGroupe-' + categorie_id, 1.00);
          $('planningGroupe-' + categorie_id).removeClassName('y-scroll');
        }
      });
  },
  /**
   * Refresh all plannings
   */
  refreshAllPlannings: function (show_inactive) {
    var form = getForm('groupe_patient_filter');

    new Url(GroupePatient.current_m, 'vw_planning_groupe_patient')
      .addParam('show_inactive', $V(form.show_inactive) ? 1 : 0)
      .requestUpdate('groupe_planning');
  },
  /**
   * Select many days for a range
   *
   * @param form
   */
  selectManyDays: function (form) {
    $V(form.groupe_days, [$V(form._groupe_days)].flatten().join('|'));
  },
  /**
   * Get the prescription's element list
   *
   * @param form
   * @param filter
   * @param element_ids
   */
  elementPrescriptionAutocomplete: function (form, filter, element_ids) {
    var url = new Url('prescription', 'httpreq_do_element_autocomplete');
    url.addParam('category', (GroupePatient.current_m == 'ssr' ? 'kine' : 'psy'));
    if (element_ids) {
      url.addParam('where_clauses[element_prescription_id]', 'IN (' + element_ids.split('|').join(',') + ')');
    }
    url.autoComplete(form.libelle, 'element_prescription_view', {
      dropdown:      true,
      minChars:      0,
      updateElement: function (element) {
        Element.cleanWhitespace(element);
        var dn = element.childNodes;
        var element_id = dn[0].firstChild.nodeValue;
        var element_libelle = element.down('strong').get('libelle');

        if (filter != 1) {
          GroupePatient.bindElementPrescription(element_id, element_libelle);

          $V(form.elements_prescription, [$V(form.elements['element[]'])].flatten().join('|'));
        } else {
          $V(form.libelle, element_libelle);
          $V(form.libelle_element_id, element_id);

          GroupePatient.managePatients($V(form.plage_groupe_patient_id), $V(form.categorie_groupe_patient_id), element_id, 1);
        }
      }
    });
  },
  /**
   * Bind the prescription's element to an object
   *
   * @param element_id
   * @param element_libelle
   */
  bindElementPrescription: function (element_id, element_libelle) {
    var form = getForm('editCategoryGroup');
    var form_name = form.name;

    var li = DOM.li({
        id:        'li_element_' + element_id,
        className: 'tag element_selected',
        style:     'cursor: default;'
      },
      DOM.span({}, element_libelle),
      DOM.i({
        className: 'fas fa-times',
        type:      'button',
        style:     'margin-left: 10px; cursor: pointer;',
        title:     'Supprimer',
        onclick:   "$(this).up('li').remove(); GroupePatient.deleteElementTag();"
      }),
      DOM.input({
        type:  'hidden',
        id:    form_name + "__element_id[" + element_id + "]",
        name:  'element[]',
        value: element_id
      })
    );

    $("show_tags_element").insert(li);

    if ($$('ul#show_tags_element > li').length > 0) {
      $('labelFor_' + form_name + '_elements_prescription').removeClassName('notNull').addClassName('notNullOK');
    }
  },
  /**
   * Remove element of tag
   */
  deleteElementTag: function () {
    var form = getForm('editCategoryGroup');

    $V(form.elements_prescription, [$V(form.elements['element[]'])].flatten().join('|'));

    if ($$('ul#show_tags_element > li').length == 0) {
      $('labelFor_' + form.name + '_elements_prescription').removeClassName('notNullOK').addClassName('notNull');
    }
  },
  /**
   * Manage patients for a group range
   *
   * @param plage_groupe_patient_id
   * @param categorie_groupe_patient_id
   * @param filter_element_id
   * @param close_modal
   * @param is_plage_groupe
   * @param date
   */
  managePatients: function (plage_groupe_patient_id, categorie_groupe_patient_id, filter_element_id, close_modal, is_plage_groupe, date) {
    if (close_modal) {
      Control.Modal.close();
    }

    var day_used = date ? date : GroupePatient.date_planning;

    new Url(GroupePatient.current_m, 'ajax_vw_groupe_patients')
      .addNotNullParam('plage_groupe_patient_id', plage_groupe_patient_id)
      .addParam('filter_element_id', filter_element_id)
      .addParam('plage_date', day_used)
      .requestModal('90%', '90%', {
        onClose: function () {
          if (window.PlanningTechnicien && !is_plage_groupe) {
            PlanningTechnicien.show();
          } else {
            GroupePatient.refreshPlanning(categorie_groupe_patient_id);
          }
        }
      });
  },
  /**
   * Select all checkboxes
   *
   * @param form
   * @param valeur
   * @param classname
   * @param already_exist
   */
  selectCheckboxes: function (form, valeur, classname, already_exist) {
    form.select('.' + classname).each(function (input) {
      if (!input.disabled) {
        $V(input, valeur);
      }
    });
  },
  sortBy:           function (order_col, order_way) {
    new Url(GroupePatient.current_m, 'ajax_vw_groupe_patients')
      .addParam('order_col', order_col)
      .addParam('order_way', order_way)
      .requestUpdate('view_groupe_patients');
  },
  /**
   * Show or Hide events of a patient
   *
   * @param element
   * @param classname
   */
  showEvents: function (element, classname) {
    var tr_elt = element.up('tr');
    var sejour_id = classname.split('_')[2];
    var filter_element_id = $V(element.form.libelle_element_id);

    if ($V(element)) {
      $$('.' + classname).invoke('show');
      $('arrow_show_actes_' + sejour_id).show();
      tr_elt.addClassName('selected');

      if (filter_element_id > 0) {
        $$('.elements_line_' + sejour_id).invoke('hide');
        $$('.element_prescription_' + filter_element_id + '_' + sejour_id).invoke('show');

        if ($$('.elements_line_' + sejour_id + ':not([style="display: none;"])').length == 0) {
          GroupePatient.form.elements['patient_' + sejour_id].up('tr').hide();
        }
      } else {
        $$('.elements_line_' + sejour_id).invoke('show');
      }
    } else {
      $$('.' + classname).invoke('hide');
      $('arrow_show_actes_' + sejour_id).hide();
      tr_elt.removeClassName('selected');
    }
  },
  /**
   * Show or Hide events of a patient with arrow
   *
   * @param element
   * @param classname
   */
  showEventsByArrow: function (element, classname) {
    if (element.hasClassName('fa-arrow-circle-down')) {
      element.removeClassName('fa-arrow-circle-down');
      element.addClassName('fa-arrow-circle-up');
      $$('.' + classname).invoke('show');
    } else {
      element.removeClassName('fa-arrow-circle-up');
      element.addClassName('fa-arrow-circle-down');
      $$('.' + classname).invoke('hide');
    }
  },
  /**
   * Add values in the main input to get in all value selected
   *
   * @param form
   * @param acte_id
   * @param sejour_id
   * @param name
   */
  addValues: function (form, acte_id, sejour_id, name) {
    var input_name_modulateur = name + '_' + acte_id + '_' + sejour_id;
    var input_name_acte = 'acte_csarr_' + acte_id + '_' + sejour_id;
    var modulateur_values = [$V(form.elements[input_name_modulateur])].flatten().join('|');

    if (name == 'modulateurs') {
      if (!form.elements[input_name_modulateur].length) {
        modulateur_values = this.form.elements[input_name_modulateur].checked ? this.form.elements[input_name_modulateur].value : '';
      }
    }

    $$('input[name=' + input_name_acte)[0].writeAttribute('data-' + name, modulateur_values);
  },
  /**
   * Check elements enabled/disabled
   *
   * @param form
   * @param element_name
   */
  checkElementsEnable: function (element, element_name) {
    var acte_id = element_name.split('_')[1];
    var sejour_id = element_name.split('_')[2];
    var number_acte = element.form.select('.show_acte_' + sejour_id + ':checked').length;
    var show_tag_actes = $('show_tag_actes_' + sejour_id);

    if (number_acte) {
      show_tag_actes.innerHTML = $T('CPlageGroupePatient-%s acte', number_acte);
      show_tag_actes.show();
    } else {
      show_tag_actes.innerHTML = "";
      show_tag_actes.hide();
    }

    var elements_arrow = $('order_arrow_' + acte_id + '_' + sejour_id);

    // Show/Hide arrow
    if (element.checked) {
      elements_arrow.show();
    }
    else {
      elements_arrow.hide();
    }

    if ($V(element)) {
      $$('.' + element_name).invoke('enable');
    } else {
      $$('.' + element_name).invoke('disable');
    }
  },
  /**
   * Confirmation to validate all selected patients to associate them with the group range
   *
   * @param form
   */
  confirmValidation: function (form) {
    var count_executant_empty = 0;
    getForm('gestion_groupe_patients').select('.add_acte_csarr:checked').each(function (add_acte_csarr) {
      var select = GroupePatient.form.elements['executant_' + add_acte_csarr.value];

      if (select.up('div').up('tr').visible() && !select.value) {
        count_executant_empty++;
      }
    });
    if (count_executant_empty > 0) {
      return confirm($T('CPlageGroupePatient-no_executant'));
    }

    var manage_patients = {
      plage_groupe_patient_id: 0,
      sejours:                 {}
    };

    if (confirm($T('CPlageGroupePatient-msg-You are about to add patients in this group range Would you like to continue'))) {
      manage_patients.plage_groupe_patient_id = $V(form.plage_groupe_patient_id);

      form.select('.groupe_patient').each(function (element) {
        var already_range = element.get('already_range');

        manage_patients.sejours[element.value] = {};
        manage_patients.sejours[element.value]['already_range'] = already_range;
        manage_patients.sejours[element.value]['checked'] = element.checked ? 1 : 0;
      });

      Object.keys(manage_patients.sejours).each(function (sejour_id) {
        if (manage_patients.sejours[sejour_id]['checked']) {
          form.select('.add_acte_csarr:checked').each(function (elt) {
            var elt_sejour_id = elt.value.split('_')[1];
            var acte_id = elt.value.split('_')[0];

            if (elt_sejour_id === sejour_id) {
              manage_patients.sejours[sejour_id][acte_id] = {
                code:                    elt.get('code'),
                modulateurs:             elt.get('modulateurs') !== "false" ? elt.get('modulateurs') : '',
                duree:                   elt.get('duree'),
                extension:               elt.get('extension'),
                type_seance:             elt.get('type_seance'),
                element_prescription_id: elt.get('element_prescription_id'),
                executant:               elt.get('executant'),
                event_id:                elt.get('event_id'),
                delete:                  0,
                acte_heure_debut:        elt.get('acte_heure_debut'),
                acte_heure_fin:          elt.get('acte_heure_fin')
              };
            }
          });
        }

        // Delete event already save
        form.select('.add_acte_csarr:not(:checked)').each(function (elt) {
          var elt_sejour_id = elt.value.split('_')[1];
          var acte_id = elt.value.split('_')[0];
          var is_selected = elt.get('is_selected');

          if ((elt_sejour_id === sejour_id) && (is_selected == 1)) {
            manage_patients.sejours[sejour_id][acte_id] = {
              code:                    elt.get('code'),
              modulateurs:             elt.get('modulateurs'),
              duree:                   elt.get('duree'),
              extension:               elt.get('extension'),
              type_seance:             elt.get('type_seance'),
              element_prescription_id: elt.get('element_prescription_id'),
              executant:               elt.get('executant'),
              event_id:                elt.get('event_id'),
              delete:                  1,
              acte_heure_debut:        elt.get('acte_heure_debut'),
              acte_heure_fin:          elt.get('acte_heure_fin')
            };
          }
        });
      });

      new Url(GroupePatient.current_m, 'do_gestion_groupe_patients', 'dosql')
        .addParam('manage_patients', Object.toJSON(manage_patients))
        .addParam('plage_date', $V(form.plage_date))
        .requestUpdate('systemMsg', {method: 'post', onComplete: Control.Modal.close});
    }
  },
  /**
   * Edit patients group range
   *
   * @param plage_groupe_patient_id
   * @param plage_date
   */
  editPatientsGroupe: function (plage_groupe_patient_id, plage_date) {
    new Url(GroupePatient.current_m, 'ajax_manage_sejours_associes')
      .addParam('plage_groupe_patient_id', plage_groupe_patient_id)
      .addParam('day_used', plage_date)
      .requestJSON(function (sejours_associes) {
        if (Object.keys(sejours_associes).length) {
          Object.keys(sejours_associes).each(function (sejour_id) {
            var sejour = sejours_associes[sejour_id];

            if (!$$("input[name='patient_" + sejour_id + "']").length) {
              return;
            }

            var patient_checkbox = $$("input[name='patient_" + sejour_id + "']")[0];
            patient_checkbox.checked = true;
            patient_checkbox.onchange();

            Object.keys(sejour).each(function (line_element_id) {
              var line_element = sejours_associes[sejour_id][line_element_id];
              Object.keys(line_element).each(function (event_id) {
                var event = sejours_associes[sejour_id][line_element_id][event_id];

                Object.keys(event).each(function (acte_csarr_id) {
                  var acte_csarr = sejours_associes[sejour_id][line_element_id][event_id][acte_csarr_id];

                  if (!acte_csarr) {
                    return;
                  }

                  if (typeof acte_csarr === 'object') {
                    var acte_csarr_code = acte_csarr['code'].replace("+", "");
                    var acte_checkbox = $$('.acte_csarr_' + acte_csarr_code + '_' + sejour_id)[0];
                    acte_checkbox.checked = true;
                    acte_checkbox.writeAttribute("data-event_id", event_id);
                    acte_checkbox.writeAttribute("data-is_selected", 1);
                    acte_checkbox.onchange();

                    var modulateurs_checkbox = $$('.modulateurs_' + acte_csarr_code + '_' + sejour_id);
                    var duree_input = $$('.duree_' + acte_csarr_code + '_' + sejour_id)[0];
                    var extension_input = $$('.extension_' + acte_csarr_code + '_' + sejour_id)[0];
                    var type_seance_input = $$('.type_seance_' + acte_csarr_code + '_' + sejour_id)[0];
                    var executant_input = $$('.executant_' + acte_csarr_code + '_' + sejour_id)[0];

                    if (modulateurs_checkbox.length) {
                      modulateurs_checkbox.each(function (input) {
                        if (acte_csarr.hasOwnProperty("modulateurs") && acte_csarr['modulateurs'].includes(input.value)) {
                          input.checked = true;
                          input.onchange();
                        } else {
                          input.checked = false;
                          input.onchange();
                        }
                      });
                    }
                    $V(extension_input, acte_csarr['extension']);
                  }

                  $V(duree_input, sejours_associes[sejour_id][line_element_id][event_id]['duree']);
                  $V(type_seance_input, sejours_associes[sejour_id][line_element_id][event_id]['type_seance']);
                  $V(executant_input, sejours_associes[sejour_id][line_element_id][event_id]['executant']);
                });
              });
            });
          });
        }
      });
  },
  /**
   * Duplicate all selected CsARR from one element to all elements of the same name
   *
   * @param sejour_id
   * @param element_id
   */
  duplicateElement: function (sejour_id, element_id) {
    if (confirm($T('CPlageGroupePatient-msg-Are you sure you want to apply the same values of this element to all other elements of the same name'))) {
      // Main element
      var main_codes_csarr = {};

      $$('.show_acte_' + sejour_id).each(function (input) {
        if (input.get('element_prescription_id') === element_id) {
          var acte_id = input.value.split('_')[0];

          main_codes_csarr[acte_id] = {
            code:        input.get('code'),
            modulateurs: input.get('modulateurs'),
            duree:       input.get('duree'),
            extension:   input.get('extension'),
            type_seance: input.get('type_seance'),
            executant:   input.get('executant')
          };
        }
      });

      // Other same element
      getForm('gestion_groupe_patients').select('.add_acte_csarr').each(function (elt) {
        var elt_sejour_id = elt.value.split('_')[1];
        var acte_id = elt.value.split('_')[0];

        if ((elt.get('element_prescription_id') === element_id) && (elt_sejour_id != sejour_id)) {
          var acte_csarr_code = main_codes_csarr[acte_id]['code'].replace("+", "");
          var modulateurs_checkbox = $$('.modulateurs_' + acte_csarr_code + '_' + elt_sejour_id);
          var duree_input = $$('.duree_' + acte_csarr_code + '_' + elt_sejour_id)[0];
          var extension_input = $$('.extension_' + acte_csarr_code + '_' + elt_sejour_id)[0];
          var type_seance_input = $$('.type_seance_' + acte_csarr_code + '_' + elt_sejour_id)[0];
          var executant_input = $$('.executant_' + acte_csarr_code + '_' + elt_sejour_id)[0];

          if (modulateurs_checkbox.length) {
            modulateurs_checkbox.each(function (input) {
              if (main_codes_csarr[acte_id]['modulateurs'].includes(input.value)) {
                input.checked = true;
                input.onchange();
              } else {
                input.checked = false;
                input.onchange();
              }
            });
          }

          $V(duree_input, main_codes_csarr[acte_id]['duree']);
          $V(extension_input, main_codes_csarr[acte_id]['extension']);
          $V(type_seance_input, main_codes_csarr[acte_id]['type_seance']);
          $V(executant_input, main_codes_csarr[acte_id]['executant']);
        }
      });
    }
  },
  /**
   * Duplicate all selected CsARR from one element to all elements of the patient
   *
   * @param button
   * @param sejour_id
   * @param element_id
   */
  duplicateReeducateur: function (button, sejour_id, element_id) {
    if (confirm($T('CPlageGroupePatient-msg-duplicateReeducateur'))) {
      var executant_id = button.up('td').down('select').value;

      getForm('gestion_groupe_patients').select('.executant_element_' + sejour_id + '_' + element_id).each(function (select) {
        $V(select, executant_id);
      });
    }
  },
  /**
   * Uncheck box to select all patients
   */
  uncheckAllPatients: function () {
    if (GroupePatient.form.check_all_patients.checked) {
      GroupePatient.form.check_all_patients.checked = false;
      GroupePatient.form.check_all_patients.onchange();
    }
  },
  /**
   * Set the given field to a not null field
   *
   * @param field         The field
   * @param label_element The label element
   */
  setNotNull: function (acte_id, sejour_id, class_acte_code, addValues) {
    var executant = this.form.elements['executant_' + acte_id + '_' + sejour_id].value || !this.form.elements['acte_csarr_' + acte_id + '_' + sejour_id].checked;
    var label = $('label_executant_' + class_acte_code + '_' + sejour_id);
    var buttonDuplicate = $('duplicateReeducateur_' + class_acte_code + '_' + sejour_id);
    var select = label.up('td').down('select');

    if (executant) {
      label.addClassName('notNullOK');
      label.removeClassName('notNull');
      select.style.color = 'black';
      buttonDuplicate.show();
    } else {
      label.addClassName('notNull');
      label.removeClassName('notNullOK');
      select.style.color = '#FF3100';
      buttonDuplicate.hide();
    }
    if (addValues) {
      GroupePatient.addValues(label.up('form'), acte_id, sejour_id, 'executant');
    }
  },
  /**
   * Add 2 times
   *
   * @param startTime
   * @param endTime
   *
   * @returns {string}
   */
  addTimes: function (startTime, endTime) {
    var times = [0, 0, 0];
    var max = times.length;

    var a = (startTime || '').split(':');
    var b = (endTime || '').split(':');

    // normalize time values
    for (var i = 0; i < max; i++) {
      a[i] = isNaN(parseInt(a[i])) ? 0 : parseInt(a[i]);
      b[i] = isNaN(parseInt(b[i])) ? 0 : parseInt(b[i]);
    }

    // store time values
    for (var i = 0; i < max; i++) {
      times[i] = a[i] + b[i];
    }

    var hours = times[0];
    var minutes = times[1];
    var seconds = times[2];

    if (seconds >= 60) {
      var m = (seconds / 60) << 0;
      minutes += m;
      seconds -= 60 * m;
    }

    if (minutes >= 60) {
      var h = (minutes / 60) << 0;
      hours += h;
      minutes -= 60 * h;
    }

    return ('0' + hours).slice(-2) + ':' + ('0' + minutes).slice(-2) + ':' + ('0' + seconds).slice(-2);
  },
  /**
   * Format time to 00h00
   *
   * @param time
   *
   * @return {string}
   */
  formatTime: function (time) {
    var time_split = time.split(':');
    return time_split[0] + 'h' + time_split[1];
  },
  /**
   * Change the timings if the duration is changed
   *
   * @param element
   * @param position
   * @param max_actes
   * @param container_name
   * @param heure_debut_plage
   */
  changeTimings: function (element, position, max_actes, container_name) {
    var main_input = element;
    var value_input = main_input.value;
    var main_duree = "00:" + main_input.dataset.duree + ":00";
    var main_heure_debut = main_input.dataset.acte_heure_debut;
    var position = position ? position : main_input.dataset.position;

    // Set the new duration to end hour
    var modify_heure_fin = this.addTimes(main_heure_debut, main_duree);
    main_input.writeAttribute("data-acte_heure_fin", modify_heure_fin);
    $('heure_fin_' + value_input).innerHTML = this.formatTime(modify_heure_fin);

    // Modify the other actes
    var other_heure_debut = '';
    position = parseInt(position) + 1;

    for (var i = position; i < parseInt(max_actes); i++) {
      if ($(container_name + '_' + i)) {
        var next_input = $(container_name + '_' + i).down('input');
        var next_value_input = next_input.value;
        var next_duree = "00:" + next_input.dataset.duree + ":00";

        if (!other_heure_debut) {
          other_heure_debut = modify_heure_fin;
        }

        // Set the new duration to end hour
        var next_heure_debut = other_heure_debut;
        var next_heure_fin = this.addTimes(next_heure_debut, next_duree);
        next_input.writeAttribute("data-acte_heure_debut", next_heure_debut);
        next_input.writeAttribute("data-acte_heure_fin", next_heure_fin);
        $('heure_debut_' + next_value_input).innerHTML = this.formatTime(next_heure_debut);
        $('heure_fin_' + next_value_input).innerHTML = this.formatTime(next_heure_fin);

        other_heure_debut = next_heure_fin;
      }
    }
  },
  /**
   * Change elements' rank
   *
   * @param element
   * @param sortable
   * @param max_actes
   * @param container_name
   */
  changeRank: function (element, sortable, max_actes, container_name) {
    var wish_rank = 0;
    var main_tr = element.up('tr');
    var main_div = main_tr.down('div.editLineCsarr');
    var main_div_id = main_div.id;
    var main_input = main_div.down('input');
    var other_container = null;
    var plage_heure_debut = main_tr.dataset.plage_heure_debut;
    var position = main_input.dataset.position;

    if (sortable == 'down') {
      wish_rank = parseInt(position) + 1;

      other_container = main_tr.next();

      if (window.getComputedStyle(other_container.down('div')).display === 'none') {
        return;
      }

      if (other_container) {
        other_container.insert({after: main_tr});
      }
    }
    else {
      wish_rank = parseInt(position) - 1;

      other_container = main_tr.previous();

      if (other_container) {
        other_container.insert({before: main_tr});
      }
    }

    var other_div = other_container.down('div.editLineCsarr');
    var other_div_id = other_div.id;
    var other_input = other_div.down('input');

    // Change id div element to recalculate schedule
    main_div.id = other_div_id;
    other_div.id = main_div_id;

    // Change the rank in dataset
    main_input.writeAttribute("data-position", wish_rank);
    other_input.writeAttribute("data-position", position);

    // Get the first div
    var first_div_id = main_div_id.slice(0, -1) + '0';
    var first_input = $(first_div_id).down('input');

    first_input.writeAttribute("data-acte_heure_debut", plage_heure_debut);
    $('heure_debut_' + first_input.value).innerHTML = this.formatTime(plage_heure_debut);

    this.changeTimings(first_input, 0, max_actes, container_name);

    // Show/hide arrows
    if ((position == 0 && wish_rank == 1)) {
      main_tr.down('span.arrow_up').down('i').show();
      other_container.down('span.arrow_up').down('i').hide();
    }
    else if ((position == 1 && wish_rank == 0)) {
      main_tr.down('span.arrow_up').down('i').hide();
      other_container.down('span.arrow_up').down('i').show();
    }
    else if ((position == max_actes - 1 && wish_rank == max_actes - 2)) {
      main_tr.down('span.arrow_down').down('i').show();
      other_container.down('span.arrow_down').down('i').hide();
    }
    else if ((position == max_actes - 2 && wish_rank == max_actes - 1)) {
      main_tr.down('span.arrow_down').down('i').hide();
      other_container.down('span.arrow_down').down('i').show();
    }
  },
};
