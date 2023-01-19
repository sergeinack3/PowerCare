{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(window.print);
</script>

<table class="tbl" id="list-sorties">
  <tr>
    <th colspan="2">{{mb_title class=CRPU field="_patient_id"}}</th>
    {{if $conf.dPurgences.responsable_rpu_view}}
      <th>{{mb_title class=CRPU field="_responsable_id"}}</th>
    {{/if}}
    <th>Prise en charge</th>
    <th>{{mb_title class=CRPU field="rpu_id"}}</th>
    <th>
      {{mb_title class=CSejour field=entree}} /
      {{mb_title class=CSejour field=sortie}}
    </th>
    {{if "dPurgences Display check_can_leave"|gconf !== "0"}}
    <th>{{mb_title class=CRPU field="_can_leave"}}</th>
    {{/if}}
  </tr>
  {{foreach from=$listSejours item=sejour}}
      {{assign var=rpu value=$sejour->_ref_rpu}}
      {{assign var=patient value=$sejour->_ref_patient}}
      <tr {{if !$sejour->sortie_reelle && $sejour->_veille}}class="veille"{{/if}}>
        {{assign var=sejour_id value=$sejour->_id}}
  
    {{assign var=rpu value=$sejour->_ref_rpu}}
    {{assign var=rpu_id value=$rpu->_id}}
    
    {{assign var=patient value=$sejour->_ref_patient}}
    {{assign var=atu value=$sejour->_ref_consult_atu}}

    <td class="text {{if $sejour->annule}} cancelled {{/if}}" colspan="2">
      {{mb_include template=inc_rpu_patient}}
    </td>
    
    {{if $sejour->annule}}
    <td class="cancelled" colspan="10">
      {{if $rpu->mutation_sejour_id}}
      Hospitalisation
        dossier {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$rpu->_ref_sejour_mutation}}
      {{else}}
      {{tr}}Cancelled{{/tr}}
      {{/if}}
    </td>
    
    {{else}}
    {{if $conf.dPurgences.responsable_rpu_view}}
    <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien}}
    </td>
    {{/if}}
    
    <td class="{{if !in_array($sejour->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id) && !$sejour->UHCD}} arretee {{/if}}">
      {{if !$rpu->_ref_consult->_id}}
        <div class="empty">{{tr}}CRPU-ATU-missing{{/tr}}</div>
      {{else}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien}}
      {{/if}}
    </td>
    
    <td class="text">
      <button class="search notext not-printable" style="float: right;" onclick="ObjectTooltip.createEx(this, '{{$rpu->_guid}}');">
        {{tr}}Info{{/tr}}
      </button>

      <!-- Vérification des champs semi obligatoires -->
      {{if "dPurgences Display check_ccmu"|gconf !== "0"}}
        {{if !$rpu->ccmu           }}<div class="warning" style="display: block;">Champ manquant {{mb_label object=$rpu field=ccmu           }}</div>{{/if}}
      {{/if}}

      {{if "dPurgences Display check_gemsa"|gconf !== "0"}}
        {{if !$rpu->gemsa          }}<div class="warning" style="display: block;">Champ manquant {{mb_label object=$rpu field=gemsa          }}</div>{{/if}}
      {{/if}}
      
      {{if "dPurgences Display check_cotation"|gconf !== "0"}}
        {{if !$rpu->_ref_consult->_count_actes && !$rpu->mutation_sejour_id}}<div class="warning" style="display: block;">Codage des actes manquant</div>{{/if}}
        {{if $sejour->sortie_reelle && !$rpu->_ref_consult->valide && !$rpu->mutation_sejour_id}}<div class="warning" style="display: block;">Le codage n'est pas validé</div>{{/if}}
      {{/if}}

      {{if $sejour->destination}}
         <strong>{{tr}}CSejour-destination{{/tr}}:</strong>
         {{mb_value object=$sejour field="destination"}} <br />
      {{/if}}
      {{if $rpu->orientation}}
         <strong>{{tr}}CRPU-orientation{{/tr}}:</strong>
         {{mb_value object=$rpu field="orientation"}}      
       {{/if}}
    </td>
    
    <td class="text sortie {{$sejour->mode_sortie}}">
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

       <br />
      {{if $sejour->sortie_reelle}}
        {{mb_title object=$sejour field=sortie}} :
        {{mb_value object=$sejour field=mode_sortie}}

        {{if $sejour->mode_sortie == "transfert" && $sejour->etablissement_sortie_id}}
          <br />&gt; <strong>{{mb_value object=$sejour field=etablissement_sortie_id}}</strong>
        {{/if}}

        {{if $sejour->mode_sortie == "mutation" && $sejour->service_sortie_id}}
          {{assign var=service_id value=$sejour->service_sortie_id}}
          {{assign var=service value=$services.$service_id}}
          <br />&gt; <strong>{{$service}}</strong>
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

    {{/if}}
      </tr>
  {{foreachelse}}
    <tr><td colspan="{{$conf.dPurgences.responsable_rpu_view|ternary:7:6}}"><em>Aucune sortie à effectuer</em></td></tr>
  {{/foreach}}
</table>
