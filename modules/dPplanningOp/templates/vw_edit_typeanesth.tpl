{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPplanningOp script=type_anesth ajax=1}}
{{if !$refresh_mode}}
<div id="type_anesth_container">
{{/if}}
  <!-- Liste des types d'anesthésie -->
  <a class="button new me-primary" href="#" onclick="TypeAnesth.openModalTypeAnesth('0');">
    {{tr}}CTypeAnesth.create{{/tr}}
  </a>

  <input type="checkbox" onchange="TypeAnesth.refreshList(this.checked ? 1 : 0);" id="showInactive_show_caduc"
         {{if $show_inactive}}checked="checked"{{/if}} name="_show_caduc" />
  <label for="showInactive_show_caduc">{{tr}}CRegleSectorisation-show-inactive{{/tr}}</label>

  <table class="tbl">
    <tr>
      <th>{{mb_title class=CTypeAnesth field=name}}</th>
      <th>{{tr}}CTypeAnesth-back-operations{{/tr}}</th>
      <th>{{mb_title class=CTypeAnesth field=ext_doc}}</th>
      <th>{{mb_title class=CTypeAnesth field=duree_postop}}</th>
      <th>{{mb_title class=CTypeAnesth field=group_id}}</th>
    </tr>
    {{foreach from=$types_anesth item=_type_anesth}}
    <tr {{if !$_type_anesth->actif}}class="hatching"{{/if}}">
      <td class="text">
        <a href="#{{$_type_anesth->_id}}" onclick="TypeAnesth.openModalTypeAnesth('{{$_type_anesth->_id}}');" title="{{tr}}CTypeAnesth-modify{{/tr}}">
          {{$_type_anesth->name}}
        </a>
      </td>
      <td>
        {{$_type_anesth->_count_operations}}
      </td>
      <td class="text {{if !$_type_anesth->ext_doc}} empty {{/if}}">
        {{mb_value object=$_type_anesth field=ext_doc}}
      </td>
      <td>{{mb_value object=$_type_anesth field=duree_postop}}</td>
      <td class="text {{if !$_type_anesth->_ref_group->_id}} empty {{/if}}">
        {{$_type_anesth->_ref_group}}
      </td>
    </tr>
    {{foreachelse}}
      <tr><td class="empty" colspan="3">{{tr}}CTypeAnesth.none{{/tr}}</td></tr>
    {{/foreach}}
  </table>
{{if !$refresh_mode}}
</div>
{{/if}}