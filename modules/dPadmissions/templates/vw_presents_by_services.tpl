{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table id="presents_table" class="tbl" style="text-align: center;">
  <tr>
    <th class="title me-text-align-center" colspan="{{math equation='x+1' x=$services|@count}}">
      <a class="not-printable" style="display: inline;" href="#" onclick="PatientsPresents.vuedateGlobalePresent('{{$lastmonth}}');">&lt;&lt;&lt;</a>
      {{$date|date_format:"%B"|capitalize}} {{$date|date_format:"%Y"}}
      <a class="not-printable" style="display: inline;" href="#" onclick="PatientsPresents.vuedateGlobalePresent('{{$nextmonth}}');">&gt;&gt;&gt;</a>
      <button class="print notext me-float-right not-printable" title="{{tr}}Print{{/tr}}" onclick="PatientsPresents.printGlobalPresents()"></button>
    </th>
  </tr>

  <tr>
    <th class="narrow me-text-align-center">Date</th>
    {{foreach from=$services item=_nb_service key=_service}}
      <th class="me-text-align-center">{{$_service}}</th>
    {{/foreach}}
  </tr>

  {{foreach from=$results key=day item=counts}}
    <tr>
      {{assign var=day_number value=$day|date_format:"%w"}}
      <td  style="text-align: right;
      {{if array_key_exists($day, $bank_holidays)}}
        background-color: #fc0;
      {{elseif $day_number == '0' || $day_number == '6'}}
        background-color: #ccc;
      {{/if}}">
        <strong>
          {{$day|date_format:"%a"|upper|substr:0:1}}
          {{$day|date_format:"%d"}}
        </strong>
      </td>
      {{foreach from=$services item=_nb_service key=_service}}
        {{if isset($counts.$_service|smarty:nodefaults)}}
          <td class="me-text-align-center">{{$counts.$_service}}</td>
        {{else}}
          <td class="empty me-text-align-center">-</td>
        {{/if}}
      {{/foreach}}
    </tr>
  {{/foreach}}
  <tr>
    <td><strong>Total</strong></td>
    {{foreach from=$services item=_nb_service key=_service}}
      <td class="me-text-align-center"><strong>{{$_nb_service}}</strong></td>
    {{/foreach}}
  </tr>
</table>