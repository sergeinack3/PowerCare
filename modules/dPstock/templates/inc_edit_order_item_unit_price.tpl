{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-order_item-{{$order_item->_id}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="dPstock" />
  <input type="hidden" name="@class" value="CProductOrderItem" />
  <input type="hidden" name="callback" value="location.reload" />
  {{mb_key object=$order_item}}

  <table class="main form">
    <tr>
      <th class="title" colspan="2">{{$order_item->_view}}</th>
    </tr>
    <tr>
      <th>{{mb_label object=$order_item field=unit_price}}</th>
      <td>{{mb_field object=$order_item field=unit_price increment=true form="edit-order_item-`$order_item->_id`"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$order_item field=_update_reference}}</th>
      <td>{{mb_field object=$order_item field=_update_reference typeEnum=checkbox}}</td>
    </tr>
    <tr>
      <th></th>
      <td>
        <button type="submit" class="save">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>

</form>