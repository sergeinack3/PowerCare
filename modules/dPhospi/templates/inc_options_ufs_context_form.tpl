{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=can_select value=1}}

{{if $object->_class === "CSejour" && $context === "medicale" && $object->_ref_affectations|@count}}
  {{assign var=can_select value=0}}
{{/if}}

<tr>
  <th class="narrow">{{tr}}Choose{{/tr}}</th>
  <td colspan="2">
    {{assign var=field value=uf_`$context`_id}}
    {{assign var=value value=$object->$field}}

    {{assign var=found_checked value=0}}
    {{foreach from=$ufs_context item=_uf}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_uf->_guid}}')">
        <label>
          <input type="radio" name="{{$field}}_radio_view" value="{{$_uf->_id}}"
            {{if $value == $_uf->_id}}
              checked
              {{assign var=found_checked value=1}}
            {{/if}}
            {{if !$can_select}}
              disabled
            {{/if}}
                 onclick="$V(this.form.{{$field}}, this.value, false);">
          {{$_uf}}
        </label>
      </span>
    {{/foreach}}
  </td>
</tr>

<tr>
  <td></td>
  <td>
    <select name="{{$field}}" onchange="$V(this.form.{{$field}}_radio_view, '', false);" {{if !$can_select}}disabled{{/if}}>
      <option value="">{{tr}}CUniteFonctionnelle.none{{/tr}}</option>
      {{foreach from=$ufs.$context item=_uf}}
        <option value="{{$_uf->_id}}" {{if $object->$field == $_uf->_id}}selected{{/if}}>
          {{mb_value object=$_uf field=libelle}}
        </option>
      {{/foreach}}
    </select>
  </td>
</tr>