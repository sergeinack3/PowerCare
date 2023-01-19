{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=script value=true}}
{{assign var=components value='|'|explode:$props.components}}

{{if $script}}
  <script type="text/javascript">
    Main.add(function () {
      var form = getForm('constants_configs');
      form['{{$config_name}}-{{$components[0]}}'].addSpinner({type: 'num', min: -1, string: 'num min|-1'});
      form['{{$config_name}}-{{$components[1]}}'].addSpinner({type: 'num', min: -1, string: 'num min|-1'});
      form['{{$config_name}}-{{$components[4]}}'].addSpinner({type: 'float', min: 0, string: 'float min|0'});
      form['{{$config_name}}-{{$components[5]}}'].addSpinner({type: 'float', min: 0, string: 'float min|0'});
      form['{{$config_name}}-{{$components[6]}}'].addSpinner({type: 'float', min: 0, string: 'float min|0'});
      form['{{$config_name}}-{{$components[7]}}'].addSpinner({type: 'float', min: 0, string: 'float min|0'});

      // Color picker
      var e = form["{{$config_name}}-{{$components[2]}}"];
      var options = {
        allowEmpty: true,
        change:     function (color) {
          $V(this, color ? color.toHex() : '');
        }.bind(e)
      };

      if (e.get("inherited")) {
        options.disabled = true;
      }

      e.colorPicker(options);
      e.up('.custom-value')
        .observe("conf:enable", function () {
          jQuery(e).spectrum("enable");
        })
        .observe("conf:disable", function () {
          jQuery(e).spectrum("disable");
        });

      if ($('configurations').childElements().length == 1) {
        var row = DOM.tr({id: 'config_header_row'});
        row.insert(DOM.th({className: 'category'}));
        {{foreach from=$configs item=_config name=configs_header}}
        {{if $_config.object != "default"}}
        {{if $smarty.foreach.configs_header.last && !$_config.object|instanceof:'Ox\Mediboard\Etablissement\CGroups'}}
        row.insert(DOM.th({className: 'category'}, '{{tr}}{{$context_class}}{{/tr}}'));
        {{elseif $_config.object == "global"}}
        row.insert(DOM.th({className: 'category'}, '{{tr}}config-inherit-{{$_config.object}}{{/tr}}'));
        {{else}}
        row.insert(DOM.th({className: 'category'}, '{{$_config.object|JSAttribute}}'));
        {{/if}}
        {{/if}}
        {{/foreach}}

        if ($('config_header_row')) {
          $('config_header_row').replace(row);
        } else {
          $('configurations').insert({before: row});
        }
      }
    });
  </script>
{{/if}}

