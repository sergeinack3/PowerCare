{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=soins script=personnel_sejour ajax=true}}

<table class="tbl" id="list_timings_personnel_sejour">
  <tr>
    <th colspan="5" class="title">
      <button type="button" onclick="PersonnelSejour.editTiming('')" class="button new compact me-primary" style="float:left;">
        {{tr}}CTimeUserSejour-title-create{{/tr}}
      </button>
      {{tr}}CTimeUserSejour.all{{/tr}}
    </th>
  </tr>
  <tr>
    <th></th>
    <th style="width:20%">{{mb_title class=CTimeUserSejour field=name}}</th>
    <th>{{mb_title class=CTimeUserSejour field=description}}</th>
    <th style="width:20%">{{mb_title class=CTimeUserSejour field=time_debut}}</th>
    <th style="width:20%">{{mb_title class=CTimeUserSejour field=time_fin}}</th>
  </tr>
  {{foreach from=$timings item=_timing}}
    <tr>
      <td class="narrow">
        <button class="button edit notext compact" onclick="PersonnelSejour.editTiming('{{$_timing->_id}}')"></button>
      </td>
      <td class="narrow text">{{mb_value object=$_timing field=name}}</td>
      <td class="text">{{mb_value object=$_timing field=description}}</td>
      <td class="text">{{mb_value object=$_timing field=time_debut}}</td>
      <td class="text">{{mb_value object=$_timing field=time_fin}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="5">{{tr}}CTimeUserSejour.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>

