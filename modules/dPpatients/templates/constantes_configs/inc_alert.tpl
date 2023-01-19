{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  changeFormfield = function (field) {
    var values = [];
    $$('input.' + field + '_formfield').each(function (input) {
      values.push($V(input));
    });

    var form = getForm('constant_alert_config');
    $V(form['_' + field], values.join('/'));
  }

  changeAlertValue = function () {
    var form = getForm('constant_alert_config');
    var value = $V(form._lower_threshold) + '|' + $V(form._upper_threshold) + '|' + $V(form._lower_text) + '|' + $V(form._upper_text);

    $V(form['c[{{$config_name}}]'], value);
  }

  Main.add(function () {
    $$('input.spinner').each(function (input) {
      input.addSpinner({type: 'num'});
    });
  });
</script>

{{assign var=constants_list value='Ox\Mediboard\Patients\CConstantesMedicales'|static:"list_constantes"}}
{{assign var=params value=$constants_list.$constant}}

<form action="?" method="post" name="constant_alert_config" onsubmit="return onSubmitFormAjax(this, Control.Modal.close.curry());">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="dosql" value="do_configuration_aed">
  <input type="hidden" name="object_guid" value="{{$context_guids}}">
  
  <table class="form">
    <tr>
      <th class="title" colspan="4">
        {{tr}}config-dPpatient-CConstantesMedicales-alert{{/tr}} {{tr}}CConstantesMedicales-{{$constant}}{{/tr}}
      </th>
    </tr>
    <tr>
      <th class="category"></th>
      {{foreach from=$configs item=_config name=configs_header}}
        {{if $_config.object != "default"}}
          {{if $smarty.foreach.configs_header.last && !$_config.object|instanceof:'Ox\Mediboard\Etablissement\CGroups'}}
            <th class="category">{{tr}}{{$context_class}}{{/tr}}</th>
          {{elseif $_config.object == "global"}}
            <th class="category">{{tr}}config-inherit-{{$_config.object}}{{/tr}}</th>
          {{else}}
            <th class="category">{{$_config.object}}</th>
          {{/if}}
        {{/if}}
      {{/foreach}}
    </tr>
    <tr>
      <th>
        <label for="_lower_threshold" title="{{tr}}config-dPpatient-CConstantesMedicales-alert-lower_threshold-desc{{/tr}}">
          {{tr}}config-dPpatient-CConstantesMedicales-alert-lower_threshold{{/tr}}
        </label>
      </th>
      {{assign var=prev_value value=null}}
      {{foreach from=$configs item=_config name=configs}}
        {{assign var=value value=$_config.config_parent.$config_name|smarty:nodefaults}}
        {{assign var=is_inherited value=true}}

        {{if array_key_exists($config_name, $_config.config)}}
          {{assign var=value value=$_config.config.$config_name|smarty:nodefaults}}
          {{assign var=is_inherited value=false}}
        {{else}}
          {{assign var=value value=$prev_value|smarty:nodefaults}}
        {{/if}}

        {{assign var=values value='|'|explode:$value}}
        {{if $_config.object != 'default'}}
          <td>
            <div class="custom-value{{if !$smarty.foreach.configs.last && $is_inherited}} opacity-30"
                 style="text-align: center;{{/if}}">
              {{if $smarty.foreach.configs.last}}
                <input type="hidden" name="c[{{$config_name}}]" value="{{'|'|implode:$values}}" />
                {{if array_key_exists('formfields', $params) && count($params.formfields) > 1}}
                  {{if $values[0] == ''}}
                    {{assign var=_values value='/'}}
                  {{else}}
                    {{assign var=_values value=$values[0]}}
                  {{/if}}
                  {{assign var=values_form value='/'|explode:$_values}}
                  {{foreach from=$params.formfields item=_formfield_name key=_key name=_formfield}}
                    <input type="text" class="spinner lower_threshold_formfield" name="_lower_threshold-{{$_key}}" size="2"
                           value="{{$values_form[$_key]}}" onchange="changeFormfield('lower_threshold');">
                    {{if !$smarty.foreach._formfield.last}}/{{/if}}
                  {{/foreach}}
                  <input type="hidden" size="4" name="_lower_threshold" value="{{$values[0]}}" onchange="changeAlertValue();">
                {{else}}
                  <input type="text" class="spinner" size="4" name="_lower_threshold" value="{{$values[0]}}"
                         onchange="changeAlertValue();">
                {{/if}}
                {{if $params.unit}}
                  {{$params.unit}}
                {{/if}}
              {{else}}
                {{if $values[0] != ''}}
                  {{$values[0]}}
                {{else}}
                  Non défini
                {{/if}}
              {{/if}}
            </div>
          </td>
        {{/if}}
        {{assign var=prev_value value=$value|smarty:nodefaults}}
      {{/foreach}}
    </tr>
    <tr>
      <th>
        <label for="_max_threshold" title="{{tr}}config-dPpatient-CConstantesMedicales-alert-upper_threshold-desc{{/tr}}">
          {{tr}}config-dPpatient-CConstantesMedicales-alert-upper_threshold{{/tr}}
        </label>
      </th>
      {{assign var=prev_value value=null}}
      {{foreach from=$configs item=_config name=configs}}
        {{assign var=value value=$_config.config_parent.$config_name|smarty:nodefaults}}
        {{assign var=is_inherited value=true}}

        {{if array_key_exists($config_name, $_config.config)}}
          {{assign var=value value=$_config.config.$config_name|smarty:nodefaults}}
          {{assign var=is_inherited value=false}}
        {{else}}
          {{assign var=value value=$prev_value|smarty:nodefaults}}
        {{/if}}

        {{assign var=values value='|'|explode:$value}}
        {{if $_config.object != 'default'}}
          <td>
            <div class="custom-value{{if !$smarty.foreach.configs.last && $is_inherited}} opacity-30"
                 style="text-align: center;{{/if}}">
              {{if $smarty.foreach.configs.last}}
                {{if array_key_exists('formfields', $params) && count($params.formfields) > 1}}
                  {{if $values[1] == ''}}
                    {{assign var=_values value='/'}}
                  {{else}}
                    {{assign var=_values value=$values[1]}}
                  {{/if}}
                  {{assign var=values_form value='/'|explode:$_values}}
                  {{foreach from=$params.formfields item=_formfield_name key=_key name=_formfield}}
                    <input type="text" class="spinner upper_threshold_formfield" name="_lower_threshold-{{$_key}}" size="2"
                           value="{{$values_form[$_key]}}" onchange="changeFormfield('upper_threshold');">
                    {{if !$smarty.foreach._formfield.last}}/{{/if}}
                  {{/foreach}}
                  <input type="hidden" size="4" name="_upper_threshold" value="{{$values[1]}}" onchange="changeAlertValue();">
                {{else}}
                  <input type="text" class="spinner" size="4" name="_upper_threshold" value="{{$values[1]}}"
                         onchange="changeAlertValue();">
                {{/if}}
                {{if $params.unit}}
                  {{$params.unit}}
                {{/if}}
              {{else}}
                {{if $values[1] != ''}}
                  {{$values[1]}}
                {{else}}
                  Non défini
                {{/if}}
              {{/if}}
            </div>
          </td>
        {{/if}}
        {{assign var=prev_value value=$value|smarty:nodefaults}}
      {{/foreach}}
    </tr>
    <tr>
      <th>
        <label for="_lower_text" title="{{tr}}config-dPpatient-CConstantesMedicales-alert-lower_text-desc{{/tr}}">
          {{tr}}config-dPpatient-CConstantesMedicales-alert-lower_text{{/tr}}
        </label>
      </th>
      {{assign var=prev_value value=null}}
      {{foreach from=$configs item=_config name=configs}}
        {{assign var=value value=$_config.config_parent.$config_name|smarty:nodefaults}}
        {{assign var=is_inherited value=true}}

        {{if array_key_exists($config_name, $_config.config)}}
          {{assign var=value value=$_config.config.$config_name|smarty:nodefaults}}
          {{assign var=is_inherited value=false}}
        {{else}}
          {{assign var=value value=$prev_value|smarty:nodefaults}}
        {{/if}}

        {{assign var=values value='|'|explode:$value}}
        {{if $_config.object != 'default'}}
          <td>
            <div class="custom-value{{if !$smarty.foreach.configs.last && $is_inherited}} opacity-30"
                 style="text-align: center; white-space: nowrap; text-overflow: ellipsis; width: 120px;{{/if}}">
              {{if $smarty.foreach.configs.last}}
                <textarea name="_lower_text" cols="30" rows="4" maxlength="500"
                          onchange="changeAlertValue();">{{$values[2]}}</textarea>
              {{else}}
                {{if $values[2] != ''}}
                  {{$values[2]}}
                {{else}}
                  Non défini
                {{/if}}
              {{/if}}
            </div>
          </td>
        {{/if}}
        {{assign var=prev_value value=$value|smarty:nodefaults}}
      {{/foreach}}
    </tr>
    <tr>
      <th>
        <label for="_upper_text" title="{{tr}}config-dPpatient-CConstantesMedicales-alert-upper_text-desc{{/tr}}">
          {{tr}}config-dPpatient-CConstantesMedicales-alert-upper_text{{/tr}}
        </label>
      </th>
      {{assign var=prev_value value=null}}
      {{foreach from=$configs item=_config name=configs}}
        {{assign var=value value=$_config.config_parent.$config_name|smarty:nodefaults}}
        {{assign var=is_inherited value=true}}

        {{if array_key_exists($config_name, $_config.config)}}
          {{assign var=value value=$_config.config.$config_name|smarty:nodefaults}}
          {{assign var=is_inherited value=false}}
        {{else}}
          {{assign var=value value=$prev_value|smarty:nodefaults}}
        {{/if}}

        {{assign var=values value='|'|explode:$value}}
        {{if $_config.object != 'default'}}
          <td>
            <div class="custom-value{{if !$smarty.foreach.configs.last && $is_inherited}} opacity-30"
                 style="text-align: center; white-space: nowrap; text-overflow: ellipsis; width: 120px;{{/if}}">
              {{if $smarty.foreach.configs.last}}
                <textarea name="_upper_text" cols="30" rows="4" maxlength="500"
                          onchange="changeAlertValue();">{{$values[3]}}</textarea>
              {{else}}
                {{if $values[3] != ''}}
                  {{$values[3]}}
                {{else}}
                  Non défini
                {{/if}}
              {{/if}}
            </div>
          </td>
        {{/if}}
        {{assign var=prev_value value=$value|smarty:nodefaults}}
      {{/foreach}}
    </tr>
    <tr>
      <td class="buttons" colspan="4" style="text-align: center;">
        <button type="button" class="save" onclick="changeAlertValue(); this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>