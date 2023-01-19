{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Patient -->
{{if $_show_identity}}
  <th>{{tr}}CPatient-nom{{/tr}} - {{tr}}CPatient-prenom{{/tr}}</th>
{{/if}}
{{if $_display_allergy}}
  <th>{{tr}}CAntecedent-Allergie|pl{{/tr}}</th>
{{/if}}
<th>{{tr}}CPatient-Age{{/tr}}</th>
<th>{{tr}}CPatient-sexe{{/tr}}</th>
{{if $_coordonnees}}
<th>{{tr}}CPatient-tel{{/tr}}</th>
{{/if}}
{{if $_display_main_doctor}}
  <th>{{tr}}CDossierMedical-medecin_traitant_id{{/tr}}</th>
{{/if}}