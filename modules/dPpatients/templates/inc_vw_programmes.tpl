{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button class="new" type="button" onclick="Programme.editProgramme(0);">
  {{tr}}CProgrammeClinique-action-New program{{/tr}}
</button>

<table class="main tbl">
  <tr>
    <th class="title" colspan="6">
      {{tr}}CProgrammeClinique-List of program{{/tr}}
    </th>

    <th>
      <label for="year-filter"></label>
      <select id="year-filter" name="year" onchange="Programme.selectYear(this);">
        <option value="0">&dash;&dash; {{tr}}CProgramme-Filter by year{{/tr}}</option>
        {{foreach from=$years item=_year}}
          <option value="{{$_year}}" {{if $_year == $selected_year}}selected{{/if}}>{{$_year}}</option>
        {{/foreach}}
      </select>
    </th>

    <th>
      <label>
        <input type="checkbox" name="show_canceled" onchange="$$('tr.hatching.programme').invoke('toggle');">
        {{tr}}CPatient-action-Show canceled{{/tr}}
      </label>
    </th>
  </tr>
  <tr>
    <th class="narrow" colspan="2"></th>
    <th>{{mb_label class=CProgrammeClinique field=nom}}</th>
    <th>{{mb_label class=CProgrammeClinique field=coordinateur_id}}</th>
    <th class="text">{{mb_label class=CProgrammeClinique field=description}}</th>
    <th class="narrow">{{tr}}CProgrammeClinique-Patient number|pl-court{{/tr}}</th>
    <th class="narrow">{{tr}}CProgrammeClinique-Date first inclusion{{/tr}}</th>
    <th class="narrow">{{tr}}CProgrammeClinique-Latest exclusion date{{/tr}}</th>
  </tr>
  {{foreach from=$programmes item=_programme}}
    <tr {{if $_programme->annule}}class="hatching programme" style="display: none;" {{/if}}>
      <td class="button">
        <button type="button" class="edit notext" onclick="Programme.editProgramme('{{$_programme->_id}}');"
                title="{{tr}}common-action-Edit{{/tr}}">
          {{tr}}common-action-Edit{{/tr}}
        </button>
      </td>
      <td>
        <button type="button" class="search notext" onclick="Programme.showPatientProgramme('{{$_programme->_id}}');"
                title="{{tr}}CInclusionProgramme-action-See the patients included in the program{{/tr}}">
          {{tr}}CInclusionProgramme-action-See the patients included in the program{{/tr}}
        </button>
      </td>
      <td>{{mb_value object=$_programme field=nom}}</td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_programme->_ref_medecin->_guid}}')">
          {{$_programme->_ref_medecin->_view}}
        </span>
      </td>
      <td>{{mb_value object=$_programme field=description}}</td>
      <td>{{$_programme->_nb_patients}}</td>
      <td>{{$_programme->_date_first_inclusion|date_format:$conf.date}}</td>
      <td>{{$_programme->_date_latest_inclusion|date_format:$conf.date}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}CProgrammeClinique-No program{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
