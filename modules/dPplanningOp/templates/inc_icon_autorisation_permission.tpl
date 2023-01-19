{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


{{assign var=last_autorisation_permission value=$sejour->_ref_last_autorisation_permission}}
{{assign var=perm_status value=""}}
{{assign var=perm_class  value=""}}

{{if $last_autorisation_permission && $last_autorisation_permission->_id}}
  {{assign var=perm_class  value="texticon-perm-planned"}}

  {{if $curr_affectation && !$curr_affectation->_in_permission && ($last_autorisation_permission->debut < $dtnow) && ($last_autorisation_permission->_fin > $dtnow)}}
    {{assign var=perm_class  value="texticon-perm-progress"}}
  {{elseif $curr_affectation && $curr_affectation->_in_permission}}
    {{assign var=perm_class  value="texticon-perm-finished"}}
  {{/if}}

  <span class="texticon {{$perm_class}}"
    {{if $last_autorisation_permission && $last_autorisation_permission->_id}}
      onmouseover="ObjectTooltip.createEx(this, '{{$last_autorisation_permission->_guid}}');"
    {{/if}}>
    {{tr}}CAutorisationPermission-icon-Perm{{/tr}}
  </span>

  {{if $curr_affectation}}
    {{mb_include module=soins template=inc_button_permission affectation=$curr_affectation}}
  {{/if}}
{{/if}}
