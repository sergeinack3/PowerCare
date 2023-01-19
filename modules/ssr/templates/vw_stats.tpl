{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=stats}}
<script>
  Main.add(function() {
    Calendar.regField(getForm("MacroStats").date);
  });
</script>

<form name="MacroStats" method="get">
  <table class="form">
    <tr>
      <th>
        <label for="date">{{tr}}Date{{/tr}}</label>
      </th>
      <td>
        <input type="hidden" name="date" value="{{$date}}" />
      </td>
    </tr>
    <tr>
      <th>
        <label for="period">{{tr}}common-period{{/tr}}</label>
      </th>
      <td>
        <select name="period">
          <option value="day"  >{{tr}}Day  {{/tr}}</option>
          <option value="week" >{{tr}}Week {{/tr}}</option>
          <option value="month">{{tr}}Month{{/tr}}</option>
          <option value="year" >{{tr}}Year {{/tr}}</option>
        </select>
      </td>
    </tr>
    <tr>
      <th>
        <label for="type">{{tr}}ssr-type_of_stat{{/tr}}</label>
      </th>
      <td>
        <select name="type">
          <option value="CEvenementSSR">{{tr}}CEvenementSSR{{/tr}}</option>
          <option value="CActeSSR"     {{if $m == "psy"}}disabled{{/if}}>{{tr}}CActeSSR{{/tr}}     </option>
        </select>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button class="modify" type="button" onclick="Stats.reeducateurs(this);">
          {{tr}}mod-ssr-tab-reeducateur_stats{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
