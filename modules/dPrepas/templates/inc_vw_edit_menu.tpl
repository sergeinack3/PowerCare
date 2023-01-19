{{*
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <td colspan="2">
    <a class="button new me-primary me-margin-top-4" href="?m={{$m}}&tab=vw_edit_menu&menu_id=0">
      Créer un nouveau menu
    </a>
  </td>
</tr>
<tr>
  <td class="halfPane">
    <table class="tbl">
      <tr>
        <th>Nom</th>
        <th>Type</th>
        <th>Plats</th>
      </tr>
      {{foreach from=$listMenus item=curr_menu}}
        <tr>
          <td>
            <a href="?m={{$m}}&tab=vw_edit_menu&menu_id={{$curr_menu->menu_id}}" title="Modifier le repas">
              {{$curr_menu->nom}}
            </a>
          </td>
          <td>
            <a href="?m={{$m}}&tab=vw_edit_menu&menu_id={{$curr_menu->menu_id}}" title="Modifier le repas">
              {{$curr_menu->_ref_typerepas->nom}}
            </a>
          </td>
          <td class="text">
            <a href="?m={{$m}}&tab=vw_edit_menu&menu_id={{$curr_menu->menu_id}}" title="Modifier le repas">
              {{assign var="premier" value=1}}
              {{foreach from=$typePlats->_specs.type->_list item=curr_typePlat}}
                {{if $curr_menu->$curr_typePlat}}
                  {{if $premier}}
                    {{assign var="premier" value=0}}
                  {{else}}
                    &mdash;
                  {{/if}}
                  {{$curr_menu->$curr_typePlat}}
                {{/if}}
              {{/foreach}}
            </a>
          </td>
        </tr>
      {{/foreach}}
    </table>
  </td>
  <td class="halfPane">
    <form name="editMenu" action="?m={{$m}}&tab=vw_edit_menu" method="post" onsubmit="return checkForm(this)">
      <input type="hidden" name="m" value="dPrepas" />
      <input type="hidden" name="dosql" value="do_menu_aed" />
      <input type="hidden" name="menu_id" value="{{$menu->menu_id}}" />
      <input type="hidden" name="group_id" value="{{if $menu->menu_id}}{{$menu->group_id}}{{else}}{{$g}}{{/if}}" />
      <input type="hidden" name="del" value="0" />
      <table class="form">
        <tr>
          {{if $menu->menu_id}}
            <th class="title modify" colspan="2">Modification du menu {{$menu->_view}}</th>
          {{else}}
            <th class="title me-th-new" colspan="2">Création d'un menu</th>
          {{/if}}
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="nom"}}</th>
          <td>{{mb_field object=$menu field="nom"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="typerepas"}}</th>
          <td colspan="3">
            <select name="typerepas" class="{{$menu->_props.typerepas}}">
              {{foreach from=$listTypeRepas item=curr_typerepas}}
                <option value="{{$curr_typerepas->typerepas_id}}"
                        {{if $menu->typerepas==$curr_typerepas->typerepas_id}}selected="selected"{{/if}}>
                  {{$curr_typerepas->nom}}
                </option>
              {{/foreach}}
            </select>
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="debut"}}</th>
          <td>{{mb_field object=$menu field="debut" form="editMenu" register=true}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="repetition"}}</th>
          <td>
            1 sem. /
            {{html_options name="repetition" options=$listRepeat class=$menu->_props.repetition selected=$menu->repetition}}
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="nb_repet"}}</th>
          <td>{{mb_field object=$menu field="nb_repet"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="plat1"}}</th>
          <td>{{mb_field object=$menu field="plat1"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="plat2"}}</th>
          <td>{{mb_field object=$menu field="plat2"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="plat3"}}</th>
          <td>{{mb_field object=$menu field="plat3"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="plat4"}}</th>
          <td>{{mb_field object=$menu field="plat4"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="plat5"}}</th>
          <td>{{mb_field object=$menu field="plat5"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="boisson"}}</th>
          <td>{{mb_field object=$menu field="boisson"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="pain"}}</th>
          <td>{{mb_field object=$menu field="pain"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="diabete"}}</th>
          <td>{{mb_field object=$menu field="diabete"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="sans_sel"}}</th>
          <td>{{mb_field object=$menu field="sans_sel"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="sans_residu"}}</th>
          <td>{{mb_field object=$menu field="sans_residu"}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$menu field="modif"}}</th>
          <td>{{mb_field object=$menu field="modif"}}</td>
        </tr>
        <tr>
          <td class="button" colspan="2">
            {{if $menu->menu_id}}
              <button class="submit">{{tr}}Edit{{/tr}}</button>
              <button class="trash" type="button"
                      onclick="confirmDeletion(this.form, {typeName: 'le menu', objName: '{{$menu->_view|smarty:nodefaults|JSAttribute}}'})">{{tr}}Delete{{/tr}}</button>
            {{else}}
              <button class="submit">{{tr}}Create{{/tr}}</button>
            {{/if}}
          </td>
        </tr>
      </table>
    </form>
  </td>
</tr>