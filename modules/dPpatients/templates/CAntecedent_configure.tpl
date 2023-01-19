{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=class value=CAntecedent}}

<script>
  var oTokenTypes = null;
  var oTokenAppareils = null;
  checkAntecedents = function () {
    var form = getForm('EditConfig-{{$class}}');
    var fieldTypes = $V(form["dPpatients[CAntecedent][types]"]).split('|');

    $$('input.manda_types_class').each(function (_elt) {
      var _val = _elt.value;
      if (!fieldTypes.include(_val)) {
        $V(_elt, false);
        $(_elt).up('div').hide();
      } else {
        $(_elt).up('div').show();
      }
    });
  };

  Main.add(function () {
    checkAntecedents();
    var form = getForm("EditConfig-{{$class}}");
    var fieldTypes = form["dPpatients[CAntecedent][types]"];
    var fieldTypesManda = form["dPpatients[CAntecedent][mandatory_types]"];
    var fieldAppareils = form["dPpatients[CAntecedent][appareils]"];
    oTokenTypes = new TokenField(fieldTypes, {onChange: checkAntecedents});
    oTokenTypesManda = new TokenField(fieldTypesManda);
    oTokenAppareils = new TokenField(fieldAppareils);
  });
</script>

<form name="EditConfig-{{$class}}" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_configure module=$m}}
  
  <h2>Types d'antécédent à afficher</h2>
  <table class="form">
    <tr>
      {{assign var="var" value="types"}}
      <th class="category halfPane" colspan="3">
        <label for="{{$m}}[{{$class}}][{{$var}}]" title="{{tr}}config-{{$m}}-{{$class}}-{{$var}}{{/tr}}"
               onclick="$(this.htmlFor).toggle(); $$('div.non-type').invoke('toggle');"
        >
          {{tr}}config-{{$m}}-{{$class}}-{{$var}}{{/tr}}
        </label>
      </th>
      {{assign var="var" value="appareils"}}
      <th class="category halfPane" colspan="3">
        <label for="{{$m}}[{{$class}}][{{$var}}]" title="{{tr}}config-{{$m}}-{{$class}}-{{$var}}{{/tr}}"
               onclick="$(this.htmlFor).toggle();"
        >
          {{tr}}config-{{$m}}-{{$class}}-{{$var}}{{/tr}}
        </label>
      </th>
    </tr>

    <tr>
      <td class="text" colspan="3">
        {{assign var="var" value="types"}}
        <input type="text" style="display: none" name="{{$m}}[{{$class}}][{{$var}}]" size="80" value="{{$conf.$m.$class.$var}}" />

        {{assign var=static_types     value='Ox\Mediboard\Patients\CAntecedent'|static:types}}
        {{assign var=static_non_types value='Ox\Mediboard\Patients\CAntecedent'|static:non_types}}
        {{assign var=usage_non_types  value=0}}

        {{foreach from=$all_types item=_type}}
        {{if in_array($_type, $static_non_types)}}
        {{if in_array($_type, $active_types)}}
          {{assign var=usage_non_types  value=1}}
        {{/if}}

        {{if in_array($_type, $active_types)}}
        <div class="opacity-50" style="width: 16em; float: left;">
          {{else}}
          <div class="non-type opacity-50" style="width: 16em; float: left; display: none;">
            {{/if}}

            {{else}}
            <div style="width: 16em; float: left;">
              {{/if}}
              <label>
                <input type="checkbox" name="types_antecedents[]" value="{{$_type}}"
                       onchange="oTokenTypes.toggle('{{$_type}}', this.checked)"
                       {{if in_array($_type, $active_types)}}checked="checked"{{/if}}
                />
                {{if !in_array($_type, $static_types)}}<strong>Ex:</strong>{{/if}}
                {{tr}}CAntecedent.type.{{$_type}}{{/tr}}
              </label>
            </div>
            {{/foreach}}

            {{if $usage_non_types}}
              <div class="small-warning" style="clear: both;">
                Certains types sélectionnés (grisés) sont désormais classés dans les appareils et devraient être cochés
                dans cette section.
              </div>
            {{/if}}
      </td>

      <td class="text" colspan="3">
        {{assign var="var" value="appareils"}}
        <input type="text" style="display: none" name="{{$m}}[{{$class}}][{{$var}}]" size="80" value="{{$conf.$m.$class.$var}}" />

        {{assign var=static_appareils value='Ox\Mediboard\Patients\CAntecedent'|static:appareils}}

        {{foreach from=$all_appareils item=_appareil}}
          <div style="width: 16em; float: left;">
            <label>
              <input type="checkbox" name="appareils_antecedents[]" value="{{$_appareil}}"
                     onchange="oTokenAppareils.toggle('{{$_appareil}}', this.checked)"
                     {{if in_array($_appareil, $active_appareils)}}checked="checked"{{/if}}
              />
              {{if !in_array($_appareil, $static_appareils)}}<strong>Ex:</strong>{{/if}}
              {{tr}}CAntecedent.appareil.{{$_appareil}}{{/tr}}
            </label>
          </div>
        {{/foreach}}
      </td>
    </tr>
  </table>


  <h2>Types d'antécédent obligatoires</h2>
  <table class="form">
    <tr>
      {{assign var="var" value="mandatory_types"}}
      <th class="category halfPane">
        <label
          for="{{$m}}[{{$class}}][{{$var}}]"
          title="{{tr}}config-{{$m}}-{{$class}}-{{$var}}{{/tr}}"
          onclick="$(this.htmlFor).toggle();">
          {{tr}}config-{{$m}}-{{$class}}-{{$var}}{{/tr}}
        </label>
      </th>
    </tr>
    <tr>
      <td class="text">
        {{assign var="var" value="mandatory_types"}}
        <p>
          <input type="text" style="display: none" name="{{$m}}[{{$class}}][{{$var}}]" size="80" value="{{$conf.$m.$class.$var}}" />
        </p>

        {{assign var=static_types     value='Ox\Mediboard\Patients\CAntecedent'|static:types}}
        {{assign var=static_non_types value='Ox\Mediboard\Patients\CAntecedent'|static:non_types}}
        {{assign var=usage_non_types  value=0}}

        {{foreach from=$all_mandatory_types item=_type}}
          <div {{if in_array($_type, $static_non_types)}}class="opacity-50"{{/if}} style="width: 16em; float: left;">
            <label>
              <input type="checkbox" name="types_antecedents[]" value="{{$_type}}" class="manda_types_class"
                     onchange="oTokenTypesManda.toggle('{{$_type}}', this.checked)"
                     {{if in_array($_type, $mandatory_types)}}checked="checked"{{/if}}
              />
              {{if !in_array($_type, $static_types)}}<strong>Ex:</strong>{{/if}}
              {{tr}}CAntecedent.type.{{$_type}}{{/tr}}
            </label>
          </div>
        {{/foreach}}

        {{if $usage_non_types}}
          <div class="small-warning" style="clear: both;">
            Certains types sélectionnés (grisés) sont désormais classés dans les appareils et devraient être cochés
            dans cette section.
          </div>
        {{/if}}
      </td>
    </tr>

    <tr>
      <td class="button">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>

  </table>
</form>
