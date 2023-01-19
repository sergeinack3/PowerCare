{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Control.Tabs.setTabCount("list-return-forms-{{$status}}", {{$return_forms|@count}});
</script>

<table class="main tbl me-no-align me-no-border-radius-top">
  <tr>
    <th class="narrow">{{mb_title class=CProductReturnForm field=return_number}}</th>
    <th class="narrow">{{mb_title class=CProductReturnForm field=datetime}}</th>
    <th class="narrow">{{mb_title class=CProductReturnForm field=supplier_id}}</th>
    <th>{{tr}}CProductReturnForm-back-product_outputs{{/tr}}</th>
    <th>{{mb_title class=CProductReturnForm field=_total}}</th>
    <th class="narrow"></th>
  </tr>

  {{foreach from=$return_forms item=_return_form}}
    <tr>
      <td onmouseover="ObjectTooltip.createEx(this, '{{$_return_form->_guid}}')">
        {{mb_value object=$_return_form field=return_number}}
      </td>
      <td>{{mb_value object=$_return_form field=datetime}}</td>
      <td>{{mb_value object=$_return_form field=supplier_id tooltip=true}}</td>
      <td>{{$_return_form->_count.product_outputs}}</td>
      <td>{{mb_value object=$_return_form field=_total}}</td>
      <td>
        {{if $status == "new"}}
          <form name="return-form-new-{{$_return_form->_id}}" method="post"
                onsubmit="return onSubmitFormAjax(this, refreshAll);">
            {{mb_class object=$_return_form}}
            {{mb_key object=$_return_form}}
            <input type="hidden" name="status" value="pending" />
            <button class="tick compact">
              {{tr}}CProductReturnForm-action-Validate{{/tr}}
            </button>
          </form>
        {{elseif $status == "pending"}}
          <form name="return-form-reset-{{$_return_form->_id}}" method="post"
                onsubmit="return onSubmitFormAjax(this, refreshAll);">
            {{mb_class object=$_return_form}}
            {{mb_key object=$_return_form}}
            <input type="hidden" name="status" value="new" />
            <button class="left compact">
              {{tr}}CProductReturnForm-action-Reset{{/tr}}
            </button>
          </form>
          <form name="return-form-send-{{$_return_form->_id}}" method="post"
                onsubmit="return onSubmitFormAjax(this, refreshAll);">
            {{mb_class object=$_return_form}}
            {{mb_key object=$_return_form}}
            <input type="hidden" name="status" value="sent" />
            <button class="send compact">
              {{tr}}CProductReturnForm-action-Send{{/tr}}
            </button>
          </form>
        {{elseif $status == "sent"}}
          <form name="return-form-unsend-{{$_return_form->_id}}" method="post"
                onsubmit="return onSubmitFormAjax(this, refreshAll);">
            {{mb_class object=$_return_form}}
            {{mb_key object=$_return_form}}
            <input type="hidden" name="status" value="pending" />
            <button class="cancel compact">
              {{tr}}CProductReturnForm-action-Unsend{{/tr}}
            </button>
          </form>
        {{/if}}

        <button class="print notext compact" onclick="ReturnForm.print({{$_return_form->_id}})">{{tr}}Print{{/tr}}</button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="5">{{tr}}CProductReturnForm.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>