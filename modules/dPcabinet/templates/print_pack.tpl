{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$listCr item=curr_cr}}
  <h1 class="newpage">Nouveau document</h1>
  <p>{{$curr_cr->document}}</p>
{{/foreach}}