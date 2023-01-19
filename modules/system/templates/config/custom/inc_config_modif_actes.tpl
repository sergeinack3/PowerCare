{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=values value='|'|explode:$value}}

{{if $is_last}}
  <script type="text/javascript">
    Main.add(function() {
      var form = getForm("edit-configuration-{{$uid}}");
      form["{{$_feature}}-ambu"].addSpinner();
      form["{{$_feature}}-comp"].addSpinner();
    });

    changeValueModifActes = function(form) {
      var input = $A(form.elements['c[{{$_feature}}]']).filter(function(element) {
        return !element.hasClassName('inherit-value');
      })[0];
      var behavior = form.elements['{{$_feature}}-behavior'];
      var value = $V(behavior);

      if (value == 'sortie_sejour') {
        var ambu = form.elements['{{$_feature}}-ambu'];
        var comp = form.elements['{{$_feature}}-comp'];
        $('modif_actes-sortie_sejour').show();
        value = value + '|' + $V(ambu) + '|' + $V(comp);
      }
      else {
        $('modif_actes-sortie_sejour').hide();
      }

      $V(input, value);
    }
  </script>
  <span>
    <input type="hidden" name="c[{{$_feature}}]" value="{{$value}}">
    <select name="{{$_feature}}-behavior" {{if $is_inherited}}disabled{{/if}} onchange="changeValueModifActes(this.form);">
      {{assign var=values_list value='never|oneday|button|facturation|48h|sortie_sejour'}}
      {{if 'web100T'|module_active}}
        {{assign var=values_list value=$values_list|cat:'|facturation_web100T'}}
      {{/if}}
      {{foreach from='|'|explode:$values_list item=_conf}}
        <option value="{{$_conf}}"{{if $values[0] == $_conf}} selected{{/if}}>
          {{tr}}config-dPsalleOp-COperation-modif_actes.{{$_conf}}{{/tr}}
        </option>
      {{/foreach}}
    </select>

    <div id="modif_actes-sortie_sejour"{{if $values[0] != 'sortie_sejour'}} style="display: none;"{{/if}}>
      <label >
        Ambulatoire :
        <input type="text" class="num" size="2" name="{{$_feature}}-ambu" onchange="changeValueModifActes(this.form);"{{if $is_inherited}} disabled{{/if}}
               value="{{if array_key_exists(1, $values)}}{{$values[1]}}{{else}}1{{/if}}">
      </label>
      <label>
        Hospitalisation complète :
        <input type="text" class="num" size="2"  name="{{$_feature}}-comp" onchange="changeValueModifActes(this.form);"{{if $is_inherited}} disabled{{/if}}
               value="{{if array_key_exists(2, $values)}}{{$values[2]}}{{else}}1{{/if}}">
      </label>
    </div>
  </span>
{{else}}
  {{tr}}config-dPsalleOp-COperation-modif_actes.{{$values[0]}}{{/tr}}
  {{if $values[0] == 'sortie_sejour'}}
    {{if array_key_exists(1, $values)}}
      Ambu : {{$values[1]}}
    {{/if}}
    {{if array_key_exists(2, $values)}}
      Hospi. comp. : {{$values[2]}}
    {{/if}}
  {{/if}}
{{/if}}