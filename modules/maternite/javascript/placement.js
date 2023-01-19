/**
 * @package Mediboard\Matenrite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Placement = window.Placement || {
  tabs_placement: null,
  terme_min: null,
  terme_max: null,
  autocomplete_pat: null,
  lock_uf_med: false,

  initPec: function(terme_min, terme_max) {
    this.terme_min = terme_min;
    this.terme_max = terme_max;
    this.makeAutocompletes();

    var form = getForm('pecPatiente');
    var lit = form._unique_lit_id;
    var grossesse_id = form.grossesse_id;

    Element.observe(lit, "lit:change", function(event) {
      var opt_group = lit.options[lit.selectedIndex].up("optgroup");
      if (!opt_group) {
        return;
      }
      var service_id = opt_group.get("service_id");
      $V(form.elements.service_id, service_id, false);
    });

    Element.observe(lit, "change", function(event) {
      lit.fire("lit:change");
    });

    Element.observe(grossesse_id, 'ui:change', function() {
      $$('.suivi_part').invoke($V(grossesse_id) ? 'show' : 'hide');
    });
  },

  makeAutocompletes: function() {
    var form = getForm('pecPatiente');

    new Url('mediusers', 'ajax_users_autocomplete')
      .addParam('mater', 1)
      .addParam('input_field', '_prat_id_view')
      .autoComplete(form._prat_id_view, null, {
        minChars: 0,
        method: 'get',
        dropdown: true,
        width: '350px',
        afterUpdateElement: function(field, selected) {
          var field_view = selected.down('.view');
          $V(field, field_view.getText());
          $V(field.form._prat_id, selected.getAttribute('id').split('-')[2]);

          Placement.toggleSejour(field_view.dataset.create_sejour_consult === '1');
        }
      });

    Placement.autocomplete_pat = new Url('system', 'ajax_seek_autocomplete')
      .addParam('object_class', 'CPatient')
      .addParam('field', 'patient_id')
      .addParam('view_field', '_patient_view')
      .addParam('input_field', '_patient_view')
      .autoComplete(
        form.elements._patient_view, null,
        {
          minChars:           3,
          method:             'get',
          select:             'view',
          dropdown:           false,
          width:              '350px',
          callback:           function(input, queryString) {
            if (form.___active_grossesse.checked) {
              queryString += '&ljoin[grossesse]=grossesse.parturiente_id = patients.patient_id';
              queryString += '&where[grossesse.active]=1';
              queryString += '&group_by=patients.patient_id';
            }
            return queryString;
          },
          afterUpdateElement: function (field, selected) {
            $V(form.patient_id, selected.get('guid').split('-')[1]);
            $V(form._patient_sexe, selected.down('span.view').dataset.sexe);
          }
        }
      );

    new Url('hospi', 'ajax_lit_autocomplete')
      .addParam('obstetrique', 1)
      .autoComplete(form.keywords, null, {
        minChars: 2,
        method: 'get',
        select: 'view',
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          var value = selected.id.split('-')[2];
          $V(form._unique_lit_id, value);
          $V(form.service_id, selected.dataset.serviceId, false);
        },
        callback: function(input, queryString) {
          var service_id = $V(form.service_id);
          if (!Object.isUndefined(service_id)) {
            queryString += "&service_id=" + service_id;
          }

          queryString += "&date_min=" + $V(form._datetime);

          return queryString;
        }
      });
  },

  checkParturiente: function() {
    var form = getForm('pecPatiente');
    var patient_id = $V(form.patient_id);

    if (!patient_id) {
      return;
    }

    $$('.edit_patient').invoke('writeAttribute', 'disabled', null);

    new Url('maternite', 'ajax_check_parturiente')
      .addParam('patient_id', patient_id)
      .requestJSON(function(result) {
        if (result.nb_consults) {
          Placement.selectConsult(patient_id);
        }

        var terme_area = $('terme_area');
        terme_area.update('&mdash;');

        Grossesse.emptyGrossesses();

        if (result.grossesse.grossesse_id) {
          var date = Date.fromDATE(result.grossesse.terme_prevu);
          terme_area.update(date.toLocaleDate());
          Grossesse.bindGrossesse(result.grossesse.grossesse_id);
        }
        if (result.grossesse.last_sejour) {
          $V(form.sejour_id, result.grossesse.last_sejour);
          $V(form._force_create_sejour, '0');
        }
        else {
          $V(form.sejour_id, '');
          $V(form._force_create_sejour, '1');
        }
      });
  },

  selectConsult: function(patient_id) {
    new Url('maternite', 'ajax_select_consult')
      .addParam('patient_id', patient_id)
      .requestModal('500px');
  },
  /**
   * Refresh the placement
   *
   * @param object_guid CService or CBlocOperatoire
   */
  refreshPlacement: function (object_guid) {
    new Url('maternite', 'ajax_placement_patients')
      .addParam('object_guid', object_guid)
      .requestUpdate(object_guid);
  },

  refreshCurrPlacement: function () {
    Placement.tabs_placement.options.afterChange(Placement.tabs_placement.activeContainer);
  },

  refreshEtiquette: function (sejour_id) {
    if (!$('placement_' + sejour_id)) {
      return;
    }

    new Url('maternite', 'ajax_placement_patients')
      .addParam('sejour_id', sejour_id)
      .requestUpdate('placement_' + sejour_id);
  },

  pecPatiente: function(sejour_id) {
    new Url('maternite', 'pecPatiente')
      .addParam('sejour_id', sejour_id)
      .requestModal('90%', '90%');
  },

  pecPatienteUrgences: function() {
    new Url('maternite', 'ajax_pec_patiente_urgences')
      .requestModal('500px');
  },

  toggleSejour: function(show) {
    if (Object.isUndefined(show)) {
      show = 0;
    }

    $$('.sejour_part').invoke(show ? 'show' : 'hide');
  },

  callbackPecPatiente: function(consult_id) {
    Control.Modal.close();

    Consultation.editModal(consult_id, null, null, () => {
        Placement.refreshCurrPlacement();
        Placement.refreshNonPlaces();
      }
    );
  },

  mapAffectation: function (affectation_id, affectation) {
    var form = getForm('CSejour-' + affectation.sejour_id + '_move');
    if (!form) {
      return;
    }

    // Déplacemnt des bébés
    var div_sejour = $('placement_' + affectation.sejour_id);

    $$("div.parent_" + affectation.sejour_id).each(function (_affectation) {
      div_sejour.up().insert(_affectation);
    });

    $V(form.affectation_id, affectation_id);
    $V(form.entree, affectation.entree);
    $V(form.sortie, affectation.sortie);
  },

  associatedAffectation: function(affectation_id, date_split, new_lit_id) {
    if (confirm('Souhaitez-vous aussi déplacer la maman ?')) {
      new Url('maternite', "ajax_move_affectation_mother")
        .addParam("affectation_id", affectation_id)
        .addParam("date_split", date_split)
        .addParam("new_lit_id", new_lit_id)
        .requestUpdate("systemMsg",{onComplete : function () {
            Placement.refreshPlacement('CService-' + ChoiceLit.service_id);
            }}
          );
    }
  },
  /**
   * Refresh unplaced patients
   */
  refreshNonPlaces: function () {
    var url = new Url('maternite', 'ajax_vw_patients_non_places');
    url.addParam("date", getForm("changeDate").date.value);
    url.requestUpdate('patients_non_places');
  },
  /**
   * Send the form to move patient
   *
   * @param form
   * @param sejour_guid
   * @param location
   * @param no_affectation
   * @returns {Boolean|boolean}
   */
  submitLitOrSalle: function(form, sejour_guid, location, no_affectation) {
    var sejour_id = sejour_guid.split('-')[1];
    var lit_id = $V(form._lit_id);
    var service_id = $V(form._service_id);
    var salle_id = $V(form._salle_id);
    var bloc_id = $V(form._bloc_id);
    var entree_salle = $V(form._entree_salle);
    var mod_mater = $V(form._mod_mater);
    var other_form = null;
    var tab_name = 'CService-' + $V(form._current_service_id);

    if (location == 'bloc') {
      tab_name = 'CBlocOperatoire-' + $V(form._current_bloc_id);
    }

    var callbacks = function () {
      Control.Modal.close();
      Placement.refreshNonPlaces();
      setTimeout(Placement.refreshPlacement.curry(tab_name), 1000);
    };

    // Service
    if (lit_id && service_id) {
      other_form = getForm((no_affectation !== '1') ? sejour_guid + "_move_service" : "changeServiceForm");

      $V(other_form._mod_mater, mod_mater);

      if (no_affectation === '1') {
        $V(other_form.lit_id, lit_id);
        $V(other_form.service_id, service_id);
        $V(other_form.entree, $V(form._sejour_entree));
        $V(other_form.sortie, $V(form._sejour_sortie));
      }
      else {
        $V(other_form._new_lit_id, lit_id);
        $V(other_form._service_id, service_id);

        if (location == 'bloc' && entree_salle) {
          $V(other_form.entree, entree_salle);
        }
      }
    }
    // Bloc
    else if (salle_id && bloc_id) {
      other_form = getForm(sejour_guid + "_move_bloc");
      $V(other_form.salle_id, salle_id);

      var container_patient = $('placement_' + sejour_id);
      var operation_id = $V(other_form.operation_id);

      // Bed locked
      if (container_patient && $V(form._bed_locked)) {
        container_patient.addClassName('div_opacity');
      }
      else if (!$V(form._bed_locked)) {
        var form_affectation = getForm('changeServiceForm');

        if (container_patient) {
          var affectation_id = container_patient.get('affectation_id');
          var service_id     = container_patient.get('service_id');

          $V(form_affectation.affectation_id, affectation_id);
          $V(form_affectation.service_id, service_id);
          $V(form_affectation.sejour_id, sejour_id);
          $V(form_affectation.lit_id, '');

          onSubmitFormAjax(form_affectation);
        }
      }

      if (!operation_id) {
        var patient_id = container_patient.get('patient_id');
        Control.Modal.close();
        Tdb.editAccouchement(null, sejour_id, null, patient_id, callbacks, salle_id);
        return true;
      }
    }

    return onSubmitFormAjax(other_form, callbacks);
  },
  /**
   * Select the context
   *
   * @param element
   * @param container_name
   */
  selectContextToMove: function (element, container_name) {
    var fieldset = element.up().up();

    if (element.checked) {
      $(container_name).disabled = true;
      $(container_name).style.opacity = '0.5';
      $(fieldset.id).disabled = false;
      $(fieldset.id).style.opacity = '1';
    }

    if (fieldset.id == 'container_salle') {
      $('msg_bed_locked').show();
      $('container_service').select('select.service').each(function(select) {
        $V(select, '');
      });
    }
    else {
      $('msg_bed_locked').hide();
      $('container_salle').select('select.salle').each(function(select) {
        $V(select, '');
      });
    }
  },
  /**
   * Get datas in the form
   *
   * @param element
   * @param object_id
   * @param container_name
   */
  getDataSelected: function (element, object_id, container_name) {
   var form = element.form;

    if (container_name == 'container_service') {
      var lit_id = element.value;

      $V(form._service_id, object_id);
      $V(form._lit_id, lit_id);

      // if I use service, remove value from bloc
      $V(form._bloc_id, '');
      $V(form._salle_id, '');

      // remove the other value from select element
      $$('select.service:not(.lit_id_' + object_id +')').each(function(select) {
        $V(select, '');
      });

      $V(form.elements['lit_id_' + object_id], lit_id);
    }
    else if (container_name == 'container_salle') {
      var salle_id = element.value;

      $V(form._bloc_id, object_id);
      $V(form._salle_id, salle_id);

      // if I use service, remove value from bloc
      $V(form._service_id, '');
      $V(form._lit_id, '');

      // remove the other value from select element
      $$('select.salle:not(.salle_id_' + object_id +')').each(function(select) {
        $V(select, '');
      });

      $V(form.elements['salle_id_' + object_id], salle_id);
    }
  },

  preselectUf: () => {
    var form = getForm('pecPatiente');

    new Url('planningOp', 'ajax_get_ufs_ids')
      .addParam('chir_id', $V(form._prat_id))
      .requestJSON(function(ids) {
        var field = form._uf_medicale_id;
        $V(field, "");

        [ids.principale_chir, ids.principale_cab, ids.secondaires].each(
          function (_ids) {
            if ($V(field)) {
              return;
            }

            if (!_ids || !_ids.length) {
              return;
            }

            var i = 0;

            while (!$V(field) && i < _ids.length) {
              $V(field, _ids[i]);
              i++;
            }
          }
        );

        if (Placement.lock_uf_med) {
          for (i = 0; i < form._uf_medicale_id.options.length; i++) {
            var _option = form._uf_medicale_id.options[i];
            var _option_value = parseInt(_option.value);

            var statut = !(
              (ids.secondaires && ids.secondaires.indexOf(_option_value) != -1)
              || (ids.principale_chir && ids.principale_chir.indexOf(_option_value) != -1)
              || (ids.principale_cab && ids.principale_cab.indexOf(_option_value) != -1)
            );

            _option.writeAttribute('disabled', statut);
          }
        }
      }
    );
  }
};
