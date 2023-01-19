/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

TrameCollective = {
  current_m: 'ssr',
  use_acte_presta: '',
  refreshPlanning: function(trame_id) {
    new Url(TrameCollective.current_m, 'ajax_vw_planning_trame')
      .addNotNullParam('trame_id', trame_id)
      .requestUpdate('planning-collectif-'+trame_id);
  },
  refreshAllPlannings: function(show_inactive) {
    var form = getForm('planning_collectif_filter');
    var div_planning = $('planning_collectif');
    var msg_alert = $('message_info_no_filter');
    if (!$V(form.function_id)) {
      div_planning.hide();
      msg_alert.show();
      return;
    }
    div_planning.show();
    msg_alert.hide();
    var url = new Url(TrameCollective.current_m, 'ajax_vw_planning_collectif');
    url.addFormData(form);
    if (show_inactive !== null && typeof(show_inactive) !== 'undefined') {
      url.addParam('show_plage_inactive', show_inactive ? 1 : 0)
    }
    url.requestUpdate('planning_collectif');
  },
  editTrame: function(trame_id) {
    new Url(TrameCollective.current_m, 'ajax_edit_trame_collective')
      .addParam('trame_id', trame_id)
      .requestModal(350, 275);
  },
  editPlage: function(plage_id) {
    new Url(TrameCollective.current_m, 'ajax_edit_plage_collective')
      .addParam('plage_id', plage_id)
      .requestModal('60%', '95%');
  },
  confirmChangePlage: function(form) {
    if (confirm($T('CPlageSeanceCollective-confirm_modification'))) {
      form.onsubmit();
    }
  },
  confirmDeletion: function(form) {
    var options = {
      objName: $V(form.nom),
      ajax: 1
    };
    var ajax = {
      onComplete: function() {
        Control.Modal.close();
        TrameCollective.refreshAllPlannings();
      }
    };
    confirmDeletion(form, options, ajax);
  },
  onsubmit: function(form) {
    if (!$V(form.plage_id) && (form.name != 'select_patients_planning_collectif')) {
      // Test de la presence d'au moins un code SSR
      if (TrameCollective.use_acte_presta == 'csarr') {
        var csarr_count = $V(form.code_csarr) ? 1 : 0;
        csarr_count += form.select('input.checkbox-csarrs:checked').length;
        csarr_count += form.select('input.checkbox-other-csarrs').length;

        if (csarr_count == 0) {
          alert($T('ssr-to_selected-Csarr'));
          return false;
        }
      }
      else if (TrameCollective.use_acte_presta == 'presta'){
        // Presta
        var presta_ssr_count = $V(form.code_presta_ssr) ? 1 : 0;
        presta_ssr_count += form.select('input.checkbox-prestas_ssr:checked').length;
        presta_ssr_count += form.select('input.checkbox-other-prestas_ssr').length;

        if (presta_ssr_count == 0) {
          alert($T('CPrestaSSR-msg-Please select at least one SSR service'));
          return false;
        }
      }
    }

    return onSubmitFormAjax(form, {
      onComplete: function() {
        Control.Modal.close();
        TrameCollective.refreshAllPlannings();
      }
    });
  },
  confirmValidation: function(form) {
    if (confirm($T('CPlageSeanceCollective.gestionPatient-confirm'))) {
      $V(form.sejour_ids, Object.toJSON(Seance.jsonSejours));
      form.onsubmit();
    }
  },
  autocompleteElementPrescription: function(form) {
    new Url('prescription', 'httpreq_do_element_autocomplete')
      .addParam('category', (TrameCollective.current_m == 'ssr' ? 'kine' : 'psy'))
      .autoComplete(form.libelle, 'element_prescription_id_autocomplete', {
      dropdown: true,
      minChars: 0,
      updateElement: function(element) {
        Element.cleanWhitespace(element);
        var dn = element.childNodes;
        $V(form.element_prescription_id, dn[0].firstChild.nodeValue);
        $V(form.libelle, element.down('strong').get('libelle'), false);
        TrameCollective.refreshListActes($V(form.plage_id), $V(form.element_prescription_id));
      }
    });
  },
  refreshListActes: function(plage_id, element_prescription_id) {
    new Url('ssr', 'ajax_refresh_codage_plage')
      .addParam('plage_id', plage_id)
      .addParam('element_prescription_id', element_prescription_id)
      .requestUpdate('actes_plage');
  },
  gestionPatient: function(plage_id) {
    new Url(TrameCollective.current_m, 'vw_patients_plage_collective')
      .addParam('plage_id', plage_id)
      .requestModal('90%', '90%');
  },
  sortBy: function (order_col, order_way) {
    new Url(TrameCollective.current_m, 'vw_patients_plage_collective')
      .addParam('order_col', order_col)
      .addParam('order_way', order_way)
      .requestUpdate('form_patients_planning_collectif');
  },
  selectMultiDays: function (form) {
    $V(form.days_week, [$V(form._days_week)].flatten().join('|'));
  },
  confirmInactivation: function (form) {
    if (confirm($T('CPlageSeanceCollective.inactivation-confirm'))) {
      $V(form.active, 0);
      form.onsubmit();
    }
  },
  confirmActivation: function (form) {
    if (confirm($T('CPlageSeanceCollective.activation-confirm'))) {
      $V(form.active, 1);
      form.onsubmit();
    }
  }
};
