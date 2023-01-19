{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$function->_id}}
  <div class="small-info">{{tr}}CFunctions-choose{{/tr}}</div>
  {{mb_return}}
{{/if}}

<table class="tbl">
  <tr>
    <th colspan="3" class="title">
      <button type="button" onclick="CategoryFactu.edit(0, '{{$category->function_id}}')"
              class="add me-float-none me-margin-right-0"
              style="float: left;margin-right: -175px;">
        {{tr}}CFactureCategory-title-create{{/tr}}
      </button>
      {{tr}}CFactureCategory.all{{/tr}} ({{$categorys|@count}}): {{$function->_view}}
    </th>
  </tr>

  <tr>
    <th class="narrow">{{tr}}Action{{/tr}}</th>
    <th class="category">{{mb_title class=CFactureCategory field=libelle}}</th>
    <th class="category">{{mb_title class=CFactureCategory field=code}}</th>
  </tr>
  {{foreach from=$categorys item=_category}}
    <tr>
      <td class="narrow button">
        <button type="button" onclick="CategoryFactu.edit('{{$_category->_id}}')" class="edit notext">
          {{tr}}Modify{{/tr}}
        </button>
      </td>
      <td>{{mb_value object=$_category field=libelle}}</td>
      <td>{{mb_value object=$_category field=code}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="3">{{tr}}CFactureCategory.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>