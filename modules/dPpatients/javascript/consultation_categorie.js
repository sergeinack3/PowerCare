/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Defines ConsultationCategorie object actions through framework.
 *
 * @type {{loadConsultationCategories: ConsultationCategorie.loadConsultationCategories}}
 */
ConsultationCategorie = {
  /**
   * Loads `ConsultationCategorie` objects referenced by a practitioner and a exercice place objects.
   *
   * @public
   *
   * @param {string}           praticien_id      Practitioner UID used to filter load process.
   * @param {string|undefined} plage_consult_id  PlageConsult UID used to filter load process.
   * @param {string|undefined} exercice_place_id ExercicePlace UID used to filter load process if defined.
   */
  loadConsultationCategories: function (praticien_id, plage_consult_id, exercice_place_id) {
    new Url('dPpatients', 'ajax_get_consultation_categorie')
      .addParam('mediuser_id', praticien_id)
      .addParam('plage_consult_id', plage_consult_id)
      .addParam('exercice_place_id', exercice_place_id)
      .requestUpdate($('consultation_categories'));
  }
};
