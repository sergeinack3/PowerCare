{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=sejour_id value=$sejour->_id}}

{{assign var=rpu value=$sejour->_ref_rpu}}
{{assign var=rpu_id value=$rpu->_id}}

{{assign var=patient value=$sejour->_ref_patient}}
{{assign var=atu value=$sejour->_ref_consult_atu}}

{{mb_script module=admissions script=admissions ajax=true}}

{{* La consultation de l'urgentiste peut être sur le séjour reliquat *}}
{{if $rpu->mutation_sejour_id && $rpu->mutation_sejour_id != $rpu->sejour_id}}
  {{assign var=atu value=$rpu->_ref_sejour_mutation->_ref_consult_atu}}
{{/if}}

{{assign var=rpu_link value="Urgences.pecInf('$sejour_id', '$rpu_id')"}}

<td class="text {{if $sejour->annule}} cancelled {{/if}}" colspan="2">
  <form name="validCotation-{{$atu->_id}}" method="post" class="prepared">
    <input type="hidden" name="m" value="cabinet" />
    <input type="hidden" name="dosql" value="do_consultation_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="consultation_id" value="{{$atu->_id}}" />
    <input type="hidden" name="valide" value="1" />
  </form>

  {{mb_include template=inc_rpu_patient}}
</td>

{{if $sejour->annule}}
  <td class="cancelled" colspan="10">
    {{if $rpu->mutation_sejour_id}}
      {{tr}}CSejour-msg-hospi{{/tr}}
      <a href="?m=planningOp&tab=vw_edit_sejour&sejour_id={{$rpu->mutation_sejour_id}}">
        dossier {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$rpu->_ref_sejour_mutation}}
      </a>
    {{else}}
      {{tr}}Cancelled{{/tr}}
    {{/if}}
  </td>
  {{mb_return}}
{{/if}}

{{if $conf.dPurgences.responsable_rpu_view}}
<td>
  <a href="#1" onclick="{{$rpu_link}}">
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien}}
  </a>
</td>
{{/if}}

<td class="button {{if !in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id) && !$sejour->UHCD}}arretee{{/if}}">
  {{mb_include module=urgences template=inc_pec_praticien}}
</td>

<td>
  {{if $rpu->_class_sfmu}}
    <i class="{{$rpu->_class_sfmu}}" style="font-size: 16pt; float: right;" title="{{mb_value object=$rpu->_ref_motif_sfmu field=libelle}}"></i>
  {{/if}}
</td>

<td class="text">
  <button class="search notext me-tertiary" style="float: right;" onclick="ObjectTooltip.createEx(this, '{{$rpu->_guid}}');">
    {{tr}}Info{{/tr}}
  </button>
  {{if $is_praticien || $access_pmsi}}
    <button class="edit notext me-tertiary" style="float: right" onclick="editFieldsRpu('{{$rpu_id}}');"></button>
  {{/if}}

  <!-- Vérification des champs semi obligatoires -->
  {{if "dPurgences Display check_ccmu"|gconf !== "0"}}
    {{if !$rpu->ccmu }}<div class="warning">Champ manquant {{mb_label object=$rpu field=ccmu }}</div>{{/if}}
  {{/if}}

  {{if "dPurgences Display check_dp"|gconf !== "0"}}
    {{if !$sejour->DP}}<div class="warning">Champ manquant {{mb_label object=$sejour field=DP}}</div>{{/if}}
  {{/if}}

  {{if "dPurgences Display check_gemsa"|gconf !== "0"}}
    {{if !$rpu->gemsa}}<div class="warning">Champ manquant {{mb_label object=$rpu field=gemsa}}</div>{{/if}}
  {{/if}}

  {{if "dPurgences Display check_cotation"|gconf !== "0"}}
    {{if !$rpu->_ref_consult->_count_actes && !$rpu->mutation_sejour_id}}
      <div class="warning">Codage des actes manquant</div>
    {{/if}}
    {{if $sejour->sortie_reelle && !$rpu->_ref_consult->valide && !$rpu->mutation_sejour_id}}<div class="warning">Le codage n'est pas validé</div>{{/if}}
  {{/if}}

  {{if "dPurgences CRPU display_motif_sfmu"|gconf && "dPurgences Display check_gemsa"|gconf !== "0"}}
    {{if !$rpu->motif_sfmu   }}<div class="warning">Champ manquant {{mb_label object=$rpu field=motif_sfmu     }}</div>{{/if}}
  {{/if}}

  {{if $sejour->sortie_reelle}}
     {{if $sejour->destination}}
       <strong>{{mb_label object=$sejour field=destination}}</strong> :
       {{mb_value object=$sejour field=destination}} <br />
     {{/if}}
     {{if $rpu->orientation}}
       <strong>{{mb_label object=$rpu field=orientation}}</strong> :
       {{mb_value object=$rpu field=orientation}}
     {{/if}}
  {{/if}}
