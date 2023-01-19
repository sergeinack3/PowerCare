/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Redon = {
  sejour_id: null,

  selectRedons: function(class_redon) {
    new Url('patients', 'ajax_select_redons')
      .addParam('sejour_id', this.sejour_id)
      .addParam('class_redon', class_redon)
      .requestModal('500px');
  },

  validateRedons: function(form) {
    return onSubmitFormAjax(form, (function() {
      Control.Modal.close();
      this.refreshRedons();
    }).bind(this));
  },

  editRedon: function(redon_id) {
    new Url('patients', 'ajax_edit_redon')
      .addParam('redon_id', redon_id)
      .requestModal('600px', null, {onClose: (this.refreshRedons).bind(this)});
  },

  refreshRedons: function() {
    new Url('patients', 'ajax_vw_redons')
      .addParam('sejour_id', this.sejour_id)
      .requestUpdate('redons_area');
  },

  refreshRedon: function(redon_id) {
    new Url('patients', 'ajax_vw_redon')
      .addParam('redon_id', redon_id)
      .requestUpdate('redon_' + redon_id);
  },

  saveReleve: function(form, redon_id, callback) {
    return onSubmitFormAjax(form, (function() {
      if (callback) {
        callback();
      }
      else {
        this.refreshRedon(redon_id);
      }
    }).bind(this));
  },

  updateDiff: function(input_qte, redon_id, last_qte_obs) {
    var qte_obs = $V(input_qte);

    if (!qte_obs) {
      $V(input_qte.form._qte_diff, '');

      return;
    }

    if (Object.isUndefined(last_qte_obs)) {
      var span_qte_obs = $('qte_observee_' + redon_id);
      last_qte_obs = span_qte_obs.dataset.vidange_apres_observation === '1' ? 0 : parseFloat(span_qte_obs.getText());
    }

    if (isNaN(last_qte_obs)) {
      last_qte_obs = 0;
    }

    var diff = qte_obs - last_qte_obs;

    $V(input_qte.form._qte_diff, diff);
  },

  delReleve: function(form, redon_id) {
    if (!confirm($T('CReleveRedon-Confirm delete releve'))) {
      return;
    }

    onSubmitFormAjax(form, (function() {
      this.refreshRedon(redon_id);
    }).bind(this))
  },

  listReleves: function(constante_medicale, redon_id) {
    new Url('patients', 'ajax_list_releves')
      .addParam('constante_medicale', constante_medicale)
      .addParam('sejour_id', this.sejour_id)
      .requestModal('500px', '90%', {onClose: (this.refreshRedon.curry(redon_id)).bind(this)});
  },

  editReleve: function(releve_id) {
    new Url('patients', 'ajax_edit_releve')
      .addParam('releve_id', releve_id)
      .requestModal('500px');
  },
  /**
   * Submit all completed forms
   */
  submitAllForms: function () {
    var redons = {};

    $$('.forms_redon').each(function (form) {
      if ($V(form.qte_observee)) {
        redons[form.name] = {
          qte_observee:              $V(form.qte_observee),
          redon_id:                  $V(form.redon_id),
          date:                      $V(form.date),
          vidange_apres_observation: $V(form.vidange_apres_observation),
          qte_diff:                  $V(form._qte_diff),
        }
      }
    });

    new Url('patients', 'storeAllReleveRedons', 'dosql')
      .addParam('redons', Object.toJSON(redons))
      .requestUpdate('systemMsg', {method: 'post', onComplete: Control.Modal.refresh});
  }
};
