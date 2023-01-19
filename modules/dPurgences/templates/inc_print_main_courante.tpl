{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=rpu value=$sejour->_ref_rpu}}
{{assign var=patient value=$sejour->_ref_patient}}
{{assign var=consult value=$rpu->_ref_consult}}

{{mb_default var=offline_lite value=0}}

<tr>
  <td style="text-align: right;">
    {{mb_value object=$sejour field=entree}}
    {{if $sejour->_veille}}
      <br/> Admis la veille
    {{/if}}
  </td>
  <td class="text">
    {{if $offline && $rpu->_id}}
      <button class="search notext not-printable" onclick="$('modal-{{$sejour->_id}}').up('tr').show(); modalwindow = Modal.open($('modal-{{$sejour->_id}}'));">
        {{tr}}Show{{/tr}}
      </button>
    {{elseif $offline_lite}}
      <button type="button" class="search compact notext not-printable" onclick="Modal.open('content_{{$patient->_id}}', {showClose: true});">Voir le dossier</button>
    {{/if}}
    {{assign var=rpu_link value="#`$patient->_guid`"}}
    {{mb_include module=urgences template=inc_rpu_patient}}
  </td>
  {{if $rpu->_id}}
    <td class="text" style="{{if $rpu->ccmu}}background-color: #{{"dPurgences Display color_ccmu_`$rpu->ccmu`"|gconf}};{{/if}}">
      {{if $rpu->ccmu}}
        {{mb_value object=$rpu field="ccmu"}}
      {{/if}}
    </td>
    <td class="text">
      {{if $rpu->date_at}}
        <span class="texticon texticon-at">AT</span>
      {{/if}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$rpu->_guid}}');">
        {{$rpu->diag_infirmier|nl2br}}
      </span>
    </td>
    <td>{{mb_value object=$consult field="heure"}}</td>
    <td>
      {{assign var=remplacant value=$sejour->_ref_praticien->_ref_remplacant}}
      {{if $remplacant}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$remplacant}}
        <br/> <span class="compact">(remplaçant de
      {{/if}}
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien}}
      {{if $remplacant}})</span>{{/if}}
      {{if $rpu->_ref_ide_responsable && $rpu->_ref_ide_responsable->_id}}
        <br/>
        <strong>{{mb_label class="CRPU" field="ide_responsable_id"}}</strong> :
        <span onmouseover="ObjectTooltip.createEx(this, '{{$rpu->_ref_ide_responsable->_guid}}')">
          {{$rpu->_ref_ide_responsable->_view}}
        </span>
      {{/if}}
    </td>
    <td>
      {{if $sejour->sortie_reelle}}
        {{mb_value object=$sejour field="mode_sortie"}}
      {{/if}}
      {{if $sejour->mode_sortie == "transfert"}}
        <br />
        &gt; <strong>{{mb_value object=$sejour field=etablissement_sortie_id}}</strong>
      {{/if}}
      {{if $sejour->mode_sortie == "mutation"}}
        <br />
        &gt; <strong>{{mb_value object=$sejour field=service_sortie_id}}</strong>
      {{/if}}
      {{if $rpu->orientation}}
        <br />
        {{mb_value object=$rpu field="orientation"}}
      {{/if}}
      <em>{{mb_value object=$sejour field=commentaires_sortie}}</em>
    </td>

    {{if $print_gemsa}}
      <td>{{mb_value object=$rpu field=gemsa}}</td>
    {{/if}}

    {{if !in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id) && !$sejour->UHCD}}
      <td colspan="2" class="text arretee">
        <strong>{{mb_value object=$sejour field=type}}</strong>
      </td>

    {{elseif $sejour->annule}}
      <td class="cancelled" colspan="2">
        {{tr}}Cancelled{{/tr}}
      </td>

    {{elseif $rpu->mutation_sejour_id}}
      {{mb_include module=urgences template=inc_dossier_mutation colspan=2}}

    {{else}}
      {{if !$sejour->sortie_reelle}}
        <td></td>
      {{else}}
        <td style="text-align: right;">{{mb_value object=$sejour field=sortie}}</td>
      {{/if}}
    {{/if}}
  {{else}}
    <!-- Pas de RPU pour ce séjour d'urgence -->
    <td colspan="10">
      <div class="small-warning">
        Ce séjour d'urgence n'est pas associé à un RPU.
      </div>
    </td>
  {{/if}}
</tr>
