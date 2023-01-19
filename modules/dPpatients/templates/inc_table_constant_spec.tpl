{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<button type="button" class="add" onclick="constantSpec.editConstantSpec(0);">{{tr}}Add{{/tr}}</button>
<button type="button" class="change" onclick="constantSpec.refreshConstant(null, 1);">{{tr}}CConstantSpec-msg-refresh{{/tr}}</button>
<table class="tbl">
  <tr>
    <th class="title" colspan="15">{{tr}}CConstantSpec{{/tr}}</th>
  </tr>

  <tr>
    <th class="section compact narrow"></th>
    <th class="section compact narrow"></th>
    <th class="section compact narrow"></th>
    <th class="section compact"><strong>{{mb_label object=$spec field=code}}</strong></th>
    <th class="section compact"><strong>{{mb_label object=$spec field=name}}</strong></th>
    <th class="section compact"><strong>{{mb_label object=$spec field=constant_spec_id}}</strong></th>
    <th class="section compact"><strong>{{mb_label object=$spec field=unit}}</strong></th>
    <th class="section compact"><strong>{{mb_label object=$spec field=value_class}}</strong></th>
    <th class="section compact"><strong>{{mb_label object=$spec field=category}}</strong></th>
    <th class="section compact"><strong>{{mb_label object=$spec field=period}}</strong></th>
    <th class="section compact"><strong>{{mb_label object=$spec field=list}}</strong></th>
    <th class="section compact"><strong>{{mb_label object=$spec field=min_value}}</strong></th>
    <th class="section compact"><strong>{{mb_label object=$spec field=max_value}}</strong></th>
    <th class="section compact"><strong>{{mb_label object=$alert field=_nb_level_alerts}}</strong></th>
    <th class="section compact"><strong>{{mb_label object=$spec field=formule}}</strong></th>
  </tr>

  {{foreach from=$constants_spec key=_key item=_spec}}
    {{foreach from=$_spec item=_constant}}
      <tr>
        {{if $_key}}
          <td class="narrow">
            <button class="edit notext" onclick="constantSpec.editConstantSpec('{{$_constant->_id}}');"></button>
          </td>
          <td class="narrow">
            <button class="trash notext" onclick="constantSpec.deleteConstantSpec('{{$_constant->_id}}')"></button>
          </td>
        {{else}}
          <td colspan="2" class="narrow">
            <button class="edit notext button" onclick="constantSpec.editConstantSpec('{{$_constant->_id}}');"></button>
          </td>
        {{/if}}
        <td class="narrow">
            {{if $_constant->alterable}}<button type="button" class="erase notext" onclick=""></button>{{/if}}
        </td>
        <th>{{mb_value object=$_constant field=code}}</th>
        {{if $_constant->_is_constant_base}}
          <th>{{$_constant->name}}</th>
          <td>{{$_constant->_id}}</td>
          <td>{{$_constant->unit}}</td>
        {{else}}
          <th>{{tr}}{{$_constant->name}}{{/tr}}</th>
          <td>{{$_constant->_id}}</td>
          <td>{{tr}}CConstantSpec.unit.{{$_constant->_primary_unit}}{{/tr}}</td>
        {{/if}}
        <td>{{mb_value object=$_constant field=value_class}}</td>
        <td>{{mb_value object=$_constant field=category}}</td>
        <td>{{mb_value object=$_constant field=period}}</td>
        <td>{{mb_value object=$_constant field=list}}</td>
        <td>{{mb_value object=$_constant field=min_value}}</td>
        <td>{{mb_value object=$_constant field=max_value}}</td>
        {{if $_constant->_is_constant_base}}
          {{if $_constant->_ref_alert}}
            <td>{{$_constant->_ref_alert->countLevelAlert()}}</td>
          {{else}}
            <td>0</td>
          {{/if}}
        {{else}}
          {{if $_constant->_ref_alert}}
            <td>{{mb_value object=$_constant->_ref_alert field=_nb_level_alerts}}
              {{elseif $_constant->_alert}}
            <td>{{mb_value object=$_constant->_alert field=_nb_level_alerts}}
              {{else}}
            <td>0
          {{/if}}
          </td>
        {{/if}}
        <td>
          {{mb_value object=$_constant field=_view_formule}}
          {{if !$_constant->isValidFormule() && $_constant->isCalculatedConstant()}}
            {{if $_constant->_warning_formule_error}}
              {{assign var="warning_msg" value=$_constant->_warning_formule_error}}
            {{else}}
              {{assign var="warning_msg" value="CConstantSpec-msg-error formule is incorrect"}}
            {{/if}}
            <i class="fa fa-exclamation-circle constant_alert_3" title="{{tr}}{{$warning_msg}}{{/tr}}"></i>
          {{/if}}
        </td>
      </tr>
    {{/foreach}}
    {{if !$_key}}
      <tr>
        <th class="title" colspan="15"></th>
      </tr>
    {{/if}}
  {{/foreach}}
</table>