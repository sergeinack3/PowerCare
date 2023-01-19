{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $item->ei_item_id}}
  <a class="button new" href="?m={{$m}}&tab=vw_edit_ei&ei_item_id=0">
    {{tr}}CEiItem.create{{/tr}}
  </a>
{{/if}}

<form name="editItem" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
  {{mb_key object=$item}}
  {{mb_class object=$item}}
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$item}}

    <tr>
      <th>{{mb_label object=$item field="nom"}}</th>
      <td>{{mb_field object=$item field="nom"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$item field="ei_categorie_id"}}</th>
      <td>
        <select name="ei_categorie_id" class="{{$item->_props.ei_categorie_id}}">
          <option value="">&mdash; {{tr}}CEiItem-ei_categorie_id-desc{{/tr}}</option>
          {{foreach from=$listCategories item=curr_cat}}
            <option value="{{$curr_cat->ei_categorie_id}}" {{if $curr_cat->ei_categorie_id == $item->ei_categorie_id}}selected{{/if}}>
              {{$curr_cat->nom}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $item->ei_item_id}}
          <button class="modify">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="confirmDeletion(this.form, {typeName: '{{tr escape="javascript"}}CEiItem.one{{/tr}}', objName: '{{$item->_view|smarty:nodefaults|JSAttribute}}'})">{{tr}}Delete{{/tr}}</button>
        {{else}}
          <button class="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
<br />

<table class="tbl">
  <tr>
    <th>{{tr}}CEiItem-nom-court{{/tr}}</th>
    <th>
      <form name="chgMode" action="?m={{$m}}" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <select name="vue_item" onchange="submit()">
          <option value="">&mdash; {{tr}}CEiCategorie.all{{/tr}}</option>
          {{foreach from=$listCategories item=curr_cat}}
            <option value="{{$curr_cat->ei_categorie_id}}" {{if $curr_cat->ei_categorie_id==$vue_item}}selected{{/if}}>
              {{$curr_cat->nom}}
            </option>
          {{/foreach}}
        </select>
      </form>
    </th>
  </tr>
  {{foreach from=$listItems item=curr_item}}
    <tr>
      <td class="text">
        <a href="?m={{$m}}&tab=vw_edit_ei&ei_item_id={{$curr_item->ei_item_id}}" title="{{tr}}CEiItem.modify{{/tr}}">
          {{$curr_item->nom}}
        </a>
      </td>
      <td class="text">
        {{$curr_item->_ref_categorie->nom}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="3" class="empty">
        {{tr}}CEiItem.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>