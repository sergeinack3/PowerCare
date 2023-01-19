{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_hslip_button value=1}}
{{mb_default var=readonly value=false}}

<table class="tbl me-no-box-shadow me-margin-top-5 me-no-border-radius-bottom me-header-operation">
  <tr>
    <th class="title text me-no-border" colspan="2">
      {{if $show_hslip_button}}
        <button class="hslip notext me-tertiary me-dark not-printable" id="listplages-trigger" type="button" style="float:left">
          {{tr}}Show_or_hide_left_column{{/tr}}
        </button>
      {{/if}}
      <a style="float: left" href="?m=patients&tab=vw_full_patients&patient_id={{$patient->_id}}">
        {{mb_include module=patients template=inc_vw_photo_identite patient=$patient size=42 sejour_conf=$sejour->presence_confidentielle}}
      </a>
      {{if $sejour->_ref_grossesse && $sejour->_ref_grossesse->_id && "maternite"|module_active}}
        <a href="#" style="float:left;" onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_ref_grossesse->_guid}}');">
          <img src="modules/maternite/images/icon.png" alt="" style="width:42px; height:42px; margin-left:5px; background-color: white" />
        </a>
      {{/if}}
      <a class="action not-printable" style="float: right;" title="Modifier le dossier administratif" href="?m=dPpatients&tab=vw_edit_patients&patient_id={{$patient->_id}}">
        {{me_img src="edit.png" icon="edit" class="me-primary"}}
      </a>

      <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">{{$patient}}</span>

      {{mb_include module=patients template=inc_icon_bmr_bhre}}
        {{if $patient->_homonyme}}
            {{mb_include module=patients template=patient_state/inc_flag_homonyme}}
        {{/if}}

      ({{$patient->_age}}
      {{if $patient->_annees != "??"}}- {{mb_value object=$patient field="naissance"}}{{/if}})
      &mdash; Dr {{$selOp->_ref_chir->_view}}
      {{if $sejour->_ref_curr_affectation && $sejour->_ref_curr_affectation->_ref_lit && $sejour->_ref_curr_affectation->_ref_lit->_ref_chambre}}- {{$sejour->_ref_curr_affectation->_ref_lit->_ref_chambre->_view}}{{/if}}
      <br />

      {{if $sejour->_ref_grossesse && $sejour->_ref_grossesse->_id && "maternite"|module_active}}
        {{mb_value object=$sejour->_ref_grossesse field=_semaine_grossesse}} <span title="{{tr}}CGrossesse-_semaine_grossesse-desc{{/tr}}">{{tr}}CGrossesse-_semaine_grossesse-court{{/tr}}</span>
        + {{mb_value object=$sejour->_ref_grossesse field=_reste_semaine_grossesse}} j &mdash;
        {{tr var1=$sejour->_ref_grossesse->terme_prevu|date_format:$conf.date}}CGrossesse-Expected term the %s{{/tr}}
        <br />
      {{/if}}

      {{mb_include module=planningOp template=inc_reload_infos_interv operation=$selOp}}

      &mdash; {{mb_label object=$selOp field=temp_operation}} : {{mb_value object=$selOp field=temp_operation}}

      {{if $selOp->exam_extempo}}
        <span class="texticon texticon-extempo" title="{{tr}}COperation-exam_extempo{{/tr}}">Ext</span>
      {{/if}}
      <br />

      <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">{{if $sejour->presence_confidentielle}}{{mb_include module=planningOp template=inc_badge_sejour_conf}}{{/if}}{{tr}}CSejour{{/tr}} du {{mb_value object=$sejour field=entree}}</span>
      au
      {{if !$readonly && ($sejour->canEdit() || $currUser->_is_praticien)}}
        {{assign var=sejour_guid value=$sejour->_guid}}
        {{unique_id var=sortie_prevue}}
        <form name="editSortiePrevue-{{$sejour_guid}}-{{$sortie_prevue}}" method="post" action="?"
              style="font-size: 0.9em;" onsubmit="return onSubmitFormAjax(this)">
          <input type="hidden" name="m" value="dPplanningOp" />
          <input type="hidden" name="dosql" value="do_sejour_aed" />
          <input type="hidden" name="del" value="0" />
          {{mb_key object=$sejour}}
          {{mb_field object=$sejour field=entree_prevue hidden=true}}
          {{mb_field object=$sejour field=entree_reelle hidden=true}}

          {{mb_ternary var=sortie_field test=$sejour->sortie_reelle value="sortie_reelle" other="sortie_prevue"}}
          {{mb_field object=$sejour field=$sortie_field register=true form="editSortiePrevue-$sejour_guid-$sortie_prevue" onchange="this.form.onsubmit()"}}
        </form>
      {{else}}
        {{mb_value object=$sejour field=sortie_prevue}}
      {{/if}}
      <span style="display:inline-block;max-height: 15px;">
      {{mb_include module=patients template=vw_antecedents_allergies sejour_id=$sejour->_id}}
      </span>

      <span style="font-size: 1.1em;">
      {{assign var=prescription value=$sejour->_ref_prescription_sejour}}
        {{assign var=dossier_medical value=$patient->_ref_dossier_medical}}

        {{if $prescription && $prescription->_ref_lines_important|@count}}
          {{mb_include module=prescription template=vw_line_important lines=$prescription->_ref_lines_important}}
        {{/if}}

        <span id="atcd_majeur">
        {{mb_include module=patients template=inc_atcd_majeur}}
      </span>
      </span>
    </th>
  </tr>

  {{if $conf.dPplanningOp.COperation.verif_cote && $selOp->cote_bloc && ($selOp->cote == "droit" || $selOp->cote == "gauche")}}
    <!-- Vérification du côté -->
    <tr>
      <td colspan="2">
        <strong>Côté DHE : {{mb_value object=$selOp field="cote"}}</strong> -
        <span class="{{if !$selOp->cote_admission}}warning{{elseif $selOp->cote_admission != $selOp->cote}}error{{else}}ok{{/if}}">
          Admission : {{mb_value object=$selOp field="cote_admission"}}
        </span> -
        <span class="{{if !$selOp->cote_consult_anesth}}warning{{elseif $selOp->cote_consult_anesth != $selOp->cote}}error{{else}}ok{{/if}}">
          Consult Anesth : {{mb_value object=$selOp field="cote_consult_anesth"}}
        </span> -
        <span class="{{if !$selOp->cote_hospi}}warning{{elseif $selOp->cote_hospi != $selOp->cote}}error{{else}}ok{{/if}}">
          Service : {{mb_value object=$selOp field="cote_hospi"}}
        </span> -
        <span class="{{if !$selOp->cote_bloc}}warning{{elseif $selOp->cote_bloc != $selOp->cote}}error{{else}}ok{{/if}}">
          Bloc : {{mb_value object=$selOp field="cote_bloc"}}
        </span>
      </td>
    </tr>
  {{/if}}

  {{assign var=consult_anesth value=$selOp->_ref_consult_anesth}}

  {{if $selOp->_ref_sejour->rques || $selOp->rques || $selOp->materiel || ($consult_anesth->_id && $consult_anesth->_intub_difficile)
       || $selOp->exam_extempo}}
    <!-- Mise en avant du matériel et remarques -->
    <tr>
      {{* Calculate the amount of columns needed then, for each section, define the correct column *}}

      {{assign var="nCols" value=0}}
      {{if $selOp->_ref_sejour->rques || $selOp->rques || ($consult_anesth->_id && $consult_anesth->_intub_difficile)}}
        {{assign var="nCols" value=$nCols+1}}
      {{/if}}
      {{if $selOp->materiel || $selOp->materiel_pharma}}
        {{assign var="nCols" value=$nCols+1}}
      {{/if}}


      {{if $selOp->_ref_sejour->rques || $selOp->rques || ($consult_anesth->_id && $consult_anesth->_intub_difficile)}}
        {{if $nCols == 1}}<td class="text big-warning">{{/if}}
        {{if $nCols == 2}}<td class="text big-warning halfPane">{{/if}}

        {{if $selOp->_ref_sejour->rques}}
          <strong>{{mb_label object=$selOp->_ref_sejour field=rques}}</strong>
          {{mb_value object=$selOp->_ref_sejour field=rques}}
        {{/if}}
        {{if $selOp->rques || ($consult_anesth->_id && $consult_anesth->_intub_difficile)}}
          <strong>{{mb_label object=$selOp field=rques}}</strong>
        {{/if}}
        {{if $selOp->rques}}
          {{mb_value object=$selOp field=rques}}
        {{/if}}
        {{if $consult_anesth->_id && $consult_anesth->_intub_difficile}}
            <span style="display: block; font-weight: bold; color:#f00;">
            {{tr}}CConsultAnesth-_intub_difficile{{/tr}}
            </span>
          {{/if}}
        </td>
      {{/if}}

      {{if $selOp->materiel || $selOp->materiel_pharma}}
        {{if $nCols == 1}}
          <td class="text big-info">
        {{/if}}
        {{if $nCols == 2}}
          <td class="text big-info halfPane">
        {{/if}}
      {{if $selOp->materiel}}
        <strong>{{mb_label object=$selOp field=materiel}}</strong>
        {{assign var=commande value=$selOp->_ref_commande_mat.bloc}}
        {{if $commande && $commande->_id}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$commande->_guid}}')">
              &nbsp;&nbsp;{{tr}}COperation-materiel-court{{/tr}} {{mb_value object=$commande field=etat}}
            </span>
        {{/if}}
        {{mb_value object=$selOp field=materiel}}
      {{/if}}

      {{if $selOp->materiel_pharma}}
        <strong>{{mb_label object=$selOp field=materiel_pharma}}</strong>
        {{assign var=commande_pharma value=$selOp->_ref_commande_mat.pharmacie}}
        {{if $commande_pharma->_id}}
          <span onmouseover="ObjectTooltip.createEx(this, '{{$commande_pharma->_guid}}')">
              &nbsp;&nbsp;{{tr}}COperation-materiel_pharma-court{{/tr}} {{mb_value object=$commande_pharma field=etat}}
            </span>
        {{/if}}
        {{mb_value object=$selOp field=materiel_pharma}}
      {{/if}}

      {{if $selOp->exam_per_op}}
        <strong>{{mb_label object=$selOp field=exam_per_op}}</strong>
        {{mb_value object=$selOp field=exam_per_op}}
      {{/if}}
      </td>
      {{/if}}
    </tr>
  {{/if}}
</table>

<table class="tbl me-no-hover me-no-box-shadow-only-white me-margin-top--1 me-no-border-radius-top me-header-operation-infos">
  {{mb_include module=soins template=inc_infos_patients_soins add_class=1}}
</table>
