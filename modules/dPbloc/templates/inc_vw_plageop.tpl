{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  .barree {
    text-decoration: line-through;
  }
</style>

{{* plages op *}}
<div class="modal_month">
  <table class="tbl">
    <tr>
      <th colspan="4" class="title">
        {{mb_include module=system template=inc_object_notes}}
        {{mb_include module=system template=inc_object_idsante400}}
        {{mb_include module=system template=inc_object_history}}
        {{if $object->verrouillage == "oui"}}<img src="images/icons/lock.png" alt="(Vérouillée)"/>{{/if}}
        {{tr}}{{$object->_class}}{{/tr}} - {{mb_value object=$object field=date}}
      </th>
    </tr>
    <tr>
      <th class="narrow">{{if $object->spec_id}}{{mb_title object=$object field=spec_id}}{{else}}{{mb_title object=$object field=chir_id}}{{/if}}</th>
      <td>
        {{if $object->spec_id}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_ref_spec->_guid}}');">{{$object->_ref_spec}}</span>
        {{else}}
          {{if $object->chir_id}}
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$object->_ref_chir}}
          {{else}}
            &mdash;
          {{/if}}
        {{/if}}
      </td>
      <th class="narrow">{{mb_title object=$object field=salle_id}}</th>
      <td><span onmouseover="ObjectTooltip.createEx(this, '{{$object->_ref_salle->_guid}}');">{{$object->_ref_salle}}</span></td>
    </tr>
    <tr>
      <th>{{mb_title object=$object field=anesth_id}}</th>
      <td>
        {{if $object->anesth_id}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$object->_ref_anesth}}
        {{else}}
          &mdash;
        {{/if}}
      </td>
      <th>horaires</th>
      <td>{{$object->debut}} &rarr; {{$object->fin}}</td>
    </tr>
  </table>
  <table class="tbl" style="max-height: 400px; overflow-y: auto;">
    <tr>
      <th colspan="6" class="title">{{tr}}COperation{{/tr}}s</th>
    </tr>
    <tr>
      <th class="narrow">{{mb_title class=COperation field=time_operation}}</th>
      <th class="narrow"></th>
      <th>{{mb_title class=COperation field=_patient_id}}</th>
      <th>{{mb_title class=COperation field=libelle}}</th>
      <th class="narrow">{{mb_title class=COperation field=_time_op}}</th>
      <th></th>
    </tr>
    {{foreach from=$object->_ref_operations item=_op}}
      {{assign var=class value=""}}
      {{if $_op->annulee}}
        {{assign var=class value="hatching barree"}}
      {{/if}}
      <tr>
        <td class="{{$class}}"><span onmouseover="ObjectTooltip.createEx(this, '{{$_op->_guid}}');">{{$_op->time_operation|date_format:$conf.time}}</span></td>
        <td style="text-align: center">
          {{if $_op->_ref_patient->_ref_photo_identite->_id}}
            {{thumbnail document=$_op->_ref_patient->_ref_photo_identite profile=small style="max-height: 30px; max-width: 30px;" alt=""}}
          {{/if}}
        </td>
        <td class="{{$class}}">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_op->_ref_patient->_guid}}');">
            {{$_op->_ref_patient}}
          </span>
        </td>
        <td class="{{$class}} compact text">{{$_op->libelle}}</td>
        <td class="{{$class}}">{{$_op->_time_op|date_format:$conf.time}}</td>
        <td class="{{$class}}">
          {{if $_op->fin_op }}
            {{me_img src="tick.png" style="height: 30px" icon="tick" class="me-success"}}
          {{/if}}
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td class="empty" colspan="6">{{tr}}COperation.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</div>
