/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

CotationRHS = {
  refresh: function(sejour_id) {
    new Url('ssr', 'ajax_cotation_rhs') .
      addParam('sejour_id', sejour_id) .
      requestUpdate('cotation-rhs-'+sejour_id);
  },

  refreshNewRHS: function(date, sejour_id) {
    new Url('ssr', 'ajax_create_rhs')
      .addParam('date_monday', date)
      .addParam('sejour_id', sejour_id)
      .requestUpdate('cotation-' + date);
  },

  refreshRHS: function(rhs_id, recalculate, light_view) {
    new Url('ssr', 'ajax_edit_rhs')
      .addParam('rhs_id', rhs_id)
      .addParam('recalculate', recalculate)
      .addParam('light_view', light_view)
      .requestUpdate('cotation-' + rhs_id, CotationRHS.launchDrawDependancesGraph.curry(rhs_id));
  },

  refreshTotaux: function(rhs_id, recalculate) {
    new Url('ssr', 'ajax_totaux_rhs')
      .addParam('rhs_id', rhs_id)
      .addParam('recalculate', recalculate)
      .requestUpdate('totaux-' + rhs_id);
  },

  printRHS: function(form) {
    new Url('ssr', 'print_sejour_rhs_no_charge')
      .addParam('sejour_ids', form.select('input.rhs:checked').pluck('value').join('-'))
      .addParam('all_rhs', $V(form.all_rhs) ? '1' : '0')
      .addElement(form.date_monday)
      .popup(700, 500, $T('ssr-print_rhs_to_charge'));
  },

  chargeRHS: function(form) {
    form.onsubmit();
  },

  restoreRHS: function(form) {
    $V(form.facture, '0');
    form.onsubmit();
  },

  onSubmitRHS: function(form) {
    return onSubmitFormAjax(form, CotationRHS.refresh.curry($V(form.sejour_id)));
  },

  onSubmitLine: function(form) {
     return onSubmitFormAjax(form, CotationRHS.refreshRHS.curry($V(form.rhs_id)));
  },

  confirmDeletionLine: function(form) {
    var options = {
      typeName:'l\'activité'
    };

    var ajax = {
      onComplete: CotationRHS.refreshRHS.curry($V(form.rhs_id))
    };

    confirmDeletion(form, options, ajax);
  },

  onSubmitQuantity: function(form, sField) {
    if ($V(form[sField]) == '0' || $V(form[sField]) == '') {
      form.parentNode.removeClassName('ok');
    }
    else {
      form.parentNode.addClassName('ok');
    }

    return onSubmitFormAjax(form, CotationRHS.refreshTotaux.curry($V(form.rhs_id)));
  },

  updateTab: function(count) {
    var tab = $('tab-equipements');
    tab.down('a').setClassName('empty', !count);
    tab.down('a small').update('('+count+')');
  },

  autocompleteExecutant: function (form) {
    new Url('ssr', 'httpreq_do_intervenant_autocomplete')
      .autoComplete(
        form._executant,
        form.down('.autocomplete.executant'),
        {
          dropdown: true,
          minChars: 2,
          updateElement: function(element) {
            CotationRHS.updateExecutant(element, form);
          }
        }
      );
  },

  showModulators: function(code_activite_csarr, rhs_line_id) {
    new Url('ssr', 'ajax_show_modulators')
      .addParam('code_activite_csarr', code_activite_csarr)
      .requestUpdate('modulators_' + rhs_line_id);
  },

  updateExecutant: function(selected, form) {
    var values = selected.down('.values');

    // On vide les valeurs
    if (!values) {
      $V(form._executant, '');
      $V(form.executant_id, '');
      $V(form.code_intervenant_cdarr, '');
    }
    // Sinon, on rempli les valeurs
    else {
      $V(form.executant_id,           values.down('.executant_id'          ).textContent);
      $V(form.code_intervenant_cdarr, values.down('.code_intervenant_cdarr').textContent);
      $V(form._executant,             values.down('._executant'            ).textContent);
    }
  },

  autocompleteCsARR: function(form) {
    new Url('ssr', 'httpreq_do_csarr_autocomplete')
      .autoComplete(
        form.code_activite_csarr,
        form.down('.autocomplete.activite'),
        {
          dropdown: true,
          minChars: 2,
          updateElement: function(element) {
            CotationRHS.updateCsARR(element, form);
          }
        }
      );
  },

  updateCsARR: function (selected, form) {
    var value = selected.down('.value');
    let collectif = selected.down('.collectif');
    if (collectif && collectif.textContent === 'oui') {
      CotationRHS.setCollectiveFieldsClass(form);
      CotationRHS.toggleColletiveFieldsNullable(form);
    } else {
      CotationRHS.setCollectiveFieldsClass(form, true)
    }
    $V(form.code_activite_csarr, value ? value.textContent : '');
    form.code_activite_csarr.onchange();
  },

  setCollectiveFieldsClass: function (form, remove) {
    let collectiveInputs = ["nb_patient_seance", "nb_intervenant_seance"];

    collectiveInputs.forEach((input_name) => {
      if (form[input_name]) {
        if (!remove) {
          form[input_name].addClassName("notNull");
        } else {
          form[input_name].removeClassName("notNull");
          let label = $(`labelFor_${form.name}_${input_name}`);
          if (label) {
            label.removeClassName("notNull");
            label.removeClassName("error");
            label.removeClassName("notNullOK");
          }
        }
      }
    });
  },

  toggleColletiveFieldsNullable: function (form) {
    CotationRHS.checkNbIntervenant(form);
    CotationRHS.checkNbPatient(form);
  },

  toggleFieldNullable: function (form, field_name) {
    let input = form[field_name];
    if (input) {
      let add_class_name = $V(input) ? "notNullOK" : "notNull";
      let remove_class_name = $V(input) ? "notNull" : "notNullOK";
      let label = $(`labelFor_${form.name}_${field_name}`);
      if (label) {
        label.addClassName(add_class_name);
        label.removeClassName(remove_class_name);
      }
    }
  },

  checkNbIntervenant: function (form) {
    if (form.nb_intervenant_seance.hasClassName("notNull")) {
      CotationRHS.toggleFieldNullable(form, "nb_intervenant_seance");
    }
  },

  checkNbPatient: function (form) {
    if (form.nb_patient_seance.hasClassName("notNull")) {
      CotationRHS.toggleFieldNullable(form, "nb_patient_seance");
    }
  },

  editDependancesRHS: function(rhs_id) {
    new Url('ssr', 'ajax_edit_dependances_rhs')
      .addParam('rhs_id', rhs_id)
      .requestModal('70%', '65%')
      .modalObject.observe('afterClose', CotationRHS.refreshRHS.curry(rhs_id));
  },

  drawDependancesGraph: function(container, rhs_id, data) {
    CotationRHS.dependancesGraphs[rhs_id] = (function(container, data){
      Flotr.draw(
        container,
        data,
        {
          radar: {show: true},
          grid: {circular: true, minorHorizontalLines: true},
          xaxis: {ticks:[
            [0, $T('DependancesRHSBilan-habillage-court')],
            [1, $T('DependancesRHSBilan-deplacement-court')],
            [2, $T('DependancesRHSBilan-alimentation-court')],
            [3, $T('DependancesRHSBilan-continence-court')],
            [4, $T('DependancesRHSBilan-comportement-court')],
            [5, $T('DependancesRHSBilan-relation-court')]
          ]},
          yaxis: {min: 0, max: 4},
          colors: [
            '#c1f1ff',
            '#8cdcff',
            '#00A8F0',
            '#86e8aa',
            '#91f798'
          ],
          legend: {
            labelBoxMargin: 4,
            labelBoxHeight: 5,
            labelBoxWidth: 4,
            margin: 4
          },
          HtmlText: false
        }
      );
    }).curry(container, data);

    CotationRHS.launchDrawDependancesGraph(rhs_id);
  },

  launchDrawDependancesGraph: function(rhs_id) {
    // Sometimes the container is invisible, flotr doesn't support it
    try {
      (CotationRHS.dependancesGraphs[rhs_id] || Prototype.emptyFunction)();
      CotationRHS.dependancesGraphs[rhs_id] = function(){};
    } catch(e) {}
  },

  duplicate: function(rhs_id, sejour_id, part) {
    new Url('ssr', 'ajax_duplicate_rhs')
      .addParam('rhs_id', rhs_id)
      .addParam('part', part)
      .requestModal('70%', '70%', {onClose: CotationRHS.refresh.curry(sejour_id)});
  }
};

