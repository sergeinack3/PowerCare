{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="7">{{tr}}CIntervHorsPlage{{/tr}} {{$date|date_format:$conf.date}}
    {{if $chir->_id}} par {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir}}{{/if}}</th>
  </tr>

  <tr>
    <th class="narrow">{{mb_title class=COperation field=time_operation}}</th>
    <th class="narrow"></th>
    <th>{{mb_title class=COperation field=_patient_id}}</th>
    <th>{{mb_title class=COperation field=chir_id}}</th>
    <th>{{mb_title class=COperation field=libelle}}</th>
    <th class="narrow">{{mb_title class=COperation field=_time_op}}</th>
    <th></th>
  </tr>
  {{foreach from=$objects item=_op}}
    <tr>
      <td><span onmouseover="ObjectTooltip.createEx(this, '{{$_op->_guid}}');">{{$_op->time_operation|date_format:$conf.time}}</span></td>
      <td style="text-align: center">
        {{if $_op->_ref_patient->_ref_photo_identite->_id}}
          {{thumbnail document=$_op->_ref_patient->_ref_photo_identite profile=small style="max-height: 30px; max-width: 30px;" alt=""}}
        {{/if}}
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_op->_ref_patient->_guid}}');">
          {{$_op->_ref_patient}}
        </span>
      </td>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_op->_ref_praticien}}
      </td>
      <td class="compact text">{{$_op->libelle}}</td>
      <td>{{$_op->_time_op|date_format:$conf.time}}</td>
      <td>
        {{if $_op->fin_op }}
          {{me_img src="tick.png" icon="tick" class="me-success" style="height: 30px"}}
        {{/if}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="7">{{tr}}CIntervHorsPlage.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>