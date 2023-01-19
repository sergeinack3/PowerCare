{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-{{$param->_class}}" action="?" method="post"
      onsubmit="return onSubmitFormAjax(this, function(){ Control.Modal.close(); })">
  <input type="hidden" name="m" value="patients" />
  {{mb_class object=$param}}
  {{mb_key object=$param}}

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header css_class="text" object=$param}}
    <tr>
      <th>{{mb_label object=$param field=coding_system}}</th>
      <td>
        {{mb_field object=$param field=coding_system list="edit-`$param->_class`-coding_systems"}}

        {{if "patientMonitoring"|module_active}}
          <datalist id="edit-{{$param->_class}}-coding_systems">
            {{foreach from='Ox\Mediboard\ObservationResult\CObservationValueCodingSystem'|static:"_list" item=_name key=_key}}
              <option value="{{$_key}}">{{$_name}}</option>
            {{/foreach}}
          </datalist>
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$param field=label}}</th>
      <td>{{mb_field object=$param field=label}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$param field=code}}</th>
      <td>{{mb_field object=$param field=code}}</td>
    </tr>
    {{if $param->_class == "CObservationValueType"}}
      <tr>
        <th>{{mb_label object=$param field=datatype}}</th>
        <td>{{mb_field object=$param field=datatype}}</td>
      </tr>
    {{/if}}
    {{if $param->_class == "CObservationValueUnit"}}
      <tr>
        <th>{{mb_label object=$param field=display_text}}</th>
        <td>{{mb_field object=$param field=display_text}}</td>
      </tr>
    {{/if}}
    <tr>
      <th>{{mb_label object=$param field=desc}}</th>
      <td>{{mb_field object=$param field=desc size=40}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$param field=group_id}}</th>
      <td class="text">
        <select name="group_id">
          <option value=""> &ndash; Tous</option>

          {{assign var=_usages_self  value=$param->_usages_self|@array_sum}}
          {{assign var=_usages_other value=$param->_usages_other|@array_sum}}

          {{if $_usages_other == 0}}
            {{foreach from=$groups item=_group}}
              {{if $_usages_self == 0 || $_group->_id == $g}}
                <option
                  value="{{$_group->_id}}" {{if $param->group_id == $_group->_id || !$param->_id && $_group->_id == $g}} selected {{/if}}>
                  {{if $_group->_id == $g}}
                    &raquo;
                  {{/if}}
                  {{$_group}}
                </option>
              {{/if}}
            {{/foreach}}
          {{/if}}
        </select>

        {{if $_usages_other > 0}}
          <div class="small-warning">
            Vous ne pouvez pas choisir l'établissement courant car ce paramètre est utilisé par d'autres
            établissements pour les élements suivants :
            <ul>
              {{foreach from=$param->_usages_other key=_backprop item=_count}}
                {{if $_count > 0}}
                  <li>
                    {{$_count}} {{tr}}{{$param->_class}}-back-{{$_backprop}}{{/tr}}
                  </li>
                {{/if}}
              {{/foreach}}
            </ul>
          </div>
        {{/if}}
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        {{if $param->_id}}
          <button class="modify me-primary">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash"
                  onclick="confirmDeletion(
                    this.form,
                    {typeName: '',  objName: '{{$param->_view|smarty:nodefaults|JSAttribute}}'},
                    Control.Modal.close
                    )">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit me-primary">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
