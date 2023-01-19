{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$serversConfiguration item=server key=ipAddr}}
  {{assign var=element value=$server.mysqlConfiguration.globalStatus.$key}}
  <td style="border-left:2px solid #999;" class="configurationValue">
    {{if $element->getValue()|is_numeric && $element->getValue() > 1024 && $row->varName != "Uptime"}}
      <span title="{{$element->getValue()}}">{{$element->getValue()|decabinary}}</span>
    {{else}}
      {{$element->getValue()}}
    {{/if}}
  </td>
  <td>
    {{if not $element->exists}}
      {{$element->varName}} n'existe pas
    {{/if}}
  </td>
{{/foreach}}