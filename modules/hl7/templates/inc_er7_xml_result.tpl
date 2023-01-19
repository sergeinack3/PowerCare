{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$nodes item=_node}}
  {{$_node|highlight:xml}}
{{foreachelse}}
  <div class="small-info">Aucun résultat</div>
{{/foreach}}
