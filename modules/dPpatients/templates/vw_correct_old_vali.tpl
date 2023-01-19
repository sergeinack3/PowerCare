{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $patients|@count && $can->admin}}
  <button class="cleanup" onclick="Patient.updateOldVali();">{{tr}}CPatient-Correct old VALI status{{/tr}}</button>
{{/if}}

{{mb_include module=system template=inc_pagination change_page="Patient.viewOldVali" current=$page step=50}}

<table class="tbl">
  <tr>
    <th class="title" colspan="8">
      {{tr}}CPatient-Correct old VALI status-desc{{/tr}}
    </th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th style="width: 15%;">{{mb_title class=CPatient field=nom_jeune_fille}}</th>
    <th style="width: 15%;">{{mb_title class=CPatient field=nom}}</th>
    <th style="width: 15%;">{{tr}}CPatient-prenom-desc{{/tr}}</th>
    <th style="width: 15%;">{{mb_title class=CPatient field=prenoms}}</th>
    <th style="width: 15%;">{{mb_title class=CPatient field=sexe}}</th>
    <th style="width: 15%;">{{mb_title class=CPatient field=naissance}}</th>
    <th>{{mb_title class=CPatient field=_code_insee}}</th>
  </tr>

  {{foreach from=$patients item=patient}}
      <tr>
        <td>
          <button class="edit notext" onclick="Patient.editModal('{{$patient->_id}}', null, null, Patient.viewOldVali.curry('{{$page}}'));">
              {{tr}}Edit{{/tr}}
          </button>
        </td>
        <td>{{mb_value object=$patient field=nom_jeune_fille}}</td>
        <td>{{mb_value object=$patient field=nom}}</td>
        <td>{{mb_value object=$patient field=prenom}}</td>
        <td>{{mb_value object=$patient field=prenoms}}</td>
        <td>{{mb_value object=$patient field=sexe}}</td>
        <td>{{mb_value object=$patient field=naissance}}</td>
        <td>{{mb_value object=$patient field=_code_insee}}</td>
      </tr>
  {{foreachelse}}
      <tr>
        <td colspan="8" class="empty">{{tr}}CPatient.none{{/tr}}</td>
      </tr>
  {{/foreach}}
</table>
