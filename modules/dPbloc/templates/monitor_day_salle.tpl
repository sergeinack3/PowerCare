{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

  Main.add(function () {
    Calendar.regField(getForm("dateMonitor").date, null, {noView: true});
  });
  
  reloadMonitor = function(form) {
    Control.Modal.close();
    EditPlanning.monitorDaySalle($V(form.salle_id), $V(form.date));
    return false;
  }

</script>

<form action="?" name="dateMonitor" method="get" onsubmit="return reloadMonitor(this)">

<table class="main">
  <tr>
    <th class="title halfPane">
      <select name="salle_id" onchange="this.form.onsubmit()">
      {{foreach from=$listBlocs item=_bloc}}
        <optgroup label="{{$_bloc}}">
          {{foreach from=$_bloc->_ref_salles item=_salle}}
          <option value={{$_salle->_id}} {{if $_salle->_id == $salle->_id}}selected="selected"{{/if}}>
            {{$_salle->_shortview}}
          </option>
          {{/foreach}}
        </optgroup>
      {{/foreach}}
      </select>
    </th>
    <th class="title">
      à partir du {{$date|date_format:$conf.longdate}}
      <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.onsubmit()" />
    </th>
  </tr>
</table>

</form>

<table class="planningBloc">
  <tr>
    <th class="narrow">Date</th>
    {{foreach from=$listHours item=_hour}}
      <th colspan="4" class="heure">{{$_hour}}:00</th>
    {{/foreach}}
  </tr>
  {{foreach from=$listDays key=curr_day item=plagesPerDay}}
    {{assign var="keyHorsPlage" value="$curr_day-HorsPlage"}}
    <tr>
      <td class="salle" style="width: 1%; white-space: nowrap;" {{if $affichages.$key_bloc.$keyHorsPlage|@count}}rowspan="2"{{/if}}>
        {{$curr_day|date_format:"sem. %V | %d/%m"}}
      </td>
      {{mb_include template=inc_planning_bloc_line bloc=$salle->_ref_bloc _salle=$salle}}
    </tr>

    {{if $affichages.$key_bloc.$keyHorsPlage|@count}}
    <tr>
      <td colspan="100" class="empty">
        <a href="?m=dPbloc&tab=vw_urgences&date={{$curr_day}}">
          + {{$affichages.$key_bloc.$keyHorsPlage|@count}} intervention(s) hors plage
        </a>
      </td>
    </tr>
    {{/if}}
  {{/foreach}}
</table>
