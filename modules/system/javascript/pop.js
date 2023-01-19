/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

POP = {
  connexion: function (exchange_source_name, action) {
    new Url("system", "ajax_connexion_pop")
      .addParam("exchange_source_name", exchange_source_name)
      .addParam("type_action", action)
      .requestModal(600);
  },

  getOldEmail: function (account_id, account_name) {
    Control.Modal.close();
    new Url("messagerie", "cron_update_pop")
      .addParam("account_id", account_id)
      .addParam("import", 1)
      .requestModal(600, null, function() {
        if ($('messagerie-auto').checked) {
          POP.getOldEmail.delay(2, account_id, account_name);
        }
    });
  }
}