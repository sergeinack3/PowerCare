{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{assign var=plage value=$object}}

<table class="form">
  <tr>
    <th class="title" colspan="2">
      {{mb_include module=system template=inc_object_notes     }}
      {{mb_include module=system template=inc_object_idsante400}}
      {{mb_include module=system template=inc_object_history   }}
      {{$plage}}
    </th>
  </tr>
  {{if $plage->chir_id}}
    <tr>
      <th>{{mb_label object=$plage field=chir_id}}</th>
      <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$plage->_ref_chir}}</td>
    </tr>
  {{/if}}
  {{if $plage->spec_id}}
    <tr>
      <th>{{mb_label object=$plage field=spec_id}}</th>
      <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$plage->_ref_spec}}</td>
    </tr>
  {{/if}}
  {{if $plage->anesth_id}}
    <tr>
      <th>{{mb_label object=$plage field=anesth_id}}</th>
      <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$plage->_ref_anesth}}</td>
    </tr>
  {{/if}}
  {{if $plage->original_owner_id}}
    <tr>
      <th>{{mb_label object=$plage field=original_owner_id}}</th>
      <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$plage->_ref_original_owner}}</td>
    </tr>
  {{/if}}
  {{if $plage->original_function_id}}
    <tr>
      <th>{{mb_label object=$plage field=original_function_id}}</th>
      <td>{{mb_include module=mediusers template=inc_vw_function mediuser=$plage->_ref_original_owner}}</td>
    </tr>
  {{/if}}
  <tr>
    <th>{{mb_label object=$plage field=date}}</th>
    <td>le {{mb_value object=$plage field=date}} de {{mb_value object=$plage field=debut}} à {{mb_value object=$plage field=fin}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$plage field=salle_id}}</th>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$plage->_ref_salle->_guid}}')"
            {{if $plage->_ref_salle && $plage->_ref_salle->color}} style="border-left: 4px solid #{{$plage->_ref_salle->color}}; padding-left: 4px;"{{/if}}>
        {{$plage->_ref_salle}}
      </span>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      {{assign var=pct value=$plage->_fill_rate}}
      {{if $pct gt 100}}
        {{assign var=pct value=100}}
      {{/if}}
      <div class="progressBar">
        <div class="bar" style="width: {{$pct}}%; text-align: center; background-color: #{{$plage->_fill_rate_color}};"></div>
        <div class="text" style="text-align: center;">
          {{$plage->_fill_rate}} % ({{$plage->_count_operations}} patients)
        </div>
      </div>
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$plage field=verrouillage}}</th>
    <td>{{mb_value object=$plage field=verrouillage}}</td>
  </tr>
  {{if $plage->max_intervention}}
    <tr>
      <th>{{mb_label object=$plage field=max_intervention}}</th>
      <td>{{mb_value object=$plage field=max_intervention}}</td>
    </tr>
  {{/if}}
  {{if $plage->max_ambu}}
    <tr>
      <th>{{mb_label object=$plage field=max_ambu}}</th>
      <td>{{mb_value object=$plage field=max_ambu}}</td>
    </tr>
  {{/if}}
  {{if $plage->max_hospi}}
    <tr>
      <th>{{mb_label object=$plage field=max_hospi}}</th>
      <td>{{mb_value object=$plage field=max_hospi}}</td>
    </tr>
  {{/if}}
</table>
