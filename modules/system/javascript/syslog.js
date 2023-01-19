/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SYSLOG = {
  test : function(exchange_source_name, action) {
    new Url("system", "ajax_syslog_test")
      .addParam("exchange_source_name", exchange_source_name)
      .addParam("type_action", action)
      .requestUpdate('syslog_test');
  }
};