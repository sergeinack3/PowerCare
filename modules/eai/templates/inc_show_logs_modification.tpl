{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th>{{tr}}CLogModificationExchange-datetime_update{{/tr}}</th>
    <th>{{tr}}CLogModificationExchange-user_id{{/tr}}</th>
    <th>{{tr}}CLogModificationExchange-data_update{{/tr}}</th>
  </tr>
  
  {{foreach from=$logs_modification item=_log_modification}}
      <tr>
        <td>{{mb_value object=$_log_modification field=datetime_update format=relative}}</td>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_log_modification->_ref_user->_guid}}')">
          {{mb_value object=$_log_modification->_ref_user field="_view"}}
        </span>
        </td>
        <td>
          <table class="tbl">
            <tr>
              <th class="section" width="50%"><strong>{{tr}}Before{{/tr}}</strong></th>
              <th class="section" width="50%">{{tr}}After{{/tr}}</th>
            </tr>
            <tr>
              <td>{{tr}}CPatient-_IPP{{/tr}} : {{$_log_modification->_data_update->before->IPP}}</td>
              <td>{{tr}}CPatient-_IPP{{/tr}} : {{$_log_modification->_data_update->after->IPP}}</td>
            </tr>
            <tr>
              <td>{{tr}}NDA{{/tr}} : {{$_log_modification->_data_update->before->NDA}}</td>
              <td>{{tr}}NDA{{/tr}} : {{$_log_modification->_data_update->after->NDA}}</td>
            </tr>
          </table>
        </td>
      </tr>
  {{/foreach}}
</table>