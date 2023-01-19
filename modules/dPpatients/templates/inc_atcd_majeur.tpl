{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=activate value="dPpatients CAntecedent show_atcd_by_tooltip"|gconf}}

{{foreach from=$dossier_medical->_all_antecedents item=_antecedent}}
  {{if $_antecedent->majeur || $_antecedent->important}}
    {{if $_antecedent->majeur}}
      {{assign var=color value="#f00"}}
      {{assign var=flagName value='Ox\Core\CAppUI::tr'|static_call:"CAntecedent-majeur-court"}}
    {{elseif $_antecedent->important}}
      {{assign var=color value="#fd7d26"}}
      {{assign var=flagName value='Ox\Core\CAppUI::tr'|static_call:"CAntecedent-important-court"}}
    {{/if}}
    <span class="circled {{if $_antecedent->majeur}}me-majeur{{/if}}" {{if $activate}}onmouseover="ObjectTooltip.createEx(this, '{{$_antecedent->_guid}}')"{{/if}}
          style="border-color: {{$color}}; color: {{$color}}; font-size: 0.6em; background-color: #fff; font-weight: normal; text-shadow: none;">
      {{if $activate}}
        {{$flagName}}
      {{else}}
        {{$_antecedent->rques|smarty:nodefaults|spancate:20:"...":false}}
      {{/if}}
    </span>
  {{/if}}
{{/foreach}}