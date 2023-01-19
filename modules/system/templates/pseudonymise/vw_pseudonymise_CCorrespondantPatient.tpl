{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$firstname_tbl_installed}}
  <div class="small-error" id="import-table-name">
    {{tr}}system-Name table does not contain data{{/tr}}
    <button class="import" onclick="ObjectPseudonymiser.goToTablePrenom();">{{tr}}system-Import table name{{/tr}}</button>
  </div>
{{/if}}

<div class="small-info">
  {{tr}}system-msg-Pseudonymise fields to modify{{/tr}} :
  <ul>
    <li>{{tr}}CCorrespondantPatient-nom{{/tr}} : Modifié pour un prénom pris au hasard dans une liste (~12000 prénoms)</li>
    <li>{{tr}}CCorrespondantPatient-nom_jeune_fille{{/tr}} : Modifié pour un prénom pris au hasard dans une liste (~12000 prénoms)</li>
    <li>{{tr}}CPatient-naissance{{/tr}} : Décalée de +/- 5 jours au hasard</li>
  </ul>

  <br/>

  {{if $_fields}}
    {{mb_include module=system template="pseudonymise/inc_other_fields"}}
  {{/if}}
</div>