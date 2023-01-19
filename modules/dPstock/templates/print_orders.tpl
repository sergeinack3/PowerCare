{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  App.print();
</script>

{{assign var=show_bill_number value=true}}
{{if $not_invoiced && !$invoiced}}
  {{assign var=show_bill_number value=false}}
{{/if}}

{{$show_bill_number}}

{{assign var=colspan value=$show_bill_number|ternary:8:7}}

<!-- Received orders -->
<table class="tbl">
  <tr>
    <th class="title" colspan="{{$colspan}}">
      Commandes {{if $not_invoiced xor $invoiced}}{{$invoiced|ternary:"facturées":"non facturées"}}{{/if}}
      entre le {{$date_min|date_format:$conf.date}} et le {{$date_max|date_format:$conf.date}}
    </th>
  </tr>
  <tr>
    <th class="narrow">Référence</th>
    <th>Num. dossier</th>
    <th>{{tr}}CProductOrder-societe_id{{/tr}}</th>
    <th>{{tr}}CProductOrder-date_ordered-court{{/tr}}</th>
    <th>{{tr}}CProductOrder-_date_received-court{{/tr}}</th>
    <th>{{tr}}CProductOrder-_total{{/tr}}</th>
    <th>{{tr}}CProductOrder-_total_tva{{/tr}}</th>
    {{if $show_bill_number}}
      <th class="narrow">{{tr}}CProductOrder-bill_number{{/tr}}</th>
    {{/if}}
  </tr>
  <tbody>
  {{foreach from=$orders item=curr_order}}
    <tr>
      <td>{{$curr_order->order_number}}</td>
      <td>
        {{if $curr_order->_ref_object}}
          {{$curr_order->_ref_object->_ref_sejour->_NDA}}
        {{/if}}
      </td>
      <td>{{$curr_order->_ref_societe->_view|truncate:25}}</td>
      <td>{{mb_value object=$curr_order field=date_ordered}}</td>
      <td>{{mb_value object=$curr_order field=_date_received}}</td>
      <td class="currency" style="text-align: right;">{{mb_value object=$curr_order field=_total}}</td>
      <td class="currency" style="text-align: right;">{{mb_value object=$curr_order field=_total_tva}}</td>
      {{if $show_bill_number}}
        <td>{{mb_value object=$curr_order field=bill_number}}</td>
      {{/if}}
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="{{$colspan}}" class="empty">{{tr}}CProductOrder.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  <tr>
    <td colspan="{{$colspan-2}}"></td>
    <th>{{mb_value object=$order field=_total}}</th>
    <th>{{mb_value object=$order field=_total_tva}}</th>
  </tr>
  </tbody>
</table>