<tr id="config_{{$constant}}">
  <td style="font-weight: bold; vertical-align: middle;">
    <label title="{{tr}}CConstantesMedicales-{{$constant}}-desc{{/tr}}">
      {{tr}}CConstantesMedicales-{{$constant}}{{/tr}}
    </label>
  </td>

  {{assign var=value_comment value=$config_comment.$config_name_comment|smarty:nodefaults}}

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
      <td class="text" style="vertical-align: {{if $smarty.foreach.configs.last}}top;{{else}}middle;{{/if}}">
        <div class="custom-value{{if !$smarty.foreach.configs.last && $is_inherited}} opacity-30{{/if}}">
          {{if $smarty.foreach.configs.last}}
            <input type="hidden" name="c[{{$config_name}}]"
                   value="{{'Ox\Mediboard\System\CConfiguration'|const:INHERIT}}" {{if !$is_inherited}} disabled {{/if}}
                   class="inherit-value" />
            <input type="hidden" name="c[{{$config_name_comment}}]" value="{{'Ox\Mediboard\System\CConfiguration'|const:INHERIT}}"
                   class="inherit-value" />
            <button type="button" class="edit notext compact keepEnable"
                    onclick="ConstantConfig.toggleCustom(this, true)" {{if !$is_inherited}} style="display: none;" disabled{{/if}}></button>
            <button type="button" class="cancel notext compact keepEnable me-tertiary me-small"
                    onclick="ConstantConfig.toggleCustom(this, false)" {{if $is_inherited}} style="display: none;" disabled{{/if}}></button>
            <input type="hidden" name="c[{{$config_name}}]" value="{{'|'|implode:$values}}" {{if $is_inherited}} disabled {{/if}} />
            <table class="layout" style="display: inline-table;">
              <tr>
                <td>
                  <label
                    title="{{tr}}config-dPpatient-CConstantesMedicales-selection-form-desc{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-form{{/tr}}
                    :</label>
                </td>
                <td>
                  <input type="text" class="num me-small" name="{{$config_name}}-{{$components[0]}}"
                         value="{{$values[0]}}" {{if $is_inherited}} disabled {{/if}} size="2"
                         onchange="ConstantConfig.changeValue('{{$config_name}}')" data-ignore="1" />
                </td>
                <td>
                  <label
                    title="{{tr}}config-dPpatient-CConstantesMedicales-selection-graph-desc{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-graph{{/tr}}
                    :</label>
                </td>
                <td>
                  <input type="text" class="num me-small" name="{{$config_name}}-{{$components[1]}}"
                         value="{{$values[1]}}" {{if $is_inherited}} disabled {{/if}} size="2"
                         onchange="ConstantConfig.changeValue('{{$config_name}}')" data-ignore="1" />
                </td>
                <td>
                  <label
                    title="{{tr}}config-dPpatient-CConstantesMedicales-selection-color-desc{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-color{{/tr}}
                    :</label>
                </td>
                <td>
                  <input type="hidden" name="{{$config_name}}-{{$components[2]}}" value="{{$values[2]}}"
                         onchange="ConstantConfig.changeValue('{{$config_name}}')"
                         data-ignore="1" {{if $is_inherited}} data-inherited="true" {{/if}}/>
                </td>
              </tr>
              <tr>
                <td>
                  <label
                    title="{{tr}}config-dPpatient-CConstantesMedicales-selection-mode-desc{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-mode{{/tr}}
                    :</label>
                </td>
                <td>
                  <select class="me-small" name="{{$config_name}}-{{$components[3]}}" {{if $is_inherited}} disabled {{/if}}
                          onchange="ConstantConfig.changeMode('{{$config_name}}')" data-ignore="1">
                    <option value="fixed"
                            {{if array_key_exists(3, $values) && $values[3] == 'fixed'}}selected{{/if}}>{{tr}}config-dPpatient-CConstantesMedicales-selection-mode.fixed{{/tr}}</option>
                    <option value="float"
                            {{if array_key_exists(3, $values) && $values[3] == 'float'}}selected{{/if}}>{{tr}}config-dPpatient-CConstantesMedicales-selection-mode.float{{/tr}}</option>
                  </select>
                </td>
                <td>
                  <label id="label_min_{{$constant}}"
                         title="{{tr}}config-dPpatient-CConstantesMedicales-selection-min{{if array_key_exists(3, $values)}}_{{$values[3]}}{{/if}}-desc{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-min{{/tr}}
                    :</label>
                </td>
                <td>
                  <input type="text" class="float me-small" name="{{$config_name}}-{{$components[4]}}"
                         value="{{if array_key_exists(4, $values)}}{{$values[4]}}{{/if}}" {{if $is_inherited}} disabled {{/if}}
                         size="2" onchange="ConstantConfig.changeValue('{{$config_name}}')" data-ignore="1" />
                </td>
                <td>
                  <label id="label_max_{{$constant}}"
                         title="{{tr}}config-dPpatient-CConstantesMedicales-selection-max{{if array_key_exists(3, $values)}}_{{$values[3]}}{{/if}}-desc{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-max{{/tr}}
                    :</label>
                </td>
                <td>
                  <input type="text" class="float me-small" name="{{$config_name}}-{{$components[5]}}"
                         value="{{if array_key_exists(5, $values)}}{{$values[5]}}{{/if}}" {{if $is_inherited}} disabled {{/if}}
                         size="2" onchange="ConstantConfig.changeValue('{{$config_name}}')" data-ignore="1" />
                </td>
              </tr>
              <tr>
                <td>Alerte:</td>
                <td>
                  <button id="alerts_{{$constant}}" type="button" class="edit notext me-small"
                          onclick="ConstantConfig.editAlert('{{$constant}}');">Configurer une alerte
                  </button>
                </td>
                <td>
                  <label
                    title="{{tr}}config-dPpatient-CConstantesMedicales-selection-norm_min-desc{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-norm_min{{/tr}}
                    :</label>
                </td>
                <td>
                  <input type="text" class="float me-small" name="{{$config_name}}-{{$components[6]}}"
                         value="{{if array_key_exists(6, $values)}}{{$values[6]}}{{/if}}" {{if $is_inherited}} disabled {{/if}}
                         size="2" onchange="ConstantConfig.changeValue('{{$config_name}}')" data-ignore="1" />
                </td>
                <td>
                  <label
                    title="{{tr}}config-dPpatient-CConstantesMedicales-selection-norm_max-desc{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-norm_max{{/tr}}
                    :</label>
                </td>
                <td>
                  <input type="text" class="float me-small" name="{{$config_name}}-{{$components[7]}}"
                         value="{{if array_key_exists(7, $values)}}{{$values[7]}}{{/if}}" {{if $is_inherited}} disabled {{/if}}
                         size="2" onchange="ConstantConfig.changeValue('{{$config_name}}')" data-ignore="1" />
                </td>
              </tr>
              <tr>
                <td colspan="2"
                  title="{{tr}}config-dPpatient-CConstantesMedicales-display-comment-desc{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-display-comment{{/tr}} :
                </td>
                <td class="me-small-fields" colspan="4">
                  <input type="radio" value="1" name="{{$config_name_comment}}-1"
                         onchange="ConstantConfig.changeValueComment('{{$config_name_comment}}', 1)"
                         {{if $value_comment == 1}}checked{{/if}}>{{tr}}common-Yes{{/tr}}</input>
                  <input type="radio" value="0" name="{{$config_name_comment}}-0"
                         onchange="ConstantConfig.changeValueComment('{{$config_name_comment}}', 0)"
                         {{if $value_comment == 0}}checked{{/if}}>{{tr}}common-No{{/tr}}</input>
                </td>
              </tr>
            </table>
          {{else}}
            {{* Ancestor *}}
            <label
              title="{{tr}}config-dPpatient-CConstantesMedicales-selection-form{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-form{{/tr}}
              :</label>
            {{$values[0]}}
            <label
              title="{{tr}}config-dPpatient-CConstantesMedicales-selection-graph{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-graph{{/tr}}
              :</label>
            {{$values[1]}}
            <label
              title="{{tr}}config-dPpatient-CConstantesMedicales-selection-color{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-color{{/tr}}
              :</label>
            <span
              style="display: inline-block; vertical-align: top; padding: 0; margin: 0; border: none; width: 16px; height: 16px; background-color: {{if $values[2]}}#{{$values[2]}}{{else}}transparent{{/if}}; "></span>
            {{if array_key_exists(3, $values)}}
              <label
                title="{{tr}}config-dPpatient-CConstantesMedicales-selection-mode{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-mode{{/tr}}
                :</label>
              {{tr}}config-dPpatient-CConstantesMedicales-selection-mode.{{$values[3]}}{{/tr}}
              <label
                title="{{tr}}config-dPpatient-CConstantesMedicales-selection-min{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-min{{/tr}}
                :</label>
              {{$values[4]}}
              <label
                title="{{tr}}config-dPpatient-CConstantesMedicales-selection-max{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-max{{/tr}}
                :</label>
              {{$values[5]}}
              <label
                title="{{tr}}config-dPpatient-CConstantesMedicales-selection-norm_min{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-norm_min{{/tr}}
                :</label>
              {{$values[6]}}
              <label
                title="{{tr}}config-dPpatient-CConstantesMedicales-selection-norm_max{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-selection-norm_max{{/tr}}
                :</label>
              {{$values[7]}}
            {{/if}}
            <br />
            <label
              title="{{tr}}config-dPpatient-CConstantesMedicales-display-comment-desc{{/tr}}">{{tr}}config-dPpatient-CConstantesMedicales-display-comment-court{{/tr}}
              :</label>
            {{if $value_comment}}{{tr}}common-Yes{{/tr}}{{else}}{{tr}}common-No{{/tr}}{{/if}}
          {{/if}}
        </div>
      </td>
    {{/if}}

    {{assign var=prev_value value=$value|smarty:nodefaults}}
  {{/foreach}}
</tr>