CotationRHS.dependancesGraphs = CotationRHS.dependancesGraphs || {};

Charged = {
  refresh: function(rhs_date_monday) {
    var form = getForm('editRHS-'+rhs_date_monday);
    var label = form.down('label.rhs-charged');
    var count = form.select('tr.charged').length;
    label.setVisibility(count != 0);
    label.down('span').update(count);
  },

  addSome: function(rhs_date_monday, value) {
    var form = getForm('editRHS-'+rhs_date_monday);
    form.select('input.rhs').each(function (checkbox) {
      if ($(checkbox).up('tr').visible()) {
        $V(checkbox, value);
      }
    });
  },
  countLinesChecked: function(rhs_date_monday) {
    var form = getForm('editRHS-'+rhs_date_monday);
    var input_legend = form.check_lines_rhs;
    input_legend.checked = '';
    input_legend.style.opacity = '1';

    var inputs_checked = form.select('input[type=checkbox]:checked.rhs');
    var inputs = form.select('input[type=checkbox].rhs');
    var nb_inputs = inputs.length;
    var nb_inputs_checked = inputs_checked.length;

    form.select('input.rhs').each(function (checkbox) {
      if (!$(checkbox).up('tr').visible()) {
        nb_inputs--;
      }
    });

    if (nb_inputs_checked) {
      if (nb_inputs_checked < nb_inputs) {
        input_legend.style.opacity = '0.5';
      }
      input_legend.checked = '1';
    }
  },
  toggle: function(checkbox) {
    $$('tr.charged').invoke('setVisible', !checkbox.checked);
  }
};
