/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

UniteFonctionnelle = {
  changeFilter : function(order_col, order_way) {
    console.log(order_col);
    var form = getForm('list_filter_stats_ufs');
    console.log(form);
    $V(form.order_col, order_col);
    $V(form.order_way, order_way);

    form.onsubmit();
  },
}
