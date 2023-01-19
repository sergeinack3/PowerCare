{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=astreintes script=categories ajax=$ajax}}

<script>
  Main.add(function () {
    Categories.editCategories();
  });
</script>

<table class="tbl categories">
  <tr>
    <th class="narrow"></th>
    <th>{{mb_title class=CCategorieAstreinte field=name}}</th>
    <th class="narrow">{{mb_title class=CCategorieAstreinte field=color}}</th>
  </tr>
  {{foreach from=$categories item=_category}}
    <tr data-id="{{$_category->_id}}">
      <td><button type="button" class="edit notext" data-id="{{mb_value object=$_category field=oncall_category_id}}"></button></td>
      <td class="name">
          {{if !$_category->group_id}}
              <i class="fas fa-exclamation-triangle" style="color: red;" title="{{tr}}CCategorieAstreinte-msg-Group id is mandatory{{/tr}}"></i>
          {{/if}}
          {{mb_value object=$_category field=name}}
      </td>
      <td class="color">{{mb_value object=$_category field=color}}</td>
    </tr>
  {{/foreach}}
</table>
