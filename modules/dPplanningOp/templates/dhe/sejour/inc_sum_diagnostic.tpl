{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span id="sejour-linked_diagnostics" style="display: inline-block;">
  {{if $sejour->_ext_diagnostic_principal}}
    <span class="dhe_diagnostic dhe_diag_main" id="sejour-main_diagnostic" title="Principal: {{$sejour->_ext_diagnostic_principal->libelle}}">
      {{$sejour->_ext_diagnostic_principal->code}}
    </span>
  {{/if}}

  {{if $sejour->_ext_diagnostic_relie}}
    <span class="dhe_diagnostic dhe_diag_second" id="sejour-second_diagnostic" title="Secondaire: {{$sejour->_ext_diagnostic_relie->libelle}}">
      {{$sejour->_ext_diagnostic_relie->code}}
    </span>
  {{/if}}
</span>

<span id="sejour-related_diagnostics" style="display: inline-block;">
  {{foreach from=$sejour->_diagnostics_associes item=_diagnostic}}
    <span class="dhe_diagnostic dhe_diag_related" id="sejour-related_diagnostic_{{$_diagnostic->code}}" title="Associé: {{$_diagnostic->libelle}}">
      {{$_diagnostic->code}}
    </span>
  {{/foreach}}
</span>