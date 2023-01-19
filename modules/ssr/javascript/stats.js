/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Stats = {
  reeducateurs: function(button) {
    var form = button.form;
    new Url('ssr', 'reeducateur_stats') .
      addElement(form.date) .
      addElement(form.type) .
      addElement(form.period) .
      requestModal(-50, -50);
  }
};
