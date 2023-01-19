{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination current=$page step=10 total=$total_products change_page_arg=$endowment->_id change_page="Endowment.changePage"}}

<table class="main tbl">
  <tr>
    <th class="category" colspan="2">
      <button style="float: right;" class="print notext"
              onclick="new Url('dPstock','print_endowment').addParam('endowment_id','{{$endowment->_id}}').popup()">
        {{tr}}Print{{/tr}}
      </button>
      {{tr}}CProductEndowment-back-endowment_items{{/tr}}
    </th>
  </tr>
  {{foreach from=$endowment->_back.endowment_items item=_item}}
    <tr {{if $_item->cancelled || $_item->_ref_product->cancelled}} class="opacity-30" {{/if}}>
      <td>
        {{assign var=_item_id value=$_item->_id}}
        <form name="edit_endowment_item_{{$_item->_id}}" action="" method="post"
              onsubmit="return onSubmitFormAjax(this, loadEndowment.curry({{$endowment->_id}}))">
          {{mb_class object=$_item}}
          {{mb_key   object=$_item}}
          <input type="hidden" name="del" value="0"/>
          <input type="hidden" name="cancelled" value="{{$_item->cancelled}}"/>
          <button class="trash notext" type="button"
                  onclick="$V(this.form.del, 1);this.form.onsubmit();">{{tr}}Remove{{/tr}}</button>
          <button class="{{$_item->cancelled|ternary:change:cancel}} notext" type="submit"
                  onclick="$V(this.form.cancelled, {{$_item->cancelled|ternary:0:1}})">{{tr}}{{$_item->cancelled|ternary:Restore:Cancel}}{{/tr}}</button>
          {{mb_field object=$_item field=quantity form="edit_endowment_item_$_item_id" increment=true size=2 onchange="this.form.onsubmit()"}}
        </form>

        <strong onmouseover="ObjectTooltip.createEx(this, '{{$_item->_ref_product->_guid}}')">
          {{$_item->_ref_product}}
        </strong>
      </td>
      <td>
        {{if $_item->_ref_product->_ref_stock_group && $_item->_ref_product->_ref_stock_group->_ref_location}}
          {{$_item->_ref_product->_ref_stock_group->_ref_location->name}}
        {{/if}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="2" class="empty">{{tr}}CProductEndowmentItem.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  <tr>
    <td colspan="2">
      <form name="edit_endowment_item" action="?m=dPstock" method="post" onsubmit="return onSubmitFormAjax(this)">
        {{mb_class class=CProductEndowmentItem}}
        {{mb_field class=CProductEndowmentItem field=endowment_item_id hidden=true}}
        <input type="hidden" name="del" value="0"/>
        <input type="hidden" name="callback" value="loadEndowment"/>
        <input type="hidden" name="cancelled" value="0" disabled="disabled"/>
        {{mb_field object=$endowment field=endowment_id hidden=true}}
        {{mb_field class=CProductEndowmentItem field=product_id form="edit_endowment_item" size="50" autocomplete="true,1,50,false,true"}}
        {{mb_field class=CProductEndowmentItem field=quantity form="edit_endowment_item" increment=true size=2 value=1}}
        <button class="save notext" type="submit"></button>
      </form>
    </td>
  </tr>
</table>
