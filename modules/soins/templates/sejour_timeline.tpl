{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    new Url('soins', 'sejour_timeline')
    .addParam('refresh', 1)
    .requestUpdate('sejour_timeline');
  });
</script>

<div id="sejour_timeline"></div>