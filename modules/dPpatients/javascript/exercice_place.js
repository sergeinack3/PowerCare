/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ExercicePlace = {
  loadExericePlaceByPrat: function(plage_consult_id, praticien_id) {
    new Url('dPpatients', 'ajax_get_exercice_place')
      .addParam('praticien_id', praticien_id)
      .addParam('plageconsult_id', plage_consult_id)
      .requestUpdate($('exercice_places'));

    return false;
  },

  loadExericePlaceByPratForMotif: function(consult_cat_id, praticien_id) {
    new Url('dPpatients', 'ajax_get_exercice_place_motif')
      .addParam('praticien_id', praticien_id)
      .addParam('motif_id', consult_cat_id)
      .requestUpdate($('exercice_places'));

    return false;
  },
};
