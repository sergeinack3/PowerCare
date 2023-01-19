{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=pmsi script=PMSI}}
{{mb_script module=pmsi script=exportPMSI}}

<script>
  Main.add(function () {
    var form = getForm("changeDate");
    window.calendar_date_min = Calendar.regField(form.date_min);
    window.calendar_date_max = Calendar.regField(form.date_max);
    form.onsubmit();
  });
</script>

<form method="get" name="changeDate" action="?m=dPpmsi&tab=vw_current_dossiers" class="watched prepared" onsubmit="return PMSI.loadCurrentDossiers(this);">
  <input type="hidden" name="page" value="0">
  <input type="hidden" name="pageOp" value="0">
  <input type="hidden" name="pageUrg" value="0">
  <table class="form">
    <tr>
      <th>{{tr}}Since-long{{/tr}}</th>
      <td>
        <input type="hidden" class="date" id="date_min" name="date_min" onchange="$V(this.form.page, '0'); $V(this.form.pageOp, '0'); $V(this.form.pageUrg, '0')" value="{{$date_min}}">
      </td>
      <th>{{tr}}date.To_long{{/tr}}</th>
      <td>
        <input type="hidden" class="date" id="date_max" name="date_max" onchange="$V(this.form.page, '0'); $V(this.form.pageOp, '0'); $V(this.form.pageUrg, '0')" value="{{$date_max}}">
      </td>
      <th>{{mb_label object=$sejour_filter field=type}}</th>
      <td>
        <select name="types" size="5" multiple>
          <option value=""
          {{if !is_array($types) || in_array("", $types)}}selected="selected"{{/if}}}>&mdash; {{tr}}CSejour.type.all{{/tr}}</option>
          {{foreach from=$sejour_filter->_specs.type->_locales key=key_hospi item=curr_hospi}}
            <option value="{{$key_hospi}}" {{if is_array($types) && in_array($key_hospi, $types)}}selected="selected"{{/if}}>
              {{$curr_hospi}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="6">
        <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<br/>
<div id="list-dossiers" class="me-no-align me-align-auto"></div>
