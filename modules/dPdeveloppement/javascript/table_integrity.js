/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

TableIntegrity = window.TableIntegrity || {
  sortIntegrityByColumn: function (elt, idx, compare_num) {
    var lines = elt.up('table').select('tbody>tr');
    var way = elt.hasClassName('ASC') ? 1 : -1;

    elt.up('thead').select('th>a').invoke('removeClassName', 'DESC').invoke('removeClassName', 'ASC');

    elt.addClassName((way === 1) ? 'DESC' : 'ASC');

    lines = lines.sort(function (a, b) {
      var val_a = a.select('td')[idx].textContent;
      var val_b = b.select('td')[idx].textContent;

      if (compare_num) {
        val_a = parseInt(val_a.replaceAll(' ', ''));
        val_b = parseInt(val_b.replaceAll(' ', ''));
      }

      return val_a < val_b ? way : -1 * way;
    });

    lines.each(function (line) {
      elt.up('table').down('tbody').append(line);
    })
  }
};
