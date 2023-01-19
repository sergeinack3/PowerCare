/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SMTP = {
  connexion: function (exchange_source_name) {
    new Url("system", "ajax_connexion_smtp")
      .addParam("exchange_source_name", exchange_source_name)
      .addParam("type_action", "connexion")
      .requestModal(600);
  },

  envoi: function (exchange_source_name) {
    new Url("system", "ajax_connexion_smtp")
      .addParam("exchange_source_name", exchange_source_name)
      .addParam("type_action", "envoi")
      .requestModal(600);
  }
}