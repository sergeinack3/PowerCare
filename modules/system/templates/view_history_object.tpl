{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  ins {
    color: #1299DA !important;
    text-decoration: none;
  }

  del {
    color: #b92323 !important;;
  }
</style>

<table class="tbl">
  {{if $object->_id}}
    <tr>
      <th colspan="8" class="title">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}');">
        Historique de {{$object->_view}}
      </span>
      </th>
    </tr>
  {{/if}}
  <tr>
    <th>{{mb_title class=CUserLog field=user_id}}</th>
    <th colspan="2">{{mb_title class=CUserLog field=date}}</th>
    {{if $app->user_prefs.displayUTCDate}}
      <th>{{tr}}common-UTC Date{{/tr}}</th>
    {{/if}}
    <th>
      {{mb_title class=CUserLog field=type}}
    </th>
    <th>{{mb_title class=CUserLog field=fields}}</th>
    {{if $object->_id}}
      <th colspan="2">
        {{tr}}CUserLog-values_before_after{{/tr}}
      </th>
    {{/if}}
  </tr>
  {{mb_include module=system template=inc_history_object_line logs=$list}}
</table>
