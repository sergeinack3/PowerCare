{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{* mb_include module=system template=CMbObject_view object=$order*}}

{{assign var=uniq value=""|uniqid}}

<span id="uniqid-{{$uniq}}"></span>

<table class="main form">
  <tr>
    <td>
      {{mb_label object=$return_form field=comments}}
    </td>
  </tr>
  <tr>
    <td>
      <form name="return-form-edit-{{$return_form->_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
        {{mb_class object=$return_form}}
        {{mb_key object=$return_form}}

        {{mb_field object=$return_form field=comments}}
        
        <button type="submit" class="tick">{{tr}}Save{{/tr}}</button>
      </form>
    </td>
  </tr>
</table>

<script>
  Main.add(function () {
    {{if $return_form->_id}}
    if (!$("return-forms-list")) {
      return;
    }

    var tab = $$("a[href='#return-form-{{$return_form->_id}}']")[0],
      count = "{{$return_form->_count.product_outputs}}";

    tab.down(".count").update("(" + count + ")");

    if (count > 0) {
      tab.removeClassName("empty");
    } else {
      tab.addClassName("empty");
    }
    {{else}}
    // container to remove
    var toRemove = $("uniqid-{{$uniq}}").up();
    $$("a[href='#" + toRemove.id + "']")[0].up().remove(); // tab
    toRemove.remove(); // container

    tabs = Control.Tabs.create("return-forms-list");
    tabs.setActiveTab(0);
    {{/if}}
  });
</script>

<table class="tbl">
  <tr>
    <th class="narrow"></th>
    <th>{{mb_title class=CProductOutput field=stock_id}}</th>
    <th>{{mb_title class=CProductOutput field=quantity}}</th>
    <th>{{mb_title class=CProductOutput field=unit_price}}</th>
  </tr>
  
  {{foreach from=$return_form->_ref_outputs item=_output}}
  {{assign var=_stock value=$_output->_ref_stock}}
  {{assign var=_product value=$_stock->_ref_product}}
  <tbody id="output-{{$_output->_id}}">
  {{mb_include module=stock template=inc_output}}
  </tbody>
  {{foreachelse}}
  <tr>
    <td colspan="10" class="empty">{{tr}}CProductOutput.none{{/tr}}</td>
  </tr>
  {{/foreach}}
  <tr>
    <td colspan="8" id="return-form-{{$return_form->_id}}-total" style="border-top: 1px solid #666;">
      <strong style="float: right;">
        {{tr}}Total{{/tr}} : <span class="total">{{mb_value object=$return_form field=_total}}</span>
      </strong>

      <button type="button" class="change" onclick="reloadReturnForms({{$return_form->_id}})">{{tr}}Refresh{{/tr}}</button>

      {{if $return_form->status == "new" && $return_form->_ref_outputs|@count > 0}}
      <form name="order-lock-{{$return_form->_id}}" method="post"
            onsubmit="return onSubmitFormAjax(this, function(){ document.location.reload() });">
        {{mb_class object=$return_form}}
        {{mb_key object=$return_form}}
        <input type="hidden" name="status" value="pending" />
        <button class="tick">
          {{tr}}CProductReturnForm-action-Validate{{/tr}}
        </button>
      </form>
      {{/if}}
    </td>
  </tr>
</table>
