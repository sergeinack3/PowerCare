{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_age_sexe_mvt value=0}}
{{mb_default var=show_hour_anesth_mvt value=0}}
{{mb_default var=affectation value=0}}
{{mb_default var=desectorise value=0}}

{{assign var=patient value=$sejour->_ref_patient}}

<tr {{if $sejour->recuse == -1}}class="opacity-70"{{/if}}>
  {{if $show_duree_preop && $type_mouvement != "sorties"}}
    <td>
      {{mb_value object=$sejour->_ref_curr_operation field=_heure_us}}
    </td>
  {{/if}}
  <td class="text {{if $sejour->confirme}}arretee{{/if}}">
    {{if $canPlanningOp->read}}
      <div style="float: right">
        {{if $isImedsInstalled}}
          {{mb_include module=Imeds template=inc_sejour_labo link="#1" float="none"}}
        {{/if}}
        
        <a class="action" style="display: inline" title="Modifier le séjour"
           href="?m=planningOp&tab=vw_edit_sejour&sejour_id={{$sejour->_id}}">
          <img src="images/icons/planning.png" alt="modifier" />
        </a>
      </div>
    {{/if}}
    
    <strong onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')"
      {{if !$sejour->entree_reelle}} class="patient-not-arrived"{{/if}}>
      {{$patient}}
    </strong>
    <br />
    {{mb_include module=hospi template=inc_vw_liaisons_prestation liaisons=$sejour->_liaisons_for_prestation}}
  </td>

  {{if $show_age_sexe_mvt}}
    <td>
      {{$patient->sexe|strtoupper}}
    </td>
    <td>
      {{mb_value object=$patient field=_age}}
    </td>
  {{/if}}

  <td class="text">
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien}} <br />

    {{if !$show_hour_anesth_mvt}}
      {{foreach from=$sejour->_ref_operations item=_op}}
        {{if $_op->_ref_anesth}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_op->_ref_anesth}}
        {{/if}}
      {{/foreach}}
    {{/if}}

  </td>
  <td class="text">
    <strong onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">{{$sejour->_motif_complet}}</strong>

    {{assign var=next_op value=$sejour->_ref_next_operation}}
    {{if "dPhospi vue_temporelle infos_interv"|gconf && ($type == "presents" || $type_mouvement ==  "entrees") && $next_op && $next_op->_id}}
      <div class="compact" style="padding-top: 5px;">
        {{$next_op->_datetime_best|date_format:$conf.date}} {{$next_op->_datetime_best|date_format:$conf.time}} -
        {{mb_value object=$next_op field=temp_operation}} -
        {{mb_value object=$next_op field=type_anesth}}
      </div>
    {{/if}}
  </td>

  {{if $show_hour_anesth_mvt}}
    {{assign var=op value=$sejour->_ref_curr_operation}}
    <td class="narrow">
      {{if $op->_id}}
        {{$op->_datetime_best|date_format:$conf.time}}
      {{/if}}
    </td>
    <td class="text">
      {{foreach from=$sejour->_ref_operations item=_op}}
        {{if $_op->_ref_anesth->_id}}
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_op->_ref_anesth}}
        {{/if}}
      {{/foreach}}
    </td>
  {{/if}}

  {{if "dmi"|module_active}}
    <td class="button">
      {{foreach from=$sejour->_ref_operations item=_interv}}
        {{mb_include module=dmi template=inc_dmi_alert interv=$_interv}}
      {{/foreach}}
    </td>
  {{/if}}
  <td class="text {{if $sejour->sortie_reelle}}effectue{{/if}}">
    {{if $affectation}}
      {{$affectation->_ref_lit}}

      {{if $desectorise}}
        <br />
        {{$affectation->_ref_lit->_ref_chambre->_ref_service}}
      {{/if}}
    {{else}}
      -
    {{/if}}
  </td>

  {{if $desectorise}}
    <td>
      {{$sejour->_ref_service}}
    </td>
  {{/if}}

  <td class="narrow">
    {{$sejour->entree|date_format:$conf.datetime}}
    <div style="position: relative;">
      <div class="sejour-bar"
           title="arrivée il y a {{$sejour->_entree_relative}}j et départ prévu dans {{$sejour->_sortie_relative}}j ">
        <div
          style="width: {{if $sejour->_duree}}{{math equation='100*(-entree / (duree))' entree=$sejour->_entree_relative duree=$sejour->_duree format='%.2f'}}{{else}}100{{/if}}%;"></div>
      </div>
    </div>
  </td>
  {{if "dPhospi mouvements print_comm_patient_present"|gconf && $type == "presents" && !$type_mouvement}}
    <td class="only-printable" style="width: 200px;"></td>
  {{/if}}
  <td class="narrow {{if $sejour->confirme}}ok{{else}}warning{{/if}}">
    <div class="only-printable">
      {{if $affectation}}
        {{$affectation->sortie|date_format:$conf.datetime}}
        <br />
        / {{mb_value object=$sejour field="mode_sortie"}}
      {{else}}
        {{$sejour->sortie|date_format:$conf.datetime}}
        <br />
        / {{mb_value object=$sejour field="mode_sortie"}}
      {{/if}}
      {{if $sejour->mode_sortie == "transfert" && $sejour->etablissement_sortie_id}}
        <br />
        {{mb_value object=$sejour field="etablissement_sortie_id"}}
      {{/if}}
    </div>
    <div class="not-printable" style="text-align: center">
      {{if $affectation}}
        {{assign var=aff_guid value=$affectation->_guid}}
      {{else}}
        {{assign var=aff_guid value=$sejour->_guid}}
      {{/if}}
      <form name="editSortiePrevue-{{$type}}-{{$aff_guid}}" method="post" action="?"
            onsubmit="return onSubmitFormAjax(this, refreshList.curry(null, null, '{{$type}}', '{{$type_mouvement}}'))">
        <input type="hidden" name="m" value="planningOp" />
        <input type="hidden" name="dosql" value="do_sejour_aed" />
        <input type="hidden" name="del" value="0" />
        {{mb_key object=$sejour}}
        <span {{if $sejour->mode_sortie}}onmouseover="ObjectTooltip.createDOM(this, 'confirme_sortie_{{$sejour->_id}}');"{{/if}}>
          {{mb_field object=$sejour field=entree_prevue hidden=true}}
          {{if $sejour->confirme}}
            {{mb_value object=$sejour field=sortie}}
            / {{mb_value object=$sejour field="mode_sortie"}}
          {{else}}
            {{mb_value object=$sejour field=sortie_prevue}}
            <br />

