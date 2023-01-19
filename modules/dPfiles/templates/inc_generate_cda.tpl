{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=eai template='report/inc_report'}}


{{if !$report->getItems()}}
    <div class="small-info">{{tr}}CDA-msg-CDA generated{{/tr}}</div>
{{/if}}
