{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=value value='|'|explode:$value}}
{{assign var=components value='|'|explode:$_prop.components}}

{{if $is_last}}
  <script>
    changeValuesDisplayMode = function(feature) {
      var oForm = getForm("edit-configuration-{{$uid}}");
      if ($V(oForm[feature + "-mode"]) == 'time') {
        $('div-graphs_display_mode-time').show();
      }
      else {
        $('div-graphs_display_mode-time').hide();
      }
      var oForm = getForm("edit-configuration-{{$uid}}");
      var value = $V(oForm[feature + "-mode"]) + '|' + $V(oForm[feature + "-time"]);
      var input = $A(oForm.elements['c[' + feature + ']']).filter(function(element) {
        return !element.hasClassName('inherit-value');
      });
      $V(input[0], value);
    }

    Main.add(function() {
      var form = getForm("edit-configuration-{{$uid}}");
      if ($V(form['{{$_feature}}-mode']) == 'time') {
        $('div-graphs_display_mode-time').show();
      }
      form["{{$_feature}}-{{$components[1]}}"].addSpinner({type: 'num', min: 1, string:'num min|1'});
    });
  </script>

  <input type="hidden" name="c[{{$_feature}}]" value="{{'|'|implode:$value}}" {{if $is_inherited}} disabled {{/if}} />

  <label title="{{tr}}config-dPpatients-CConstantesMedicales-graphs_display_mode-mode-desc{{/tr}}">{{tr}}config-dPpatients-CConstantesMedicales-graphs_display_mode-mode{{/tr}} :</label>
  <select name="{{$_feature}}-{{$components[0]}}" {{if $is_inherited}} disabled {{/if}} onchange="changeValuesDisplayMode('{{$_feature}}')">
    <option value="classic" {{if $value[0] == 'classic'}}selected{{/if}}>{{tr}}config-dPpatients-CConstantesMedicales-graphs_display_mode-mode-classic{{/tr}}</option>
    <option value="time" {{if $value[0] == 'time'}}selected{{/if}}>{{tr}}config-dPpatients-CConstantesMedicales-graphs_display_mode-mode-time{{/tr}}</option>
  </select>

  <span id="div-graphs_display_mode-time" style="display: none;">
    <label title="{{tr}}config-dPpatients-CConstantesMedicales-graphs_display_mode-time-desc{{/tr}}">{{tr}}config-dPpatients-CConstantesMedicales-graphs_display_mode-time{{/tr}} :</label>
    <input type="text" class="num" name="{{$_feature}}-{{$components[1]}}" value="{{$value[1]}}" {{if $is_inherited}} disabled {{/if}} size="2" onchange="changeValuesDisplayMode('{{$_feature}}')"/>
  </span>
{{else}}
  <label title="{{tr}}config-dPpatients-CConstantesMedicales-graphs_display_mode-mode-desc{{/tr}}">{{tr}}config-dPpatients-CConstantesMedicales-graphs_display_mode-mode-court{{/tr}} :</label> {{tr}}config-dPpatients-CConstantesMedicales-graphs_display_mode-mode-{{$value[0]}}{{/tr}}
  {{if $value[0] == 'time'}}
    | <label title="{{tr}}config-dPpatients-CConstantesMedicales-graphs_display_mode-time-desc{{/tr}}">{{tr}}config-dPpatients-CConstantesMedicales-graphs_display_mode-time-court{{/tr}} :</label> {{$value[1]}}
  {{/if}}
{{/if}}
