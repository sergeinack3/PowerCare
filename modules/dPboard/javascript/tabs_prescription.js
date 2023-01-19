/**
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

TabsPrescription = {
  frequency:           300,
  prat_id:             null,
  date:                null,
  prescription_active: false,
  plan_soins_active:   false,
  function_id:         null,

  updateComPharma: function () {
    new Url('soins', 'ajax_vw_commentaire_pharma')
      .addParam('prat_id', this.prat_id)
      .addParam('date_com_pharma', this.date)
      .addParam('function_id', this.function_id)
      .requestUpdate('com_pharma');
  },

  updateAntibios: function () {
    new Url('soins', 'ajax_vw_reeval_antibio')
      .addParam('prat_id', this.prat_id)
      .addParam('date_reeval', this.date)
      .addParam('function_id', this.function_id)
      .requestUpdate('antibios_reeval');
  },

  updateInscriptions: function () {
    if (!TabsPrescription.prescription_active) {
      return;
    }

    new Url('soins', 'httpreq_vw_bilan_list_inscriptions')
      .addParam('prat_bilan_id', this.prat_id)
      .addParam('_date_entree_prevue', this.date)
      .addParam('_date_sortie_prevue', this.date)
      .addParam('function_id', this.function_id)
      .addParam('board', '1')
      .requestUpdate('inscriptions');
  },

  updatePrescriptions: function () {
    if (!TabsPrescription.prescription_active) {
      return;
    }

    new Url('soins', 'httpreq_vw_bilan_list_prescriptions')
      .addParam('prat_bilan_id', this.prat_id)
      .addParam('_date_entree_prevue', this.date)
      .addParam('_date_sortie_prevue', this.date)
      .addParam('board', '1')
      .addParam('function_id', this.function_id)
      .requestUpdate('prescriptions_non_signees');
  },

  updateAdmAnnulees: function () {
    if (!TabsPrescription.plan_soins_active) {
      return;
    }

    new Url('planSoins', 'ajax_list_adm_annulees')
      .addParam('chir_id', this.prat_id)
      .addParam('date', this.date)
      .addParam('function_id', this.function_id)
      .requestUpdate('adm_annulees');
  },

  updateReeval: function () {
    if (!TabsPrescription.prescription_active) {
      return;
    }

    new Url('prescription', 'ajax_list_reeval')
      .addParam('chir_id', this.prat_id)
      .addParam('date', this.date)
      .addParam('function_id', this.function_id)
      .requestUpdate('reeval');
  },

  initPeriodicalUpdaters: function () {
    Object.keys(this).each(
      (function (_key) {
        // On lance seulement les fonctions qui commencent par update
        if (!_key.match(/^update/)) {
          return;
        }

        this[_key]();
        new PeriodicalExecuter((this[_key]).bind(this), this.frequency);
      }).bind(this)
    );

  },

  countPrescriptionTabs: function () {
    let countTabs = 0;

    $$('a.count').each(
      function (elt) {
        countTabs = parseInt(elt.down('small').innerHTML.replace(/(\(|\))*/, '')) + parseInt(countTabs);
      }
    );

    return countTabs;
  }
};
