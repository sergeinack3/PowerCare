{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=limit_prise_rdv value=$app->user_prefs.limit_prise_rdv}}
{{assign var=consult value=$object->_ref_consultation}}

<script>
  newExam = function(sAction, consultation_id) {
    if (sAction) {
      var url = new Url("cabinet", sAction);
      url.addParam("consultation_id", consultation_id);
      url.popup(900, 600, "Examen");
    }
  };

  printFiche = function(dossier_anesth_id) {
    var url = new Url("cabinet", "print_fiche");
    url.addParam("dossier_anesth_id", dossier_anesth_id);
    url.addParam("print", true);
    url.popup(700, 500, "printFiche");
  };
</script>

{{* CConsultAnesth *}}
<table class="form">
  <tr>
    <th class="title" colspan="4">
      {{mb_include module=system template=inc_object_idsante400 object=$consult}}
      {{mb_include module=system template=inc_object_history    object=$consult}}
      {{mb_include module=system template=inc_object_notes      object=$consult}}

      {{if !$limit_prise_rdv}}
        {{foreach from=$consult->_refs_dossiers_anesth item=_dossier_anesth}}
          <button class="print" type="button" style="float: right;" onclick="printFiche('{{$_dossier_anesth->_id}}');">
            {{tr}}CConsultation-Print the card{{/tr}}
            {{if $_dossier_anesth->_ref_operation->_id}}
              {{tr var1=$_dossier_anesth->_ref_operation->_datetime_best|date_format:$conf.datetime}}COperation-for the intervention of the %s{{/tr}}
            {{/if}}
          </button>
        {{/foreach}}
      {{/if}}

      {{$consult}}
    </th>
  </tr>
  <tr>
    <td colspan="2">
      <strong>{{tr}}CConsultation-_date{{/tr}} :</strong>
      <i>{{tr var1=$object->_ref_plageconsult->date|date_format:"%d %B %Y" var2=$consult->heure|date_format:$conf.time}}common-the %s at %s{{/tr}}</i>
    </td>
    <td colspan="2">
      <strong>{{tr}}CConsultation-_prat_id{{/tr}} :</strong>
      <i>{{if $object->_ref_plageconsult->_ref_chir->isPraticien()}}{{tr}}CMedecin.titre.dr{{/tr}}{{/if}} {{$object->_ref_plageconsult->_ref_chir->_view}}</i>
    </td>
  </tr>
  <tr>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultation-motif{{/tr}} :</strong>
      <i>{{mb_value object=$consult field=motif}}</i>
    </td>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultation-rques{{/tr}} :</strong>
      <i>{{mb_value object=$object field=rques}}</i>
    </td>
  </tr>

  <tr>
    <td class="text" colspan="4">
      <strong>{{tr}}CConsultAnesth-operation_id{{/tr}} :</strong>
      {{if $object->operation_id}}
        {{tr}}the{{/tr}} <i>{{$object->_ref_operation->_ref_plageop->date|date_format:$conf.longdate}}</i>
        {{tr}}COperation-by the{{/tr}} <i>{{tr}}CMedecin.titre.dr{{/tr}} {{$object->_ref_operation->_ref_chir->_view}}</i>
        ({{tr}}COperation-side{{/tr}} {{tr}}COperation.cote.{{$object->_ref_operation->cote}}{{/tr}})
        <ul>
          {{if $object->_ref_operation->libelle}}
            <li><em>[{{$object->_ref_operation->libelle}}]</em></li>
          {{/if}}
          {{foreach from=$object->_ref_operation->_ext_codes_ccam item=curr_code}}
            <li><em>{{$curr_code->libelleLong}}</em> ({{$curr_code->code}})</li>
          {{/foreach}}
        </ul>
      {{else}}
        {{tr}}CPlageOp-back-operations.empty{{/tr}}
      {{/if}}
    </td>
  </tr>
  
  {{if $object->operation_id}}
  <tr>
    <td class="text" colspan="2">
      <strong>{{tr}}CSejour-_admission-court{{/tr}} : </strong>
      <i>
        {{tr}}CSejour.type.{{$object->_ref_operation->_ref_sejour->type}}{{/tr}}
        {{if $object->_ref_operation->_ref_sejour->type!="ambu" && $object->_ref_operation->_ref_sejour->type!="exte"}}
          &mdash; {{tr var1=$object->_ref_operation->_ref_sejour->_duree_prevue}}common-%d day(s){{/tr}}
        {{/if}}
      </i>
    </td>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-Planned anesthesia{{/tr}} :</strong>
      <i>{{$object->_ref_operation->_lu_type_anesth}}</i>
    </td>
  </tr>
  {{/if}}
  
  <tr>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-position_id{{/tr}} :</strong>
      {{if $object->_position_id}}
        <i>{{$object->_ref_position->_view}}</i>
      {{/if}}
    </td>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-back-techniques{{/tr}} :</strong>
      <ul>
        {{foreach from=$object->_ref_techniques item=curr_tech}}
        <li>
          <i>{{$curr_tech->technique}}</i>
        </li>
        {{foreachelse}}
          <li><i>{{tr}}CConsultAnesth-back-techniques.empty{{/tr}}</i></li>
        {{/foreach}}
      </ul>
    </td>
  </tr>
  {{if $object->_refs_info_check_items|@count}}
    <tr>
      <td class="text" colspan="2">
        <strong>{{tr}}CInfoChecklistItem-title-send_to_patient{{/tr}} :</strong>
        <ul>
          {{foreach from=$object->_refs_info_check_items item=_item}}
            <li>{{$_item->_view}}</li>
          {{/foreach}}
        </ul>
      </td>
      <td class="text" colspan="2"></td>
    </tr>
  {{/if}}
  {{assign var=const_med value=$object->_ref_consultation->_ref_patient->_ref_constantes_medicales}}
  {{assign var=dossier_medical value=$object->_ref_consultation->_ref_patient->_ref_dossier_medical}}
  <tr>
    <th class="title" colspan="4">
      {{tr}}CPatient-Patient information{{/tr}}
    </th>
  </tr>
  <tr>
    <td class="text">
      <strong>{{tr}}CConstantesMedicales-poids{{/tr}} :</strong>
      {{if $const_med->poids}}<i>{{$const_med->poids}} {{tr}}CPatient-unit Kg-court{{/tr}}</i>{{/if}}
    </td>
    <td class="text" rowspan="2">
      <strong>{{tr}}CConstantesMedicales-_imc{{/tr}} :</strong>
      {{if $const_med->_imc}}<i>{{$const_med->_imc}}</i>{{/if}}
      {{if $const_med->_imc_valeur}}<br/><i>{{$const_med->_imc_valeur}}</i>{{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CDossierMedical-groupe_sanguin-desc{{/tr}} :</strong>
      <i>{{tr}}CDossierMedical.groupe_sanguin.{{$dossier_medical->groupe_sanguin}}{{/tr}} &nbsp;{{tr}}CDossierMedical.rhesus.{{$dossier_medical->rhesus}}{{/tr}}</i>
    </td>
    <td class="text">
      <strong>{{tr}}CConsultAnesth-rai{{/tr}} :</strong>
      <i>{{tr}}CConsultAnesth.rai.{{$object->rai}}{{/tr}}</i>
    </td>
  </tr>
  
  <tr>
    <td class="text">
      <strong>{{tr}}common-Size{{/tr}} :</strong>
      {{if $const_med->taille}}<i>{{$const_med->taille}} {{tr}}CPatient-unit cm-court{{/tr}}</i>{{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}COperation-ASA{{/tr}} :</strong>
      {{if $object->_ASA}}
        <i>{{tr}}COperation.ASA.{{$object->_ASA}}{{/tr}}</i>
      {{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CConstantesMedicales-_vst{{/tr}} :</strong>
      <i>{{if $const_med->_vst}}{{$const_med->_vst}} ml{{/if}}</i>
    </td>
  </tr>
  
  <tr>
    <td class="text">
      <strong>{{tr}}CConstantesMedicales-pouls{{/tr}} :</strong>
      {{if $const_med->pouls}}<i>{{$const_med->pouls}} / min</i>{{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CConstantesMedicales-ta-court{{/tr}} :</strong>
      {{if $const_med->ta_gauche}}
      <i>
        {{$const_med->_ta_gauche_systole}} / {{$const_med->_ta_gauche_diastole}} cm Hg
      </i>
      {{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CConstantesMedicales-spo2-court{{/tr}} :</strong>
      {{if $const_med->spo2}}<i>{{$const_med->spo2}} %</i>{{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CConsultAnesth-_psa{{/tr}} :</strong>
      {{if $object->_psa}}<i>{{$object->_psa}} ml/GR</i>{{/if}}
    </td>
  </tr>
  <tr>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-tabac{{/tr}} :</strong>
      <i>{{$object->tabac|nl2br}}</i>
    </td>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-oenolisme{{/tr}}:</strong>
      <i>{{$object->oenolisme|nl2br}}</i>
    </td>
  </tr>
  
  {{if $object->mallampati || $object->bouche || $object->distThyro || $object->mob_cervicale || $object->etatBucco ||
       $object->examenCardio || $object->examenPulmo || $object->examenDigest || $object->examenAutre ||
       $object->conclusion}}
  <tr>
    <th class="title" colspan="4">
      {{tr}}CConsultAnesth-legend-Conditions of intubation{{/tr}}
    </th>
  </tr>
  <tr>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-mallampati{{/tr}} :</strong>
      {{if $object->mallampati}}<i>{{tr}}CConsultAnesth.mallampati.{{$object->mallampati}}{{/tr}}</i>{{/if}}
    </td>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-etatBucco{{/tr}} :</strong>
      <i>{{$object->etatBucco|nl2br}}</i>
    </td>
  </tr>
  <tr>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-examenCardio{{/tr}} :</strong>
      <i>{{$object->examenCardio|nl2br}}</i>
    </td>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-examenPulmo{{/tr}} :</strong>
      <i>{{$object->examenPulmo|nl2br}}</i>
    </td>
  </tr>
  <tr>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-examenDigest{{/tr}} :</strong>
      <i>{{$object->examenDigest|nl2br}}</i>
    </td>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-examenAutre{{/tr}} :</strong>
      <i>{{$object->examenAutre|nl2br}}</i>
    </td>
  </tr>
  <tr>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-bouche{{/tr}} :</strong>
      {{if $object->bouche}}<i>{{tr}}CConsultAnesth.bouche.{{$object->bouche}}{{/tr}}</i>{{/if}}
    </td>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-Conclusion{{/tr}} :</strong>
      <i>{{$object->conclusion|nl2br}}</i>
    </td>
  </tr>
  <tr>
    <td class="text" colspan="4">
      <strong>{{tr}}CConsultAnesth-mob_cervicale{{/tr}} :</strong>
      {{if $object->mob_cervicale}}<i>{{tr}}CConsultAnesth.mob_cervicale.{{$object->mob_cervicale}}{{/tr}}</i>{{/if}}
    </td>
  </tr>
  <tr>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-Distance thyroid-chin{{/tr}} :</strong>
      {{if $object->distThyro}}<i>{{tr}}CConsultAnesth.distThyro.{{$object->distThyro}}{{/tr}}</i>{{/if}}
    </td>
    <td class="text" colspan="2">
      <i>
        {{if $object->_intub_difficile}}
          {{tr}}CConsultAnesth-_intub_difficile{{/tr}}
        {{else}}
          {{tr}}CConsultAnesth-_intub_difficile.none{{/tr}}
        {{/if}}
      </i>
    </td>
  </tr>
  {{/if}}
  
  <tr>
    <th class="title" colspan="4">
      {{tr}}CConsultation-back-examcomp{{/tr}}
    </th>
  </tr>
  
  <tr>
    <td class="text">
      <strong>{{tr}}CConsultAnesth-hb{{/tr}} :</strong>
      {{if $object->hb}}<i>{{$object->hb}} g/dl</i>{{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CConsultAnesth-plaquettes{{/tr}} :</strong>
      {{if $object->plaquettes}}<i>{{$object->plaquettes}}</i>{{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CConstantesMedicales-sodium-court{{/tr}} :</strong>
      {{if $object->na}}<i>{{$object->na}} mmol/l</i>{{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CConsultAnesth-tca{{/tr}} :</strong>
      {{if $object->tca}}
        <i>{{$object->tca_temoin}} s / {{$object->tca}} s</i>
      {{/if}}
    </td>  
  </tr>
  <tr>
    <td class="text">
      <strong>{{tr}}CConsultAnesth-ht-court{{/tr}} :</strong>
      {{if $object->ht}}<i>{{$object->ht}} %</i>{{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CConsultAnesth-creatinine{{/tr}} :</strong>
      {{if $object->creatinine}}<i>{{$object->creatinine}} mg/l</i>{{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CConsultAnesth-k{{/tr}} :</strong>
      {{if $object->k}}<i>{{$object->k}} mmol/l</i>{{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CConsultAnesth-tsivy{{/tr}} :</strong>
      {{if $object->tsivy}}<i>{{$object->tsivy|date_format:"%Mm%Ss"}}</i>{{/if}}
    </td>  
  </tr>
  <tr>
    <td class="text">
      <strong>{{tr}}CConsultAnesth-ht_final{{/tr}} :</strong>
      {{if $object->ht_final}}<i>{{$object->ht_final}} %</i>{{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CConsultAnesth-_clairance-desc{{/tr}} :</strong>
      {{if $object->_clairance}}<i>{{$object->_clairance}} ml/min</i>{{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CConsultAnesth-tp{{/tr}} :</strong>
      {{if $object->tp}}<i>{{$object->tp}} %</i>{{/if}}
    </td>
    <td class="text">
      <strong>{{tr}}CConsultAnesth-ecbu{{/tr}} :</strong>
      {{if $object->ecbu}}<i>{{tr}}CConsultAnesth.ecbu.{{$object->ecbu}}{{/tr}}</i>{{/if}}
    </td>  
  </tr>
  
  <tr>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultation-back-examcomp{{/tr}} :</strong>
      {{foreach from=$consult->_types_examen key=curr_type item=list_exams}}
      {{if $list_exams|@count}}
        <br/><i>{{tr}}CExamComp.realisation.{{$curr_type}}{{/tr}}</i>
        <ul>
          {{foreach from=$list_exams item=curr_examcomp}}
          <li>
            {{$curr_examcomp->examen}}
            {{if $curr_examcomp->fait}}
              ({{tr}}Done{{/tr}})
            {{else}}
              ({{tr}}common-To Do{{/tr}})
            {{/if}}
          </li>
          {{/foreach}}
        </ul>
      {{/if}}
      {{foreachelse}}
       <i>{{tr}}CExamComp.none{{/tr}}</i>
      {{/foreach}}
    </td>
    <td class="text" colspan="2">
      {{if $consult->_ref_exampossum->_id}}
        <strong>{{tr}}CExamPossum{{/tr}} :</strong>
        <i>
          {{tr}}CConsult-Morbidity{{/tr}} : {{mb_value object=$consult->_ref_exampossum field="_morbidite"}}% &mdash;
          {{tr}}CConsult-Mortality{{/tr}} : {{mb_value object=$consult->_ref_exampossum field="_mortalite"}}%
        </i><br />
      {{/if}}
      {{if $consult->_ref_examnyha->_id}}
        <strong>{{tr}}CConsult-NYHA Classification{{/tr}} :</strong>
        <i>{{mb_value object=$consult->_ref_examnyha field="_classeNyha"}}</i>   
      {{/if}}
    </td>
  </tr>
  <tr>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-premedication{{/tr}} :</strong>
      <i>{{$object->premedication|nl2br}}</i>
    </td>
    <td class="text" colspan="2">
      <strong>{{tr}}CConsultAnesth-prepa_preop{{/tr}} :</strong>
      <i>{{$object->prepa_preop|nl2br}}</i>
    </td>
  </tr>  
</table>

<table class="tbl">
  {{mb_include module=cabinet template=inc_list_actes_ccam subject=$object->_ref_consultation vue=complete}}
</table>

{{* CConsultation *}}
{{if !$limit_prise_rdv}}
  {{if ($consult->_ref_plageconsult->chir_id == $app->user_id || $can->admin)}}
    <table class="form">
      <tr>
        <th class="title" colspan="2">
          {{tr}}CConsultation-part-Billing{{/tr}}
        </th>
      </tr>
      <tr>
        <td>
          <strong>{{tr}}CConsultation-date_reglement{{/tr}} :</strong>
          {{assign var=facture value=$consult->loadRefFacture()}}
          {{if $facture->patient_date_reglement}}
            <i>{{mb_value object=$facture field=patient_date_reglement}}</i>
          {{else}}
            <i>{{tr}}CConsultation-Unpaid{{/tr}}</i>
          {{/if}}
        </td>
        <td rowspan="3">
          <table class="tbl">
            <tr>
              <th class="category">{{tr}}CReglement-mode{{/tr}}</th>
              <th class="category">{{tr}}CReglement-montant{{/tr}}</th>
              <th class="category">{{tr}}CReglement-date{{/tr}}</th>
              <th class="category">{{tr}}CReglement-banque_id{{/tr}}</th>
            </tr>
            {{foreach from=$consult->_ref_facture->_ref_reglements item=reglement}}
              <tr>
                <td>{{tr}}CReglement.mode.{{$reglement->mode}}{{/tr}}</td>
                <td>{{mb_value object=$reglement field=montant}}</td>
                <td>{{mb_value object=$reglement field=date}}</td>
                <td>{{$reglement->_ref_banque->_view}}</td>
              </tr>
              {{foreachelse}}
              <tr>
                <td colspan="4">{{tr}}CConsultation-No payment made{{/tr}}</td>
              </tr>
            {{/foreach}}
          </table>
        </td>
      </tr>
      <tr>
        <td>
          <strong>{{tr}}CConsultation-Part agreement{{/tr}} :</strong>
          <i>{{mb_value object=$consult field=secteur1}}</i>
        </td>
      </tr>
      <tr>
        <td>
          <strong>{{tr}}CConsultation-Excess of fees{{/tr}} :</strong>
          <i>{{mb_value object=$consult field=secteur2}}</i>
        </td>
      </tr>
    </table>
  {{/if}}
{{/if}}
  
<!-- Dossier Médical -->
{{assign var=sejour value=$object->_ref_sejour}}

{{if !$sejour->_id}}
<div class="big-info">
  {{tr}}CConsultAnesth-msg-The preanesthetic consultation is not associated with any stay, so there is no medical record available.{{/tr}}
</div>
{{elseif $sejour->_ref_dossier_medical->_id}}
  {{mb_include module=patients template=CDossierMedical_complete object=$sejour->_ref_dossier_medical}}
{{/if}}
