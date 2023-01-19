{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button class="new" onclick="loadSelection(0)">{{tr}}CProductSelection-title-create{{/tr}}</button>

<form name="edit_selection" action="" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_class object=$selection}}
  {{mb_key   object=$selection}}
  <input type="hidden" name="callback" value="loadSelection" />
  <input type="hidden" name="del" value="0" />
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$selection}}
    <tr>
      <th>{{mb_label object=$selection field=name}}</th>
      <td>{{mb_field object=$selection field=name}}</td>
    </tr>
    <tr>
      <td class="button" colspan="4">
        {{if $selection->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form,{objName:'{{$selection->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

{{if $selection->_id}}
  <table class="tbl">
    <tr>
      <th class="category" colspan="2">{{tr}}CProductSelection-back-selection_items{{/tr}}</th>
    </tr>
    {{foreach from=$selection->_back.selection_items item=_item}}
      <tr>
        <td class="narrow">
          <button class="remove notext" onclick="deleteSelectionItem({{$_item->_id}})"></button>
        </td>
        <td>
          <strong onmouseover="ObjectTooltip.createEx(this, '{{$_item->_ref_product->_guid}}')">
            {{$_item}}
          </strong>
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="2" class="empty">{{tr}}CProductSelectionItem.none{{/tr}}</td>
      </tr>
    {{/foreach}}
    <tr>
      <td colspan="2">
        <form name="edit_selection_item" action="?m=dPstock" method="post" onsubmit="return onSubmitFormAjax(this)">
          {{mb_class class=CProductSelectionItem}}
          {{mb_field class=CProductSelectionItem field=selection_item_id hidden=true}}
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="callback" value="loadSelection" />
          <input type="hidden" name="cancelled" value="0" disabled="disabled" />
          {{mb_field object=$selection field=selection_id hidden=true}}
          {{mb_field class=CProductSelectionItem field=product_id form="edit_selection_item" autocomplete="true,1,50,false,true"}}
          <button class="add notext" type="submit"></button>
        </form>
      </td>
    </tr>
  </table>
{{/if}}