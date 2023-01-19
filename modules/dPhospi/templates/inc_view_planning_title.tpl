{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $modele_used == "modele1"}}
  <tr>
    <th>{{mb_title class=CSejour field=$filter->_horodatage}}</th>
    <th>{{tr}}CPatient-Last name / First name{{/tr}}</th>
    <th>{{tr}}CPatient-Birth (Age){{/tr}}</th>
    <th>{{tr}}CPatient-sexe{{/tr}}</th>
    {{if $filter->_coordonnees}}
      <th>{{tr}}CPatient-adresse{{/tr}}</th>
      <th>{{tr}}CPatient-tel-court{{/tr}}</th>
    {{/if}}
    <th>{{tr}}COperation-libelle_of_interv{{/tr}}</th>
    <th>{{tr}}COperation-chir_id{{/tr}}</th>
    <th>{{tr}}COperation-anesth_id-court{{/tr}}</th>
    <th>{{tr}}CAffectation-rques{{/tr}}</th>
    <th>{{tr}}CChambre{{/tr}}</th>
    <th>{{tr}}CSejour-_type_admission-court{{/tr}}</th>
    <th>{{tr}}CAffectation-_duree-desc{{/tr}}</th>
    <th>{{tr}}CSejour-rques-court{{/tr}}</th>
    {{if $filter->_notes}}
      <th>{{tr}}common-Note|pl{{/tr}}</th>
    {{/if}}
  </tr>
{{else}}
  <tr>
    {{assign var=suffixe value="_title"}}
    {{mb_include module=hospi template=inc_planning/$col1$suffixe}}
    {{mb_include module=hospi template=inc_planning/$col2$suffixe}}
    {{mb_include module=hospi template=inc_planning/$col3$suffixe}}
  </tr>
  <tr>
    {{assign var=suffixe value="_header"}}
    {{mb_include module=hospi template=inc_planning/$col1$suffixe}}
    {{mb_include module=hospi template=inc_planning/$col2$suffixe}}
    {{mb_include module=hospi template=inc_planning/$col3$suffixe}}
  </tr>
{{/if}}