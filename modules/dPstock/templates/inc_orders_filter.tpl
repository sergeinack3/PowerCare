{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm("filter-orders");
    Calendar.regField(form.date_min);
    Calendar.regField(form.date_max);
  });
  
  printOrders = function (form) {
    var url = new Url();
    url.addFormData(form);
    url.pop(800, 600, "export", null, null, {}, Element.getTempIframe());
    return false;
  }
</script>

<form name="filter-orders" method="get" action="?" onsubmit="return printOrders(this)">
  <input type="hidden" name="m" value="stock" />
  <input type="hidden" name="a" value="print_orders" />
  <table class="main form">
    <tr>
      <th>
        Entre le
      </th>
      <td>
        <input type="hidden" name="date_min" class="date" value="{{$date_min}}" />
      </td>
    </tr>
    <tr>
      <th>
        et le
      </th>
      <td>
        <input type="hidden" name="date_max" class="date" value="{{$date_max}}" />
      </td>
    </tr>
    <tr>
      <th>
        Afficher les
      </th>
      <td>
        <label><input type="checkbox" name="not-invoiced" checked="checked" /> Non facturées</label>
        <label><input type="checkbox" name="invoiced" /> Facturées</label>
      </td>
    </tr>
    <tr>
      <th>
      </th>
      <td>
        <button type="submit" class="print">{{tr}}Print{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>