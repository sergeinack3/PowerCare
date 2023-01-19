{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$sejour->_ref_prescriptions item=_prescription key=type}}
  {{if is_array($_prescription->_counts_by_chapitre) && array_sum($_prescription->_counts_by_chapitre)}}
    <span class="dhe_sum_item">
    {{tr}}CPrescription._type_sejour.{{$type}}{{/tr}} :
    {{foreach from=$_prescription->_counts_by_chapitre key=chapitre item=_count_chapitre name=count_chapitres}}
      {{if $_count_chapitre}}
        {{$_count_chapitre}} {{tr}}CPrescription._chapitres.{{$chapitre}}{{/tr}}
        {{if !$smarty.foreach.count_chapitres.last}},{{/if}}
      {{/if}}
    {{/foreach}}
    </span>
  {{/if}}
{{/foreach}}