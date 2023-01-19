{{*
 * @package Mediboard\livi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<table class="main tbl">
  {{foreach from=$patients_livi item=_patient }}
    <tr>
      <td>
        {{$_patient}}
      </td>
      <td>
        <button class="print notext compact" title="{{tr}}Print{{/tr}}">{{tr}}Print{{/tr}}</button>
      </td>
    </tr>
  {{/foreach}}
</table>
