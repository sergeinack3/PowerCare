/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

MLLP = {
  connexion: function (exchange_source_name) {
    new Url("hl7", "ajaxConnexionMLLP")
      .addParam("exchange_source_name", exchange_source_name)
      .requestModal(600);
  },

  send: function (exchange_source_name) {
    new Url("hl7", "ajaxSendMLLP")
      .addParam("exchange_source_name", exchange_source_name)
      .requestModal(600);
  }
}
