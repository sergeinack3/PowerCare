{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$firstname_tbl_installed}}
  <div class="small-error" id="import-table-name">

    {{if !$firstname_tbl_installed}}
      {{tr}}system-Name table does not contain data{{/tr}}
      <button class="import" onclick="ObjectPseudonymiser.goToTablePrenom();">{{tr}}system-Import table name{{/tr}}</button>
      <br/>
    {{/if}}
  </div>
{{/if}}

<div class="small-info">
  {{tr}}system-msg-Pseudonymise fields to modify{{/tr}} :
  <ul>
    <li>{{tr}}CMedecin-nom{{/tr}} : Modifi� pour un pr�nom pris au hasard dans une liste (~12000 pr�noms)</li>
    <li>{{tr}}CMedecin-jeunefille{{/tr}} : Modifi� pour un pr�nom pris au hasard dans une liste (~12000 pr�noms)</li>
  </ul>

  <br/>

  {{if $_fields}}
    {{mb_include module=system template="pseudonymise/inc_other_fields"}}
  {{/if}}
</div>
