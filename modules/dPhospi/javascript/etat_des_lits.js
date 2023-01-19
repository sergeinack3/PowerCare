/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

EtatDesLits = {
  serviceUhcd:     null,
  serviceUrgences: null,

  /*
   * Affiche une modale de l'état des lits (module dPhospi)
   *
   * @param string mode uhcd|urgence Indique quel service est à présélectionner
   */
  showModale: function (mode, date) {
    var service_id = (mode === 'uhcds') ? this.serviceUhcd : this.serviceUrgences;
    new Url("hospi", "vw_recherche")
      .addParam("services_ids[" + service_id + "]", service_id)
      .addParam("dialog", 1)
      .addParam("date_recherche", date)
      .requestModal("90%", "50%");
  }
}