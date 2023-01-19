{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=urgences script=rpu_dashboard ajax=1}}

<script>
  Main.add(function () {
    var form = getForm("filter");
    Calendar.regField(form.date_min);
    Calendar.regField(form.date_max);

    RPUDashboard.searchPatient(getForm('filter'));

    new Url('urgences', 'vwList')
      .requestUpdate('listRPUs');
  });
</script>


<form name="filter" method="post" action="?">
  <table class="form">
    <tr><th class="title" colspan="6">{{tr}}CRPU-find_filter{{/tr}}</th></tr>
    <tr>
      <th>{{tr}}date.From_long{{/tr}}</th>
      <td class="me-w25">
        <input type="hidden" name="date_min" class="date notNull" value="{{$date_min}}"/>
      </td>
      <th>{{tr}}CPatient{{/tr}}</th>
      <td class="me-w25">
        <input type="text" name="_seek_patient" style="width: 13em;"d
               placeholder="{{tr}}fast-search{{/tr}} {{tr}}CPatient{{/tr}}" "autocomplete"/>
        <input name="patient_id" hidden>
        <button type="button" class="erase me-tertiary" onclick="$V(this.form._seek_patient, '');$V(this.form.patient_id, '');"></button>
      </td>
      <th>{{tr}}CSejour-_NDA{{/tr}}</th>
      <td class="me-w25">
        <input type="text" name="nda"/>
      </td>
    </tr>
    <tr>
      <th>{{tr}}date.To_long{{/tr}}</th>
      <td class="me-w25">
        <input type="hidden" name="date_max" class="date notNull" value="{{$date_max}}" />
      </td>
      <th>{{tr}}CPatient-_IPP{{/tr}}</th>
      <td class="me-w25">
        <input type="text" name="patient_ipp"/>
      </td>
      <th>{{tr}}CRPU-_count_extract_passages-none{{/tr}}</th>
      <td class="me-w25">
        <input type="checkbox" name="sent"/>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="6">
        <button type="button" class="search me-primary" onclick="RPUDashboard.changePage()">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="listRPUs"></div>
