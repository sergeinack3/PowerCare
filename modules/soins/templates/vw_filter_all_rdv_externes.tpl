{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    var form = getForm("print_all_rdv_externes");
    Calendar.regField(form.date_min);
    Calendar.regField(form.date_max);
  });
</script>

<form name="print_all_rdv_externes">
  <table class="form">
    <tr>
      <th class="title" colspan="2">
        {{tr}}CRDVExterne-action-Print external appointments{{/tr}}
      </th>
    </tr>
    <tr>
      <th>{{tr}}date.From_long{{/tr}}</th>
      <td>
        <input type="hidden" name="date_min" class="date notNull" value="{{$date_min}}"/>
      </td>
    </tr>
    <tr>
      <th>{{tr}}date.To_long{{/tr}}</th>
      <td>
        <input type="hidden" name="date_max" class="date notNull" value="{{$date_max}}"/>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="print" onclick="Soins.printAllExternalRDV(1, this.form);">
          {{tr}}Print{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
