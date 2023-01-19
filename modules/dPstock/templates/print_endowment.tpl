{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(print);
</script>

<h2>{{$endowment}}</h2>

<table class="main tbl">
  {{foreach from=$endowment->_back.endowment_items item=_item}}
    {{if !$_item->cancelled}}
      <tr>
        <td>{{mb_value object=$_item field=quantity}}</td>
        <td>{{mb_value object=$_item field=product_id}}</td>
      </tr>
    {{/if}}
    {{foreachelse}}
    <tr>
      <td colspan="2" class="empty">{{tr}}CProductEndowment.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>