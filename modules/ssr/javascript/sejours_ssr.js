/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SejoursSSR = {
  filter: function(input, name_table) {
    var table = $(name_table);
    table.select('tr').invoke('show');
    
    var term = $V(input);
    if (!term) return;
    
    table.select('.CPatient-view').each(function(e) {
      if (!e.innerHTML.like(term)) {
        e.up('tr').hide();
      }
    });
  }
}