</td>

<td class="text sortie {{$sejour->mode_sortie}}">
  <span style="float: right;">
    {{if $sejour->UHCD}}
      <span class="encart encart-uhcd">{{tr}}CSejour-UHCD{{/tr}}</span>
    {{/if}}

    {{if $rpu->mutation_sejour_id}}
      <span class="texticon texticon-mutation">Muta</span>
    {{/if}}

    {{if $sejour->_ref_curr_affectation &&
         $sejour->_ref_curr_affectation->_ref_service &&
         $sejour->_ref_curr_affectation->_ref_service->radiologie}}
      <span class="encart encart-imagerie">IMG</span>
    {{/if}}
  </span>
  <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
    {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
    {{if !$sejour->sortie_reelle}}
      {{mb_title object=$sejour field=entree}}
    {{/if}}
    <strong>
      {{mb_value object=$sejour field=entree date=$date}}
      {{if $sejour->sortie_reelle}}
      &gt; {{mb_value object=$sejour field=sortie date=$date}}
      {{/if}}
    </strong>
  </span>

  <br style="clear: both;"/>
  {{if $rpu->mutation_sejour_id && $sejour->mode_sortie != "mutation"}}
    <div class="warning">
      Un séjour de mutation a été détecté, mais le mode de sortie <strong>mutation</strong> n'a pas été renseigné.
    </div>
  {{/if}}

  {{if $can->admin && $rpu->mutation_sejour_id && $sejour->_id != $rpu->mutation_sejour_id}}
    <form method="post" name="annulerHospitalisation">
      <input type="hidden" name="m" value="urgences"/>
      <input type="hidden" name="dosql" value="do_cancel_hospitalization_aed"/>
      <input type="hidden" name="sejour_guid" value="{{$sejour->_guid}}"/>
      <button type="submit" class="unlink notext" title="{{tr}}cancel-hospitalization{{/tr}}" style="float: right;">
        {{tr}}cancel-hospitalization{{/tr}}
      </button>
    </form>
  {{/if}}

  {{if $sejour->sortie_reelle || $sejour->mode_sortie == "mutation"}}
    {{if "ecap"|module_active}}
      <button class="ecap notext singleclick" style="float: right;" onclick="DHE.closeDHE('{{$sejour->_id}}')">
        {{tr}}Close{{/tr}}
      </button>
    {{/if}}

    <button class="edit notext" style="float: right;" onclick="refreshExecuter.stop();
      Admissions.validerSortie('{{$sejour->_id}}', false, function () {refreshExecuter.resume(); Sortie.refresh('{{$rpu->_id}}'); });">
      {{tr}}Edit{{/tr}} {{mb_label object=$sejour field=sortie}}
    </button>

    {{mb_title object=$sejour field=sortie}} :
    {{mb_value object=$sejour field=mode_sortie}}

    {{if $sejour->mode_sortie == "transfert" && $sejour->etablissement_sortie_id}}
      <br />&gt; <strong>{{mb_value object=$sejour field=etablissement_sortie_id}}</strong>
    {{/if}}

    {{if $sejour->mode_sortie == "mutation" && $sejour->service_sortie_id}}
      {{assign var=service_id value=$sejour->service_sortie_id}}
      {{assign var=service value=$services.$service_id}}
      <br />Vers: <strong>{{$service}}</strong>
      {{foreach from=$rpu->_ref_sejour_mutation->_ref_affectations item=_affectation}}
        &rarr; <strong>{{$_affectation}}</strong>
      {{/foreach}}
    {{/if}}

    <div class="compact">{{mb_value object=$sejour field=transport_sortie}}</div>
    <div class="compact">{{mb_value object=$sejour field=rques_transport_sortie}}</div>
    <div class="compact">{{mb_value object=$sejour field=commentaires_sortie}}</div>
    {{if $sejour->destination}}
      <div class="compact">
        {{mb_value object=$sejour field=destination}}
        {{if $sejour->mode_destination_id}}: {{mb_value object=$sejour field=mode_destination_id}}{{/if}}
      </div>
    {{/if}}
    {{if $rpu->orientation}}
      <div class="compact">{{mb_label object=$rpu field=orientation}}: {{mb_value object=$rpu field=orientation}}</div>
    {{/if}}

  {{else}}
    {{if $sejour->mode_sortie && $sejour->mode_sortie != "normal"}}
      <div class="warning">
        Le mode de sortie est
        <strong>
          {{mb_value object=$sejour field=mode_sortie}}
        </strong>
        mais la sortie réelle n'est pas validée
      </div>
    {{/if}}
    <button class="tick" onclick="refreshExecuter.stop(); Admissions.validerSortie('{{$sejour->_id}}', false, function() { refreshExecuter.resume(); Sortie.refresh('{{$rpu->_id}}'); });">
      {{tr}}Validate{{/tr}} {{mb_label object=$sejour field=sortie}}
    </button>
  {{/if}}
  </td>

{{if "dPurgences Display check_can_leave"|gconf !== "0"}}
  {{if $sejour->mode_sortie == "mutation"}}
    <td></td>
  {{else}}
    <td id="rpu-{{$rpu->_id}}" style="font-weight: bold" class="text {{if !$rpu->sortie_autorisee}}arretee{{/if}} {{$rpu->_can_leave_level}}">
      {{if $sejour->sortie_reelle}}
        {{if !$rpu->sortie_autorisee}}
          {{tr}}CRPU-sortie_assuree.{{$rpu->sortie_autorisee}}{{/tr}}
        {{/if}}
      {{elseif $rpu->_can_leave == -1}}
        {{if !in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id)}}
          {{mb_value object=$sejour field=type}}<br />
        {{elseif !$atu->_id}}
          Pas encore de prise en charge<br />
        {{else}}
          {{tr}}CConsultation{{/tr}} {{tr}}CConsultation.chrono.48{{/tr}} <br />
        {{/if}}
        {{tr}}CRPU-sortie_assuree.{{$rpu->sortie_autorisee}}{{/tr}} {{if $rpu->sortie_autorisee}}: {{mb_value object=$rpu field=date_sortie_aut}}{{/if}}
      {{elseif $rpu->_can_leave != -1 && !$rpu->sortie_autorisee}}
        {{tr}}CConsultation{{/tr}} {{tr}}CConsultation.chrono.64{{/tr}} <br />
        {{tr}}CRPU-sortie_assuree.0{{/tr}}
      {{else}}
        {{if $rpu->_can_leave_since}}
          {{tr}}CRPU-_can_leave_since{{/tr}}
        {{elseif $rpu->_can_leave_about}}
          {{tr}}common-Since{{/tr}}
        {{/if}}
        <span title="{{mb_value object=$sejour field=sortie_prevue}}">{{mb_value object=$rpu field="_can_leave"}}</span><br />
        {{tr}}CRPU-sortie_assuree.{{$rpu->sortie_autorisee}}{{/tr}}: {{mb_value object=$rpu field=date_sortie_aut}}
      {{/if}}
    </td>
  {{/if}}
{{/if}}
