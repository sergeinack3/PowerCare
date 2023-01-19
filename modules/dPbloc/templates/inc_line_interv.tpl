{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=multi_salle value=0}}
{{mb_default var=count_ops value=0}}

{{assign var=sejour  value=$_op->_ref_sejour}}
{{assign var=patient value=$sejour->_ref_patient}}
{{assign var=consult_anesth value=$_op->_ref_consult_anesth}}

<tr>
  <td class="text" style="vertical-align: top;">
      {{mb_include module=system template=inc_object_history object=$_op}}
      {{if $patient->_ref_dossier_medical->_id && $patient->_ref_dossier_medical->_count_allergies}}
          {{me_img src="warning.png" icon="warning" class="me-warning"  style="float:right" onmouseover="ObjectTooltip.createEx(this, '`$patient->_guid`', 'allergies');"}}
      {{/if}}
      {{if $patient->_ref_dossier_medical->_id && $patient->_ref_dossier_medical->_count_antecedents}}
        <i class="texticon texticon-atcd me-float-right me-margin-right-2"
              onmouseover="ObjectTooltip.createEx(this, '{{$patient->_ref_dossier_medical->_guid}}', 'antecedents');">{{tr}}CAntecedent.court{{/tr}}</i>
      {{/if}}
    {{mb_include module=patients template=inc_patient_overweight float="right"}}
    <strong>
      {{if $_op->rank}}
        <div class="rank" style="float: left;{{if $_op->annulee}}background-color: #800; color: #fff;{{/if}}">
          {{$_op->rank}}
        </div>
        <select name="rang" class="toggle_rank" style="display: none;" onchange="toggleRank('{{$_op->_id}}', this.value);">
          {{foreach from=1|range:$count_ops item=rank}}
          <option value="{{$rank}}" {{if $rank == $_op->rank}}selected{{/if}}>{{$rank}}</option>
          {{/foreach}}
        </select>
        {{$_op->time_operation|date_format:$conf.time}}
      {{elseif $_op->rank_voulu}}
        <div class="rank desired" style="float: left;{{if $_op->annulee}}background: #800; color: #fff;{{/if}}">{{$_op->rank_voulu}}</div>
      {{else}}
        <div class="rank" style="float: left;{{if $_op->annulee}}background: #800; color: #fff;{{/if}}"></div>
      {{/if}}
      <a href="?m=patients&tab=vw_idx_patients&patient_id={{$patient->_id}}">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');"
              class="{{if !$sejour->entree_reelle}}patient-not-arrived{{/if}} {{if $sejour->septique}}septique{{/if}}">
          {{$patient}} ({{$patient->_age}})
        </span>

        {{mb_include module=patients template=inc_icon_bmr_bhre}}
      </a>
    </strong>
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_op->_ref_chir}}
    <br />
      <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');" {{if !$sejour->entree_reelle}}style="color: red;"{{/if}}>
       Adm. le {{mb_value object=$sejour field=entree}} ({{$sejour->type|truncate:1:""|capitalize}})
      </span>
    {{if $_op->horaire_voulu}}
      <br />
      Passage souhaité à {{$_op->horaire_voulu|date_format:$conf.time}}
    {{/if}}
    <div style="text-align: right;">
      <form name="edit-interv-{{$list_type}}-{{$_op->operation_id}}" action="?m={{$m}}" method="post">
        <input type="hidden" name="m" value="planningOp" />
        <input type="hidden" name="dosql" value="do_planning_aed" />
        <input type="hidden" name="del" value="0" />
        {{mb_key object=$_op}}
        {{mb_label object=$_op field="temp_operation"}}
        {{mb_field object=$_op field="temp_operation" hidden=true onchange="submitOrder(this.form, '$list_type');"}}
        <br />
        {{mb_label object=$_op field="duree_preop"}}
        {{mb_field object=$_op field="duree_preop" hidden=true onchange="submitOrder(this.form, '$list_type');"}}
        {{if $_op->rank}}
          <br />
          {{mb_label object=$_op field="pause"}}
          {{mb_field object=$_op field="pause" hidden=true onchange="submitOrder(this.form, '$list_type');"}}
          <br />
          {{mb_label object=$_op field="duree_bio_nettoyage"}}
          {{mb_field object=$_op field="duree_bio_nettoyage" hidden=true onchange="submitOrder(this.form, '$list_type');"}}
          <br />
          {{mb_label object=$_op field="duree_postop"}}
          {{mb_field object=$_op field="duree_postop" readonly=1}}
        {{elseif $listPlages|@count > 1}}
          <br />
          Changement de salle
          <select name="plageop_id" onchange="submitOrder(this.form);">
            {{foreach from=$listPlages item="_plage"}}
              <option value="{{$_plage->_id}}" {{if $plage->_id == $_plage->_id}}selected{{/if}}>
                {{$_plage->_ref_salle->nom}} / {{$_plage->debut|date_format:$conf.time}} à {{$_plage->fin|date_format:$conf.time}}
              </option>
            {{/foreach}}
          </select>
        {{/if}}
      </form>
    </div>
  </td>

  <td class="text" style="vertical-align: top;">
    {{mb_include module=system template=inc_object_notes object=$_op}}

    <a onclick="Operation.editModal({{$_op->_id}}, {{$_op->plageop_id}}, function() { window.url_edit_planning.refreshModal();} );" href="#">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_op->_guid}}');">
        {{if $_op->libelle}}
          <strong>{{$_op->libelle}}</strong>
          {{if $_op->urgence}}
            <img src="images/icons/attente_fourth_part.png" title="{{tr}}COperation-emergency{{/tr}}"/>
          {{/if}}
        {{else}}
        {{foreach from=$_op->_ext_codes_ccam item=curr_code}}
          <strong>{{$curr_code->code}}</strong> : {{$curr_code->libelleLong|truncate:60:"...":false}}<br />
        {{/foreach}}
        {{/if}}
      </span>

      {{mb_include module=planningOp template=inc_icon_panier operation=$_op float=right}}
    </a>

    <em>{{mb_label object=$_op field=cote}}</em> :
    {{mb_value object=$_op field=cote}}
    {{if $consult_anesth->accord_patient_debout_aller}}
      <br />
         <span class="fas fa-check"> {{tr}}CConsultAnesth-accord_patient_debout_aller-court{{/tr}}</span>
    {{/if}}
    {{if $_op->exam_extempo}}
      <br />
      <span class="texticon texticon-extempo" title="{{tr}}COperation-exam_extempo{{/tr}}">Ext</span>
    {{/if}}

    {{if isset($_op->_other_interv_patient|smarty:nodefaults)}}
      {{assign var=other_interv value=$_op->_other_interv_patient}}
      <br />

      <div style="color: red;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$other_interv->_guid}}')">
          Intervention à {{$other_interv->_datetime_best|date_format:$conf.time}}<br />
          {{mb_value object=$other_interv field=libelle}} <br />
        </span>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$other_interv->_ref_chir}} -
        <span onmouseover="ObjectTooltip.createEx(this, '{{$other_interv->_ref_salle->_guid}}');">
          {{$other_interv->_ref_salle}}
        </span>
      </div>
    {{/if}}

    {{if $_op->materiel}}
      <br />
      {{assign var=commande_bloc value=$_op->_ref_commande_mat.bloc}}
      <em>
      {{if !$commande_bloc->_id}}
        {{mb_label object=$_op field=materiel}}
      {{else}}
        <em>Matériel {{tr}}CCommandeMaterielOp.etat.{{$commande_bloc->etat}}{{/tr}} :</em>
      {{/if}}
      </em>
      {{mb_value object=$_op field=materiel}}
    {{/if}}
    {{if $_op->materiel_pharma}}
      <br />
      {{assign var=commande_pharma value=$_op->_ref_commande_mat.pharmacie}}
      {{if !$commande_pharma->_id}}
      {{else}}
        {{tr}}CCommandeMaterielOp.etat.{{$commande_bloc->etat}}{{/tr}}
      {{/if}}

      {{mb_value object=$_op field=materiel_pharma}}
    {{/if}}
    {{if $_op->exam_per_op}}
      <br /><em>{{mb_label object=$_op field=exam_per_op}}</em> :
      {{mb_value object=$_op field=exam_per_op}}
    {{/if}}
    {{mb_include module=bloc template=inc_rques_intub operation=$_op}}
  </td>
  <td class="narrow" style="vertical-align: top;">
    <form name="editFrmAnesth{{$_op->operation_id}}" action="?m={{$m}}" method="post" style="float: right;">
      <input type="hidden" name="m" value="dPplanningOp" />
      <input type="hidden" name="dosql" value="do_planning_aed" />
      <input type="hidden" name="operation_id" value="{{$_op->operation_id}}" />
      <select name="type_anesth" onchange="submitOrder(this.form, '{{$list_type}}');" style="width: 11em; clear: both;">
        <option value="">&mdash; Anesthésie</option>
        {{foreach from=$anesth item=curr_anesth}}
          {{if $curr_anesth->actif || $_op->type_anesth == $curr_anesth->type_anesth_id}}
            <option value="{{$curr_anesth->type_anesth_id}}" {{if $_op->type_anesth == $curr_anesth->type_anesth_id}} selected="selected" {{/if}}>
              {{$curr_anesth->name}}{{if !$curr_anesth->actif && $_op->type_anesth == $curr_anesth->type_anesth_id}}(Obsolète){{/if}}
            </option>
          {{/if}}
        {{/foreach}}
      </select>
      <br />
      <button type="button" style="clear: both; width:11em;"
              class="{{if $consult_anesth->_ref_consultation->_id}}print{{else}}warning{{/if}} me-tertiary me-width-auto"
              onclick="printFicheAnesth('{{$consult_anesth->_id}}', '{{$_op->_id}}');">
        Fiche d'anesthésie
      </button>
      <br />
      <button type="button" onclick="extraInterv('{{$_op->_id}}')"
        {{if ($_op->salle_id && $_op->salle_id != $_op->_ref_plageop->salle_id) || $_op->_count_affectations_personnel}}
          style="font-weight: bold" class="search me-tertiary me-personnel-indicateur"
        {{else}}
          class="search me-tertiary"
        {{/if}}>Extra</button>

      <br />
      <button type="button" class="search me-tertiary" onclick="SalleOp.manageProtocolesOp('{{$_op->_id}}');">
        {{tr}}CProtocoleOperatoire|pl{{/tr}}
      </button>

      {{if "dPbloc CPlageOp systeme_materiel"|gconf == "expert"}}
        <br />
        {{mb_include module=bloc template=inc_button_besoins_ressources type=operation_id object_id=$_op->_id usage=1}}
      {{/if}}
    </form>
  </td>
  <td class="narrow" style="text-align: center;">
    <!-- Intervention à valider -->
    {{if $_op->annulee && !$conf.dPplanningOp.COperation.save_rank_annulee_validee}}
      {{me_img src="cross.png" icon="cancel" class="me-error"}}
    {{elseif $_op->rank == 0}}
      <form name="edit-insert-{{$_op->operation_id}}" action="?m={{$m}}" method="post" class="prepared">
        <input type="hidden" name="m" value="dPplanningOp" />
        <input type="hidden" name="dosql" value="do_planning_aed" />
        <input type="hidden" name="_move" value="last" /><!-- Insertion à la fin -->
        <input type="hidden" name="operation_id" value="{{$_op->operation_id}}" />
        {{if $multi_salle}}
          <input type="hidden" name="plageop_id" />
          <table class="layout">
            {{foreach from=$plages item=_plage}}
              <tr>
                <td class="narrow">
                  <button type="button" class="tick notext oneclick" title="{{tr}}Add{{/tr}}" onclick="$V(this.form.plageop_id, '{{$_plage->_id}}'); submitOrder(this.form);">
                    {{tr}}Add{{/tr}}
                  </button>
                </td>
                <td style="text-align: left;">
                  {{if $_op->plageop_id == $_plage->_id}}
                  <span class="texticon">
                  {{/if}}
                    {{$_plage->_ref_salle}}
                  {{if $_op->plageop_id == $_plage->_id}}
                  </span>
                  {{/if}}
                  <div class="compact">
                    {{mb_value object=$_plage field=debut}} - {{mb_value object=$_plage field=fin}}
                  </div>
                </td>
              </tr>
            {{/foreach}}
          </table>
        {{else}}
          <button type="button" class="tick notext oneclick" title="{{tr}}Add{{/tr}}" onclick="submitOrder(this.form);">
            {{tr}}Add{{/tr}}
          </button>
        {{/if}}
      </form>
    {{else}}

      <!-- Intervention validée -->
      {{if $_op->rank != 1 || ($_op->_ref_prev_op && $_op->_ref_prev_op->_id)}}
        <form name="edit-up-{{$_op->_id}}" method="post" class="prepared">
          <input type="hidden" name="m" value="planningOp" />
          <input type="hidden" name="dosql" value="do_planning_aed" />
          <input type="hidden" name="operation_id" value="{{$_op->_id}}" />
          <input type="hidden" name="_move" value="before" />
          <button type="button" class="up notext oneclick me-tertiary me-dark" onclick="submitOrder(this.form, '{{$list_type}}');">
            {{tr}}Up{{/tr}}
          </button>
        </form>
        <br />
      {{/if}}

      <form name="edit-del-{{$_op->_id}}" method="post" class="prepared">
        <input type="hidden" name="m" value="planningOp" />
        <input type="hidden" name="dosql" value="do_planning_aed" />
        <input type="hidden" name="operation_id" value="{{$_op->_id}}" />
        <input type="hidden" name="_move" value="out" />
        <button type="button" class="cancel notext oneclick me-tertiary me-dark" onclick="submitOrder(this.form);">
          {{tr}}Delete{{/tr}}
        </button>
      </form>
      <br />

      {{if $seconde_plage->_id}}
        <form name="change-salle-{{$_op->_id}}" method="post">
          <input type="hidden" name="m" value="bloc" />
          <input type="hidden" name="dosql" value="do_move_operation" />
          <input type="hidden" name="operation_id" value="{{$_op->_id}}" />
          <input type="hidden" name="plageop_id" value="{{$seconde_plage->_id}}" />
          <button type="button" class="hslip notext oneclick me-tertiary me-dark" onclick="submitOrder(this.form, '{{$list_type}}');">
            {{tr}}move-salle{{/tr}}
          </button>
        </form>
        <br />
      {{/if}}

      {{if ($_op->rank != $intervs|@count) || ($_op->_ref_next_op && $_op->_ref_next_op->_id)}}
        <form name="edit-down-{{$_op->_id}}" method="post" class="prepared">
          <input type="hidden" name="m" value="planningOp" />
          <input type="hidden" name="dosql" value="do_planning_aed" />
          <input type="hidden" name="operation_id" value="{{$_op->_id}}" />
          <input type="hidden" name="_move" value="after" />
          <button type="button" class="down notext oneclick me-tertiary me-dark" onclick="submitOrder(this.form, '{{$list_type}}');">
            {{tr}}Down{{/tr}}
          </button>
        </form>
        <br />
      {{/if}}
    {{/if}}
  </td>
</tr>
