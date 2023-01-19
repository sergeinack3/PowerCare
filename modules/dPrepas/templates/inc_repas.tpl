{{*
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">

  {{if !$menu_id}}
    <input type="hidden" name="menu_id" value="" />
    <tr>
      <th class="category" colspan="2">Pas de repas à prévoir</th>
    </tr>
  {{else}}
    <input type="hidden" name="menu_id" value="{{$menu->menu_id}}" />
    <tr>
      <th class="category" colspan="2">{{$menu->nom}}</th>
    </tr>
    {{foreach from=$plats->_specs.type->_list item=curr_typePlat}}
      <tr>
        <th>
          <label for="{{$curr_typePlat}}">{{tr}}CPlat.type.{{$curr_typePlat}}{{/tr}}</label>
        </th>
        <td class="text">
          {{if $listPlats.$curr_typePlat|@count}}
            <select name="{{$curr_typePlat}}">
              <option value="" {{if !$repas->repas_id || !$repas->$curr_typePlat==""}}selected="selected"{{/if}}>
                {{$menu->$curr_typePlat}}
              </option>
              <optgroup label="Remplacements possibles">
                {{foreach from=$listPlats.$curr_typePlat item=curr_plat}}
                  <option value="{{$curr_plat->plat_id}}" {{if $repas->$curr_typePlat==$curr_plat->plat_id}}selected="selected"{{/if}}>
                    {{$curr_plat->nom}}
                  </option>
                {{/foreach}}
              </optgroup>
            </select>
          {{else}}
            {{$menu->$curr_typePlat}}
            <input type="hidden" name="{{$curr_typePlat}}" value="" />
          {{/if}}
        </td>
      </tr>
    {{/foreach}}
  {{/if}}

  <tr>
    <td colspan="2" class="button">
      {{if $repas->repas_id}}
        <button type="submit" class="modify">
          {{tr}}Save{{/tr}}
        </button>
        <button class="trash" type="button"
                onclick="confirmDeletion(this.form,{typeName:'{{tr escape="javascript"}}CRepas.one{{/tr}}'})">
          {{tr}}Delete{{/tr}}
        </button>
      {{else}}
        <button type="submit" class="submit">
          {{tr}}Create{{/tr}}
        </button>
      {{/if}}
    </td>
  </tr>
</table>