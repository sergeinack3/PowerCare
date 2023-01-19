{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Check list de sécurité du patient</h2>
<hr />
<h3>{{$patient->_view}} ({{$patient->_age}}
{{if $patient->_annees != "??"}}- {{mb_value object=$patient field="naissance"}}{{/if}})
&mdash; Dr {{$operation->_ref_chir->_view}}
{{if $sejour->_ref_curr_affectation->_id}}- {{$sejour->_ref_curr_affectation->_ref_lit->_ref_chambre->_view}}{{/if}}
<br />

{{if $operation->libelle}}{{$operation->libelle}} &mdash;{{/if}}
{{mb_label object=$operation field=cote}} : {{mb_value object=$operation field=cote}}
&mdash; {{mb_label object=$operation field=temp_operation}} : {{mb_value object=$operation field=temp_operation}}
</h3>

<h3>
{{tr}}CSejour{{/tr}} 
du {{mb_value object=$sejour field=entree}}
au {{mb_value object=$sejour field=sortie_prevue}}
</h3>

{{mb_include module=salleOp template=inc_vw_check_lists object=$operation}}