{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  {{assign var=return_form_id value=$_output->return_form_id}}
  {{assign var=id value=$_output->_id}}
  <td>
    <form name="form-item-del-{{$_output->_id}}" action="?" method="post">
      <input type="hidden" name="m" value="stock" />
      <input type="hidden" name="dosql" value="do_output_aed" />
      <input type="hidden" name="product_output_id" value="{{$_output->_id}}" />
      <input type="hidden" name="del" value="0" />
      <button type="button" class="trash notext"
              onclick="confirmDeletion(this.form,{
                typeName:'',objName:'cet élement', ajax: 1
                }, {onComplete: function() {reloadReturnForms({{$return_form_id}}) } })"></button>
    </form>
  </td>
  <td>
    <p onmouseover="ObjectTooltip.createEx(this, '{{$_output->_guid}}')">
      {{$_output->_ref_stock->_view|truncate:80}}
    </p>
  </td>
  <td>
    <script type="text/javascript">
      quantitySubmit{{$id}} = function (element) {
        return onSubmitFormAjax(element.form, function () {
          refreshValue('CProductReturnForm-{{$return_form_id}}', '_total', function (v) {
            $('return-form-{{$return_form_id}}').down('.total').update(v)
          }, {dummy: 1});
        });
      };
    </script>
    
    <!-- Order item quantity change -->
    <form name="form-item-quantity-{{$id}}" action="?" method="post" onsubmit="return false">
      <input type="hidden" name="m" value="stock" />
      <input type="hidden" name="dosql" value="do_output_aed" />
      {{mb_key object=$_output}}
      {{mb_field object=$_output
      field=quantity
      onchange="quantitySubmit$id(this)"
      form=form-item-quantity-$id
      min=0
      size=2
      style="width: 3em;"
      increment=true}}
      {{mb_value object=$_output->_ref_stock->_ref_product field=item_title}}
    </form>
  </td>
  <td style="text-align: right;">
    {{mb_value object=$_output field=unit_price}}
  </td>
</tr>