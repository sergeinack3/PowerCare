{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=callback_uf value=false}}

<tr>
  <th class="narrow">{{tr}}Choose{{/tr}}</th>
  <td colspan="2">
    {{assign var=field value=uf_`$context`_id}}
    {{assign var=value value=$affectation->$field}}

    <input type="hidden" name="{{$field}}" value="{{$value}}" {{if $callback_uf}}onchange="{{$callback_uf}}"{{/if}}/>
    {{assign var=found_checked value=0}}
    {{foreach from=$ufs item=_uf}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_uf->_guid}}')">
        <label>
          <input type="radio" name="{{$field}}_radio_view" value="{{$_uf->_id}}"
            {{if $value == $_uf->_id}}
              checked
              {{assign var=found_checked value=1}}
            {{/if}}
                 onclick="$V(this.form.{{$field}}, this.value); $V(this.form.{{$field}}_view, '');
                   AffectationUf.onSubmitRefresh(this.form);">
          {{$_uf}}
        </label>
      </span>
    {{/foreach}}

    <div>
      Autre :
      {{assign var=ref_uf     value=_ref_uf_`$context`}}
      {{assign var=unfound_uf value=$affectation->$ref_uf}}
      <input type="text" class="autocomplete" name="{{$field}}_view"
             {{if !$found_checked}}value="{{$unfound_uf->_view}}{{/if}}" />
    </div>
    
    
    <script>
      Main.add(function () {
        var form = getForm("affect_uf");
        new Url("hospi", "uf_autocomplete")
        .addParam("type", "{{$context}}")
        .addParam("group_id", "{{$g}}")
        .addParam("date_debut", '{{$affectation->entree|date_format:"%Y-%m-%d"}}')
        .addParam("date_fin", '{{$affectation->sortie|date_format:"%Y-%m-%d"}}')
        .addParam("input_field", "{{$field}}_view")
        .autoComplete(form.{{$field}}_view, null, {
          minChars:           1,
          method:             "get",
          select:             "view",
          dropdown:           true,
          afterUpdateElement: function (field, selected) {
            var form = field.form;
            $V(form.{{$field}}, selected.getAttribute("id").split("-")[2]);
            AffectationUf.onSubmitRefresh(form);
            if (form.{{$field}}_radio_view && form.{{$field}}_radio_view.length) {
              $A(form.{{$field}}_radio_view).each(function (elt) {
                elt.checked = "";
              });
            }
            else {
              form.{{$field}}_radio_view.checked = "";
            }
          }
        });
      });
    </script>
  </td>
</tr>
