{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination total=$nb_pat current=$page change_page="TestHL7.changePageListEncounterSupplier" step=30}}

<table class="tbl">
  <tr>
    <th style="width: 20%;">{{mb_title object=$patient field=nom}}</th>
    <th style="width: 20%;">{{mb_title object=$patient field=prenom}}</th>
    <th style="width: 20%;">{{mb_title object=$patient field=nom_jeune_fille}}</th>
    <th style="width: 10%;">{{mb_title object=$patient field=sexe}}</th>
    <th>{{mb_title object=$patient field=pays_insee}}</th>
    <th>Actions</th>
  </tr>

  {{foreach from=$patients item=_patient}}
    <tr>
      <td>
        <span  onmouseover="ObjectTooltip.createEx(this, '{{$_patient->_guid}}')">
          {{mb_value object=$_patient field=nom}}
        </span>
      </td>
      <td>{{mb_value object=$_patient field=prenom}}</td>
      <td>{{mb_value object=$_patient field=nom_jeune_fille}}</td>
      <td>{{mb_value object=$_patient field=sexe}}</td>
      <td>{{mb_value object=$_patient field=pays}}</td>
      <td>
        <button type="button" class="compact search notext" onclick="TestHL7.selectPatient('{{$_patient->_id}}')">
          {{tr}}Select{{/tr}}
        </button>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CPatient.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>