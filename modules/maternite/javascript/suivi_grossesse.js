/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SuiviGrossesse = {
  suivi_grossesse_id: null,

  afterCreationConsultNow: function (_id) {
    var callback = window.parent && window.parent.Placement ? window.parent.Placement.refreshCurrPlacement : null;
    if (callback) {
      window.close();
    }
    Consultation.editModal(_id, null, '', callback);
  },

  declencherAccouchement: function (consult_id) {
    new Url('maternite', 'ajax_declenchement_accouchement')
      .addParam('consult_id', consult_id)
      .requestModal('70%', '70%');
  },
  submitConstante: function (elt) {
    if (!elt.value) {
      return false;
    }

    var form_constante = getForm('editConstante');
    var form_suivi = getForm('Suivi-Grossesse-CSuiviGrossesse-' + this.suivi_grossesse_id);

    $V(form_constante.hauteur_uterine, $V(form_suivi.hauteur_uterine));
    return onSubmitFormAjax(form_constante);
  },

  hospitalize: function(consult_id) {
    new Url('maternite', 'ajax_hospitalize')
      .addParam('consult_id', consult_id)
      .requestModal('800', '500');
  },

  toggleFieldsHospitalize: function(input) {
    var form = input.form;

    form.praticien_id[($V(input) ? 'remove' : 'add') + 'ClassName']('notNull');

    $V(form.praticien_id, input.dataset.praticien_id);
    $V(form.praticien_id_view, input.dataset.praticien_id_view);
    $V(form.uf_soins_id, input.dataset.uf_soins_id);
    $V(form.mode_entree, input.dataset.mode_entree);
    $V(form.mode_entree_id, input.dataset.mode_entree_id);
    $V(form.ATNC, input.dataset.atnc);

  },

  /**
   * Fonction permettant le bon affichage de la partie "Formulaires" du suivi de grossesse, en "sortant" les input du formulaire
   *
   * @param input
   * @param guid
   * @param type
   */
  updateHiddenInput: function(input, guid, type) {
    $V("Suivi-Grossesse-"+guid+"_"+type, $V(input));
  }
};