{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$offline}}
  {{mb_return}}
{{/if}}

<button type="button" class="print not-printable" style="float: right;" onclick="window.print();">{{tr}}print-global-offline_planning{{/tr}}</button>
<button type="button" class="print not-printable" style="float: right;" onclick="printFiches();">{{tr}}print-cs-offline_planning{{/tr}}</button>
<button type="button" class="print not-printable" style="float: right;" onclick="this.up('table').print();">{{tr}}print-planning-offline_planning{{/tr}}</button>