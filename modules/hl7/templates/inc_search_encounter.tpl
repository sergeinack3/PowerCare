{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=hl7 template=inc_banner_patient_hl7}}

<form name="filter-pat-encounter-supplier" method="get" onsubmit="return TestHL7.refreshListEncounterSupplier(this)">
  <input type="hidden" name="m" value="hl7" />
  <input type="hidden" name="a" value="ajax_list_encounter" />
  <input type="hidden" name="page" value="0" />
  <fieldset>
    <legend>Critères de recherche</legend>
    <table class="form me-no-box-shadow">
      <tr>
        <th>{{mb_title class="CPatient" field=nom}}</th>
        <td>{{mb_field class="CPatient" field=nom}}</td>
      </tr>
      <tr>
        <th>{{mb_title class="CPatient" field=prenom}}</th>
        <td>{{mb_field class="CPatient" field=prenom}}</td>
      </tr>
      <tr>
        <th>{{mb_title class="CPatient" field=nom_jeune_fille}}</th>
        <td>{{mb_field class="CPatient" field=nom_jeune_fille}}</td>
      </tr>
      <tr>
        <th>{{mb_title class="CPatient" field=sexe emptyLabel="All"}}</th>
        <td>{{mb_field class="CPatient" field=sexe emptyLabel="All"}}</td>
      </tr>
      <tr>
        <td class="button" colspan="2">
          <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
        </td>
      </tr>
    </table>
  </fieldset>
</form>
<br/>
<div id="list_encounter"></div>