/ {{mb_value object=$sejour field="mode_sortie"}}
            <br />
            <button class="add" type="button" onclick="addDays(this, 1)">1J</button>
            {{mb_field object=$sejour field=sortie_prevue hidden=true form="editSortiePrevue-`$type`-`$aff_guid`" onchange="this.form.onsubmit()"}}
          {{/if}}
        </span>
        {{if $sejour->sortie_reelle}}
          /
          <strong>Effectuée</strong>
        {{else}}
          <button class="edit" type="button"
                  onclick='Admissions.validerSortie("{{$sejour->_id}}", {{if $sejour->type == "ambu" && "dPplanningOp CSejour sortie_reelle_ambu"|gconf}}false{{else}}true{{/if}}, refreshList.curry("{{$order_col}}", "{{$order_way}}", "{{$type}}", "{{$type_mouvement}}"));'>
            Modifier
          </button>
        {{/if}}
        {{if $sejour->mode_sortie == "transfert" && $sejour->etablissement_sortie_id}}
          <br />
          {{mb_value object=$sejour field="etablissement_sortie_id"}}
        {{/if}}
        <div id="confirme_sortie_{{$sejour->_id}}" style="display: none">
          {{mb_include module=planningOp template=inc_vw_sortie_sejour}}
        </div>
      </form>
    </div>

    {{if "dPhospi mouvements print_comm_patient_present"|gconf && $type == "presents" && !$type_mouvement && $sejour->confirme}}
      <span class="only-printable">(Sortie autorisée: {{mb_value object=$sejour field=confirme}})</span>
    {{/if}}
  </td>
  {{if $type == "ambu"}}
    {{if $show_retour_mvt}}
      <td></td>
    {{/if}}
    {{if $show_collation_mvt}}
      <td></td>
    {{/if}}
    {{if $show_sortie_mvt}}
      <td></td>
    {{/if}}
  {{/if}}
</tr>