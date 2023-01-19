{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$firstname_tbl_installed || $conf_pat_tel || $conf_pat_addr}}
  <div class="small-error" id="import-table-name">
    {{if !$firstname_tbl_installed}}
      {{tr}}system-Name table does not contain data{{/tr}}
      <button class="import" onclick="ObjectPseudonymiser.goToTablePrenom();">{{tr}}system-Import table name{{/tr}}</button>
      <br/>
    {{/if}}

    {{if $conf_pat_tel}}
      La configuration <b>{{tr}}config-dPpatients-CPatient-addr_patient_mandatory{{/tr}}</b> est activée, il faut la désactiver pour pouvoir pseudonymiser les patients.
      <br/>
    {{/if}}

    {{if $conf_pat_addr}}
      La configuration <b>{{tr}}config-dPpatients-CPatient-tel_patient_mandatory{{/tr}}</b> est activée, il faut la désactiver pour pouvoir pseudonymiser les patients.
      <br/>
    {{/if}}
  </div>
{{/if}}


<div class="small-info">
  {{tr}}system-msg-Pseudonymise fields to modify{{/tr}} :
  <ul>
    <li>{{tr}}CPatient-nom{{/tr}} : Modifié pour un prénom pris au hasard dans une liste (~12000 prénoms)</li>
    <li>{{tr}}CPatient-nom_jeune_fille{{/tr}} : Modifié pour un prénom pris au hasard dans une liste (~12000 prénoms)</li>
    <li>{{tr}}CPatient-naissance{{/tr}} : Décalée de +/- 5 jours au hasard</li>
  </ul>

  <br/>

  {{if $_fields}}
    {{mb_include module=system template="pseudonymise/inc_other_fields"}}
  {{/if}}
</div>