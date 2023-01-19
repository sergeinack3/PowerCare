{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=impose_motif value="dPurgences CRPU impose_motif"|gconf}}

{{if !$rpu->_id && !$impose_motif}}
  {{mb_return}}
{{/if}}

<fieldset class="me-small">
  <legend>Prise en charge médicale</legend>

  {{if $can->edit && $impose_motif}}
  <form name="editMotif" method="get">
    <table class="form me-small-form">
      <tr>
        <th style="width: 10em;">{{mb_label object=$rpu field="motif"}}</th>
        <td>
          {{mb_field object=$rpu field="motif" class="autocomplete" form="editMotif"
          aidesaisie="validateOnBlur: 0, resetSearchField: 0,resetDependFields: 0" onchange="\$V(getForm('editRPU').motif, this.value)"}}
      </tr>
    </table>
  </form>
  {{/if}}


  <table class="form me-no-align me-no-box-shadow me-small-form">
    {{if $view_mode == "infirmier" && $rpu->_id}}
      <tr>
        <th style="width: 10em;">{{tr}}CSejour-praticien_id{{/tr}}</th>
        <td class="{{if !in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id) && !$sejour->UHCD}}arretee{{/if}}">
          {{mb_include module="urgences" template="inc_pec_praticien" tab_mode=0}}
        </td>
      </tr>
    {{/if}}
    {{if $view_mode != "infirmier" && $rpu->_id}}
    <!-- Diagnostic Principal -->
    <tr id="dp_{{$sejour->_id}}">
      {{mb_include module=urgences template=inc_diagnostic_principal diagCanNull=true size_th="10em;" form=editDP_RPU with_form=1}}
    </tr>
    {{/if}}
  </table>
</fieldset>
