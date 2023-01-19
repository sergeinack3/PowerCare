{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=submit_fun value="showActes"}}
{{mb_default var=mode value=false}}
{{mb_default var=container value=tt_actes}}

<script>
  Main.add(
    function() {
      Calendar.regField(getForm('tools_actes').date_min);
      Calendar.regField(getForm('tools_actes').date_max);
    }
  );
</script>
<form name="tools_actes" method="get"
      onsubmit="return FactuTools.{{$submit_fun}}(
        {
        date_min     : this.date_min.value,
        date_max     : this.date_max.value,
        praticien_id : this.praticien_id.value,
        container    : '{{$container}}',
        mode         : '{{$mode}}'
        }
        );">
  <table class="form tbl">
    <tr>
      <th class="category" colspan="3">{{tr}}Filter{{/tr}}</th>
    </tr>
    <tr>
      <td>
        <label>
          {{tr}}common-Start date{{/tr}}
          <input type="hidden" name="date_min" value="{{$date_min}}"/>
        </label>
      </td>
      <td>
        <label>
          {{tr}}common-End date{{/tr}}
          <input type="hidden" name="date_max" value="{{$date_max}}"/>
        </label>
      </td>
      <td>
        <label>
          {{tr}}common-Practitioner{{/tr}}
          <select name="praticien_id">
            <option value="-1">&dash;{{tr}}All{{/tr}}</option>
            {{foreach from=$praticiens item=_praticien}}
              <option value="{{$_praticien->_id}}" {{if $_praticien->_id === $praticien_id}}selected{{/if}}>{{$_praticien}}</option>
            {{/foreach}}
          </select>
        </label>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="3">
        <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
        <button type="button" class="help" style="float:right" onclick="Modal.open('help_{{$help_container}}')">
          {{tr}}Help{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>