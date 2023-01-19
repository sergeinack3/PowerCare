{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=soins script=soins ajax=$ajax}}

{{mb_default var=from_placement value=0}}
{{mb_default var=float value=""}}

{{if !$affectation->_affectation_perm_id && !$curr_affectation->_in_permission && ($last_autorisation_permission->debut < $dtnow) && ($last_autorisation_permission->_fin > $dtnow)}}
  <button class="door-out notext" {{if $float}}style="float: {{$float}};"{{/if}}
          onclick="Soins.askDepartEtablissement(
            '{{$affectation->_id}}',
            '{{$from_placement}}'
            );">
    {{tr}}CAffectation-Start in other external service{{/tr}}
  </button>
{{elseif $affectation->_affectation_perm_id && $curr_affectation->_in_permission}}
  <button class="door-in notext" {{if $float}}style="float: {{$float}};"{{/if}}
          onclick="Soins.askRetourEtablissement(
            '{{$affectation->_id}}',
            '{{$affectation->_affectation_perm_id}}',
            {{if $affectation->_in_permission_sup_48h}}1{{else}}0{{/if}},
            '{{$from_placement}}'
            );">
    {{tr}}CAffectation-Back in etablissement{{/tr}}
  </button>
{{/if}}
