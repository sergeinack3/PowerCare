{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=offline value=0}}

{{assign var="operation" value=$dossier_anesth->_ref_operation}}

<script>
  printFiche1 = function() {
    {{if $offline && !$multi}}
      $('fiche_anesth_{{$operation->_id}}').print();
    {{else}}
      var div_fiche = $('fiche_anesth');
      if (div_fiche) {
        div_fiche.print();
      }
      else {
        window.print();
      }
    {{/if}}
  };

  Main.add(function() {
    var cpa_operation = $('cpa_'+'{{$dossier_anesth->_ref_operation->_guid}}');
    if (cpa_operation) {
      {{if $dossier_anesth->_id}}
        cpa_operation.hide();
      {{else}}
        cpa_operation.show();
      {{/if}}
    }
  });
</script>

{{if !$offline || @$multi}}
      </td>
    </tr>
  </table>
  
  {{if $print && !@$multi}}
    <script>
    Main.add(window.print);
    </script> 
  {{/if}}
  
  {{assign var=tbl_class value="print"}}
{{else}}
  {{assign var=tbl_class value="main form"}}
{{/if}}

{{assign var="consult"   value=$dossier_anesth->_ref_consultation}}
{{assign var="patient"   value=$consult->_ref_patient}}
{{assign var="sejour"    value=$operation->_ref_sejour}}

{{assign var=const_med       value=$patient->_ref_constantes_medicales}}
{{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
{{assign var=ant             value=$dossier_medical->_ref_antecedents_by_type}}
{{assign var=intubation_auto value="dPcabinet CConsultAnesth risque_intubation_auto"|gconf}}

<table class="{{$tbl_class}} me-margin-0 me-no-box-shadow">
  <tr>
    <td colspan="2">
      <!-- Bordereau d'en-tête -->
      <table width="100%">
        <tr>
          <th class="title" colspan="7">
            {{if $offline}}
              <button type="button" class="cancel" style="float: right;"
                      onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
            {{/if}}
            <a href="#" onclick="printFiche1();">
              Dossier d'anesthésie de {{$patient->_view}}
            </a>
          </th>
        </tr>
        <tr>
          <th>Telephone</th>
          <td style="white-space: nowrap;">{{mb_value object=$patient field="tel"}}</td>
          <th>Age</th>
          <td style="white-space: nowrap;">{{$patient->_age}}</td>
          <th>C.A.</th>
          <td>
            {{if $consult->_ref_chir->isPraticien()}}Dr{{/if}} {{$consult->_ref_chir->_view}}
            - le {{mb_value object=$consult->_ref_plageconsult field="date"}}</td>
          {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins}}
            <td rowspan="5">{{mb_include module=dPpatients template=vw_datamatrix_ins}}</td>
          {{/if}}
        </tr>
        <tr>
          <th>Mobile</th>
          <td style="white-space: nowrap;">{{mb_value object=$patient field="tel2"}}</td>
          <th>Taille</th>
          <td style="white-space: nowrap;">{{if $const_med->taille}}{{$const_med->taille}} cm{{else}}-{{/if}}</td>
          <th>Séjour</th>
          <td>
            {{if $sejour->_id}}
            {{mb_value object=$sejour field="type"}}
            du {{mb_value object=$sejour field="entree"}}
            au {{mb_value object=$sejour field="sortie"}}
            {{else}}
            -
            {{/if}}
          </td>
        </tr>
        <tr>
          <th>Profession</th>
          <td>{{$patient->profession}}</td>
          <th>Poids</th>
          <td style="white-space: nowrap;">{{if $const_med->poids}}{{$const_med->poids}} kg{{else}}-{{/if}}</td>
          <th>Intervention</th>
          <td>
            {{if $operation->_id}}
              Dr {{$operation->_ref_chir->_view}} - le {{$operation->_datetime|date_format:$conf.date}}
              {{if $operation->libelle}}
                <em>[{{$operation->libelle}}]</em>
              {{/if}}
            {{else}}
              {{if $dossier_anesth->chir_id}}
                Dr {{$dossier_anesth->_ref_chir}} -
              {{/if}}
              {{if $dossier_anesth->date_interv}}
                le {{$dossier_anesth->date_interv|date_format:$conf.date}}
              {{/if}}
              {{if $dossier_anesth->libelle_interv}}
                <em>[{{$dossier_anesth->libelle_interv}}]</em>
              {{/if}}
            {{/if}}
          </td>
        </tr>
        {{if !("moebius"|module_active && $app->user_prefs.ViewConsultMoebius)}}
          <tr>
            <th>{{tr}}CConsultAnesth-APFEL score{{/tr}}</th>
            <td style="white-space: nowrap;">
              {{$dossier_anesth->_score_apfel}}
            </td>
            <th>{{tr}}CExamLee-_score_lee{{/tr}}</th>
            <td style="white-space: nowrap;">
              {{$dossier_anesth->_ref_score_lee->_score_lee}}
            </td>
            <th>{{tr}}CExamMet-_score_met{{/tr}}</th>
            <td style="white-space: nowrap;">
              {{$dossier_anesth->_ref_score_met->_score_met}}
            </td>
          </tr>
          <tr>
          <th>{{tr}}CExamHemostase-_score_hemostase{{/tr}}</th>
            <td style="white-space: nowrap;">
              {{$dossier_anesth->_ref_score_hemostase->_score_hemostase}}
            </td>
            {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins && $patient->status == "QUAL"}}
              <th>{{tr}}CINSPatient{{/tr}}</th>
              <td>
                  {{mb_value object=$patient->_ref_patient_ins_nir field=ins_nir}} ({{$patient->_ref_patient_ins_nir->_ins_type}})
              </td>
            {{/if}}
          </tr>
        {{/if}}
      </table>
    </td>
  </tr>
  <tr>
    <td width="50%">
      <!-- Examens complémentaires / Traitements / Allergies / Code ASA-->
      <table width="100%">
        <tr>
          <th class="category">{{tr}}CConsultation-back-examcomp{{/tr}}</th>
        </tr>
        {{foreach from=$consult->_types_examen key=curr_type item=list_exams}}
        {{if $list_exams|@count}}
        <tr>
          <td>
            {{tr}}CExamComp.realisation.{{$curr_type}}{{/tr}}
          </td>
        </tr>
        <tr>
          <td class="text">
            <ul>
              {{foreach from=$list_exams item=curr_examcomp}}
              <li>
                {{$curr_examcomp->examen}} {{if $curr_examcomp->fait}}(Fait){{/if}}
              </li>
              {{/foreach}}
            </ul>
          </td>
        </tr>
       {{/if}}
       {{foreachelse}}
       <tr>
        <td>{{tr}}CExamComp.none{{/tr}}</td>
      </tr>
      {{/foreach}}
        <tr>
          <th class="category">Traitements</th>
        </tr>
        <tr>
          <td class="text">
            <ul>
              {{foreach from=$dossier_medical->_ref_traitements item=curr_trmt}}
              <li>
                {{if $curr_trmt->fin}}
                  Depuis {{mb_value object=$curr_trmt field=debut}} 
                  jusqu'à {{mb_value object=$curr_trmt field=fin}} :
                {{elseif $curr_trmt->debut}}
                  Depuis {{mb_value object=$curr_trmt field=debut}} :
                {{/if}}
                <i>{{$curr_trmt->traitement}}</i>
              </li>
              {{foreachelse}}
              {{if !($dossier_medical->_ref_prescription && $dossier_medical->_ref_prescription->_ref_prescription_lines|@count)}}
              <li>Pas de traitements</li>
              {{/if}}
              {{/foreach}}
            </ul>
          </td>
        </tr>
        <tr>
          <td class="text">
            <ul>
              {{if $dossier_medical->_ref_prescription}}
                {{foreach from=$dossier_medical->_ref_prescription->_ref_prescription_lines item=_line_med}}
                  <li>
                    {{$_line_med->_ucd_view}}
                    {{if $_line_med->_ref_prises|@count}}
                      ({{foreach from=$_line_med->_ref_prises item=_prise name=foreach_prise}}
                        {{$_prise->_view}}{{if !$smarty.foreach.foreach_prise.last}},{{/if}}
                      {{/foreach}})
                    {{/if}}
                    {{if $_line_med->debut || $_line_med->fin}}
                      <span class="compact">({{mb_include module=system template=inc_interval_date from=$_line_med->debut to=$_line_med->fin}})</span>
                    {{/if}}
                  </li>
                {{/foreach}}
              {{/if}}
            </ul>
          </td>
        </tr>
        <tr>
          <th class="category">Allergies</th>
        </tr>
        <tr>
          <td class="text" style="font-weight: bold; font-size:130%;">
          {{if $dossier_medical->_ref_antecedents_by_type && array_key_exists('alle', $dossier_medical->_ref_antecedents_by_type)}}
            {{foreach from=$dossier_medical->_ref_antecedents_by_type.alle item=currAnt}}
              <ul>
                <li> 
                  {{if $currAnt->date}}
                    {{mb_value object=$currAnt field=date}} :
                  {{/if}}
                  {{$currAnt->rques}} {{if $currAnt->important}}
                                        <strong>({{tr}}CAntecedent-important{{/tr}})</strong>
                                      {{elseif $currAnt->majeur}}
                                        <strong>({{tr}}CAntecedent-majeur{{/tr}})</strong>
                                      {{/if}}
                </li>
              </ul>
            {{/foreach}}
          {{else}}
            <ul>
              <li>{{tr}}CAntecedent-No known allergy-desc{{/tr}}</li>
            </ul>
          {{/if}}
          </td>
        </tr>
        <tr>
          <th class="category">Code ASA : {{if $dossier_anesth->_ASA}}{{tr}}COperation.ASA.{{$dossier_anesth->_ASA}}{{/tr}}{{/if}}</th>
        </tr>
      </table>
    </td>
    <td width="50%">
      <!-- Docs édités / Intubation / Prémédication / Techniques complémentaires-->
      <table width="100%">
        <tr>
          <th class="category">Documents édités</th>
        </tr>
        <tr>
          <td class="text">
            <ul>
              {{foreach from=$dossier_anesth->_ref_documents item=currDoc}}
                <li>{{$currDoc->nom}}<br />
              {{/foreach}}
              {{foreach from=$consult->_ref_documents item=currDoc}}
                <li>{{$currDoc->nom}}<br />
              {{/foreach}}

              {{if !$dossier_anesth->_ref_documents|@count && !$consult->_ref_documents|@count}}
                <li>{{tr}}CMbObject-back-documents.empty{{/tr}}</li>
              {{/if}}
            </ul>
          </td>
        </tr>
        <tr>
          <th class="category">Intubation</th>
        </tr>
        <tr>
          <td class="text">
            <strong>{{mb_label object=$dossier_anesth field=mallampati}} :</strong>
            {{tr}}CConsultAnesth.mallampati.{{$dossier_anesth->mallampati}}{{/tr}}
            <br />
            <strong>Ouverture de la bouche :</strong>
            {{tr}}CConsultAnesth.bouche.{{$dossier_anesth->bouche}}{{/tr}}
            <br />
            <strong>Distance thyro-mentonière :</strong>
            {{tr}}CConsultAnesth.distThyro.{{$dossier_anesth->distThyro}}{{/tr}}
            <br />
            {{if !$intubation_auto}}
              <strong>{{mb_label object=$dossier_anesth field=risque_intub}} :</strong>
              {{mb_value object=$dossier_anesth field=risque_intub}}
              <br />
            {{/if}}
            <strong>Mobilité cervicale :</strong>
            {{tr}}CConsultAnesth.mob_cervicale.{{$dossier_anesth->mob_cervicale}}{{/tr}}
            <br />
            <strong>{{mb_title object=$dossier_anesth field="cormack"}} :</strong>
            {{ if $dossier_anesth->cormack }}
              {{mb_value object=$dossier_anesth field="cormack"}} ({{$dossier_anesth->com_cormack}})
            {{else}}
              -
            {{/if}}
            <br />
            <strong>{{mb_title object=$dossier_anesth field=etatBucco}} :</strong>
            {{$dossier_anesth->etatBucco|nl2br}}
            <br />
            {{if $etatDents}}
              {{$etatDents|nl2br}}
            {{/if}}
            <strong>Conclusion :</strong>
            {{$dossier_anesth->conclusion|nl2br}}
            {{if $intubation_auto}}
              <br />
              {{if $dossier_anesth->_intub_difficile}}
                <span style="font-weight: bold; text-align:center; color:#F00;">
                  Intubation difficile prévisible
                </span>
              {{else}}
                <span style="font-weight: bold; text-align:center;">
                  Pas d'intubation difficile prévisible
                </span>
              {{/if}}
            {{/if}}
          </td>
        </tr>
        <tr>
          <th class="category">{{mb_label object=$dossier_anesth field=premedication}}</th>
        </tr>
        <tr>
          <td>
            {{$dossier_anesth->premedication|nl2br}}
          </td>
        </tr>
        {{assign var=prescription value =$sejour->_ref_prescription_sejour}}
        <tr>
          <td class="text">
            <ul>
              {{foreach from=$lines item=_line}}
                {{if 'Ox\Mediboard\Prescription\CPrescription::isMPMActive'|static_call:null}}
                  {{if $_line|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMedicament'}}
                    {{mb_include module="prescription" template="inc_print_medicament" med=$_line print=0 dci=0}}
                  {{/if}}
                  {{if $_line|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMix'}}
                    {{mb_include module="prescription" template="inc_print_prescription_line_mix" perf=$_line print=0 dci=0}}
                  {{/if}}
                {{/if}}
                {{if $_line|instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineElement'}}
                  {{mb_include module="prescription" template="inc_print_element" elt=$_line}}
                {{/if}}
              {{/foreach}}
            </ul>
          </td>
        </tr>

        {{if $lines_per_op|@count}}
            <tr>
              <th class="category">{{tr}}CPrescription._chapitres.perop{{/tr}}</th>
            </tr>
            {{assign var=prescription value=$sejour->_ref_prescription_sejour}}
            <tr>
              <td class="text">
                <ul>
                  {{foreach from=$lines_per_op item=_line_per_op}}
                    {{if $_line_per_op|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMedicament'}}
                      {{mb_include module="prescription" template="inc_print_medicament" med=$_line_per_op print=0 dci=0}}
                    {{/if}}
                    {{if $_line_per_op|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMix'}}
                      {{mb_include module="prescription" template="inc_print_prescription_line_mix" perf=$_line_per_op print=0 dci=0}}
                    {{/if}}
                    {{if $_line_per_op|instanceof:'Ox\Mediboard\Prescription\CPrescriptionLineElement'}}
                      {{mb_include module="prescription" template="inc_print_element" elt=$_line_per_op}}
                    {{/if}}
                  {{/foreach}}
                </ul>
              </td>
            </tr>
        {{/if}}

        <tr>
          <th class="category">Techniques complémentaires</th>
        </tr>
        <tr>
          <td class="text">
            <ul>
              {{foreach from=$dossier_anesth->_ref_techniques item=curr_tech}}
              <li>
                {{$curr_tech->technique}}<br />
              </li>
              {{foreachelse}}
              <li>Pas de technique complémentaire prévu</li>
              {{/foreach}}
            </ul>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="2"><hr /></td>
  </tr>
  <tr>
    <td>
      <!-- Atcd chirurgicaux / anesthésiques / Biologie-->
      <table width="100%">
        <tr>
          <th class="category">ATCD Chirurgicaux</th>
        </tr>
        <tr>
          <td class="text">
            {{if $dossier_medical->_ref_antecedents_by_type && array_key_exists('chir', $dossier_medical->_ref_antecedents_by_type)}}
            <ul>
            {{foreach from=$dossier_medical->_ref_antecedents_by_type.chir item=currAnt}}
              <li> 
                {{if $currAnt->date}}
                  {{mb_value object=$currAnt field=date}} :
                {{/if}}
                {{$currAnt->rques}} {{if $currAnt->important}}
                                      <strong>({{tr}}CAntecedent-important{{/tr}})</strong>
                                    {{elseif $currAnt->majeur}}
                                      <strong>({{tr}}CAntecedent-majeur{{/tr}})</strong>
                                    {{/if}}
              </li>
            {{/foreach}}
            </ul>
            {{/if}}
          </td>
        </tr>
        <tr>
          <th class="category">ATCD Anesthésiques</th>
        </tr>
        <tr>
          <td class="text">
            {{if $dossier_medical->_ref_antecedents_by_type && array_key_exists('anesth', $dossier_medical->_ref_antecedents_by_type)}}
            <ul>
            {{foreach from=$dossier_medical->_ref_antecedents_by_type.anesth item=currAnt}}
              <li> 
                {{if $currAnt->date}}
                  {{mb_value object=$currAnt field=date}} :
                {{/if}}
                {{$currAnt->rques}} {{if $currAnt->important}}
                                      <strong>({{tr}}CAntecedent-important{{/tr}})</strong>
                                    {{elseif $currAnt->majeur}}
                                      <strong>({{tr}}CAntecedent-majeur{{/tr}})</strong>
                                    {{/if}}
              </li>
            {{/foreach}}
            </ul>
            {{/if}}
          </td>
        </tr>
        <tr>
          <th class="category">Biologie</th>
        </tr>
        <tr>
          <td class="text">
            <table style="width: 100%;">
              <tr>
                <td style="width: 50%">
                  {{if $dossier_anesth->date_analyse}}
                    {{mb_label object=$dossier_anesth field=date_analyse}} {{mb_value object=$dossier_anesth field=date_analyse}}
                    <br />
                  {{/if}}
                  {{if $dossier_medical->groupe_sanguin!="?" || $dossier_medical->rhesus!="?"}}
                    Groupe sanguin&nbsp;:&nbsp;{{tr}}CDossierMedical.groupe_sanguin.{{$dossier_medical->groupe_sanguin}}{{/tr}}&nbsp;{{tr}}CDossierMedical.rhesus.{{$dossier_medical->rhesus}}{{/tr}}
                    <br />
                  {{/if}}
                  {{if $dossier_anesth->rai && $dossier_anesth->rai!="?"}}
                    RAI&nbsp;:&nbsp;{{tr}}CConsultAnesth.rai.{{$dossier_anesth->rai}}{{/tr}}
                    <br />
                  {{/if}}
                  {{if $dossier_anesth->hb}}
                    Hb&nbsp;:&nbsp;{{$dossier_anesth->hb}}&nbsp;g/dl
                    <br />
                  {{/if}}
                  {{if $dossier_anesth->ht}}
                    Ht&nbsp;:&nbsp;{{$dossier_anesth->ht}}&nbsp;%
                    <br />
                  {{/if}}
                  {{if $dossier_anesth->ht_final}}
                    Ht&nbsp;final&nbsp;:&nbsp;{{$dossier_anesth->ht_final}}&nbsp;%
                    <br />
                  {{/if}}
                  {{if $dossier_anesth->_psa}}
                    PSA&nbsp;final&nbsp;:&nbsp;{{$dossier_anesth->_psa}}&nbsp;ml/mg<br />
                  {{/if}}
                  {{if $dossier_anesth->plaquettes}}
                    Plaquettes&nbsp;:&nbsp;{{$dossier_anesth->plaquettes}}&nbsp;(x1000)&nbsp;/mm3
                  {{/if}}
                </td>
                <td style="width: 50%">
                  {{if $dossier_anesth->creatinine}}
                    Créatinine&nbsp;:&nbsp;{{$dossier_anesth->creatinine}}&nbsp;mg/l
                    <br />
                  {{/if}}
                  {{if $dossier_anesth->_clairance}}
                    Créatinine&nbsp;:&nbsp;{{$dossier_anesth->_clairance}}&nbsp;ml/min
                    <br />
                  {{/if}}
                  {{if $dossier_anesth->na}}
                    Na+&nbsp;:&nbsp;{{$dossier_anesth->na}}&nbsp;mmol/l
                    <br />
                  {{/if}}
                  {{if $dossier_anesth->k}}
                    K+&nbsp;:&nbsp;{{$dossier_anesth->k}}&nbsp;mmol/l<br />
                  {{/if}}
                  {{if $dossier_anesth->tp}}
                    TP&nbsp;final&nbsp;:&nbsp;{{$dossier_anesth->tp}}&nbsp;%
                    <br />
                  {{/if}}
                  {{if $dossier_anesth->tca}}
                    TCA&nbsp:&nbsp;{{$dossier_anesth->tca_temoin}}&nbsp;s&nbsp;/&nbsp;{{$dossier_anesth->tca}}&nbsp;s
                    <br />
                  {{/if}}
                  {{if $dossier_anesth->tsivy && $dossier_anesth->tsivy != "00:00:00"}}
                    TS Ivy&nbsp;:&nbsp;{{$dossier_anesth->tsivy|date_format:"%M min %S s"}}
                    <br />
                  {{/if}}
                  {{if $dossier_anesth->ecbu && $dossier_anesth->ecbu!="?"}}
                    ECBU&nbsp;:&nbsp;{{tr}}CConsultAnesth.ecbu.{{$dossier_anesth->ecbu}}{{/tr}}
                    <br />
                  {{/if}}
                </td>
              </tr>
              {{if $dossier_anesth->result_com}}
                <tr>
                  <td colspan="2">
                    {{mb_label object=$dossier_anesth field=result_com}}&nbsp;:&nbsp;{{$dossier_anesth->result_com}}&nbsp;<br />
                  </td>
                </tr>
              {{/if}}
              {{if $dossier_anesth->histoire_maladie}}
                <tr>
                  <td colspan="2">
                    {{mb_label object=$dossier_anesth field=histoire_maladie}}&nbsp;:&nbsp;{{$dossier_anesth->histoire_maladie}}&nbsp;<br />
                  </td>
                </tr>
              {{/if}}
            </table>
          </td>
        </tr>
      </table>
    </td>
    <td>
      <!-- Atcd Cardio / Uro-Nephro / NeuroPsy / Endoc / Gyneco / Autres -->
      <table width="100%">
        <tr>
          <th class="category">Examen Cardiovasculaire</th>
        </tr>
        <tr>
          <td>
            Tension artérielle :
            {{if $const_med->ta_gauche}}
              {{$const_med->_ta_gauche_systole}} / {{$const_med->_ta_gauche_diastole}} cm Hg
            {{elseif $const_med->ta_droit}}
              {{$const_med->_ta_droit_systole}} / {{$const_med->_ta_droit_diastole}} cm Hg
            {{elseif $const_med->ta}}
              {{$const_med->_ta_systole}} / {{$const_med->_ta_diastole}} cm Hg
            {{else}}
              ?
            {{/if}}
            -
            Pouls :
            {{if $const_med->pouls}}
            {{$const_med->pouls}} / min
            {{else}}
            ?
            {{/if}}
            <br />
            {{$dossier_anesth->examenCardio}}
            {{if $dossier_medical->_ref_antecedents_by_type && array_key_exists('cardio', $dossier_medical->_ref_antecedents_by_type)}}
            <ul>
            {{foreach from=$dossier_medical->_ref_antecedents_by_type.cardio item=currAnt}}
              <li> 
                {{if $currAnt->date}}
                  {{mb_value object=$currAnt field=date}} :
                {{/if}}
                {{$currAnt->rques}}
              </li>
            {{/foreach}}
            </ul>
            {{/if}}
          </td>
        </tr>
        
        <tr>
          <th class="category">Examen Pulmonaire</th>
        </tr>
        <tr>
          <td class="text">
            {{$dossier_anesth->examenPulmo}}
            {{if $dossier_medical->_ref_antecedents_by_type && array_key_exists('pulmo', $dossier_medical->_ref_antecedents_by_type)}}
            <ul>
            {{foreach from=$dossier_medical->_ref_antecedents_by_type.pulmo item=currAnt}}
              <li> 
                {{if $currAnt->date}}
                  {{mb_value object=$currAnt field=date}} :
                {{/if}}
                {{$currAnt->rques}}
              </li>
            {{/foreach}}
            </ul>
            {{/if}}
          </td>
        </tr>
        
        <tr>
          <th class="category">Examen Digestif</th>
        </tr>
        <tr>
          <td class="text">
            {{$dossier_anesth->examenDigest}}
            {{if $dossier_medical->_ref_antecedents_by_type && array_key_exists('digestif', $dossier_medical->_ref_antecedents_by_type)}}
            <ul>
            {{foreach from=$dossier_medical->_ref_antecedents_by_type.digestif item=currAnt}}
              <li> 
                {{if $currAnt->date}}
                  {{mb_value object=$currAnt field=date}} :
                {{/if}}
                {{$currAnt->rques}}
              </li>
            {{/foreach}}
            </ul>
            {{/if}}
          </td>
        </tr>
        
        {{if $dossier_anesth->examenAutre}}
        <tr>
          <th class="category">Examen Autre</th>
        </tr>
        <tr>
          <td>
            {{$dossier_anesth->examenAutre}}
          </td>
        </tr>
        {{/if}}
        
        <tr>
          <th class="category">Uro-nephrologie</th>
        </tr>
        <tr>
          <td class="text">
            {{if $dossier_medical->_ref_antecedents_by_type && array_key_exists('uro', $dossier_medical->_ref_antecedents_by_type)}}
            <ul>
            {{foreach from=$dossier_medical->_ref_antecedents_by_type.uro item=currAnt}}
              <li> 
                {{if $currAnt->date}}
                  {{mb_value object=$currAnt field=date}} :
                {{/if}}
                {{$currAnt->rques}} {{if $currAnt->important}}
                                      <strong>({{tr}}CAntecedent-important{{/tr}})</strong>
                                    {{elseif $currAnt->majeur}}
                                      <strong>({{tr}}CAntecedent-majeur{{/tr}})</strong>
                                    {{/if}}
              </li>
            {{/foreach}}
            </ul>
            {{/if}}
          </td>
        </tr>
        <tr>
          <th class="category">Neuro-psychiatrie</th>
        </tr>
        <tr>
          <td class="text">
            {{if $dossier_medical->_ref_antecedents_by_type && array_key_exists('neuropsy', $dossier_medical->_ref_antecedents_by_type)}}
            <ul>
            {{foreach from=$dossier_medical->_ref_antecedents_by_type.neuropsy item=currAnt}}
              <li> 
                {{if $currAnt->date}}
                  {{mb_value object=$currAnt field=date}} :
                {{/if}}
                {{$currAnt->rques}} {{if $currAnt->important}}
                                      <strong>({{tr}}CAntecedent-important{{/tr}})</strong>
                                    {{elseif $currAnt->majeur}}
                                      <strong>({{tr}}CAntecedent-majeur{{/tr}})</strong>
                                    {{/if}}
              </li>
            {{/foreach}}
            </ul>
            {{/if}}
          </td>
        </tr>
        <tr>
          <th class="category">Endocrinologie</th>
        </tr>
        <tr>
          <td class="text">
            {{if $dossier_medical->_ref_antecedents_by_type && array_key_exists('endocrino', $dossier_medical->_ref_antecedents_by_type)}}
            <ul>
            {{foreach from=$dossier_medical->_ref_antecedents_by_type.endocrino item=currAnt}}
              <li> 
                {{if $currAnt->date}}
                  {{mb_value object=$currAnt field=date}} :
                {{/if}}
                {{$currAnt->rques}} {{if $currAnt->important}}
                                      <strong>({{tr}}CAntecedent-important{{/tr}})</strong>
                                    {{elseif $currAnt->majeur}}
                                      <strong>({{tr}}CAntecedent-majeur{{/tr}})</strong>
                                    {{/if}}
              </li>
            {{/foreach}}
            </ul>
            {{/if}}
          </td>
        </tr>
        <tr>
          <th class="category">Gynécologie</th>
        </tr>
        <tr>
          <td class="text">
            {{if $dossier_medical->_ref_antecedents_by_type && array_key_exists('gyn', $dossier_medical->_ref_antecedents_by_type)}}
            <ul>
            {{foreach from=$dossier_medical->_ref_antecedents_by_type.gyn item=currAnt}}
              <li> 
                {{if $currAnt->date}}
                  {{mb_value object=$currAnt field=date}} :
                {{/if}}
                {{$currAnt->rques}} {{if $currAnt->important}}
                                      <strong>({{tr}}CAntecedent-important{{/tr}})</strong>
                                    {{elseif $currAnt->majeur}}
                                      <strong>({{tr}}CAntecedent-majeur{{/tr}})</strong>
                                    {{/if}}
              </li>
            {{/foreach}}
            </ul>
            {{/if}}
          </td>
        </tr>
        <tr>
          <th class="category">Autres</th>
        </tr>
        <tr>
          <td class="text">
            {{$consult->examen}}
            <ul>
              {{foreach from=$dossier_medical->_ref_antecedents_by_type key=type_name item=curr_type}}
              {{if $type_name != 'alle'
                && $type_name != 'chir'
                && $type_name != 'anesth'
                && $type_name != 'cardio'
                && $type_name != 'uro'
                && $type_name != 'neuropsy'
                && $type_name != 'endocrino'
                && $type_name != 'gyn'}}
                {{foreach from=$curr_type item=currAnt}}
                <li> 
                  {{if $currAnt->date}}
                    {{mb_value object=$currAnt field=date}} :
                  {{/if}}
                  {{$currAnt->rques}} {{if $currAnt->important}}
                                        <strong>({{tr}}CAntecedent-important{{/tr}})</strong>
                                      {{elseif $currAnt->majeur}}
                                        <strong>({{tr}}CAntecedent-majeur{{/tr}})</strong>
                                      {{/if}}
                </li>
                {{/foreach}}
              {{/if}}
              {{/foreach}}
            </ul>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  {{if "dPcabinet CConsultAnesth show_facteurs_risque"|gconf}}
  <tr>
    <td colspan="2">
      <table style="width: 100%">
        <tr>
          <th class="category" colspan="3">Facteurs de risque</th>
        </tr>
        <tr>
          <th class="category">Facteur</th>
          <th class="category">{{tr}}Patient{{/tr}}</th>
          <th class="category">Chirurgie</th>
        </tr>
        <tr>
          <th>Maladie thrombo-embolique</th>
          <td style="text-align: center;">
            {{mb_value object=$dossier_medical field="risque_thrombo_patient"}}
          </td>
          <td style="text-align: center;">
            {{mb_value object=$dossier_medical_sejour field="risque_thrombo_chirurgie"}}
          </td> 
        </tr>
        <tr>
          <th>MCJ</th>
          <td style="text-align: center;">
            {{mb_value object=$dossier_medical field="risque_MCJ_patient"}}
          </td>
          <td style="text-align: center;">
            {{mb_value object=$dossier_medical_sejour field="risque_MCJ_chirurgie"}}
          </td> 
        </tr>
        <tr>
          <th>{{tr}}CDossierMedical-risque_antibioprophylaxie-desc{{/tr}}</th>
          <td style="text-align: center;">&mdash;</td>
          <td style="text-align: center;">
            {{mb_value object=$dossier_medical_sejour field="risque_antibioprophylaxie"}}
          </td> 
        </tr>
        <tr>
          <th>{{tr}}CDossierMedical-risque_prophylaxie-desc{{/tr}}</th>
          <td style="text-align: center;">&mdash;</td>
          <td style="text-align: center;">
            {{mb_value object=$dossier_medical_sejour field="risque_prophylaxie"}}
         </td>  
        </tr>
        <tr>
          <th>{{tr}}CDossierMedical-Eligible stay for outpatient care{{/tr}}</th>
          <td style="text-align: center;">&mdash;</td>
          <td style="text-align: center;">
            {{mb_value object=$sejour field="pec_ambu"}}
          </td>
        </tr>
        <tr>
          <th>{{tr}}CSejour-rques_pec_ambu{{/tr}}</th>
          <td style="text-align: center;">&mdash;</td>
          <td style="text-align: center;">
            {{mb_value object=$sejour field="rques_pec_ambu"}}
          </td>
        </tr>
        <tr>
          <th>{{tr}}{{mb_label object=$dossier_medical field="facteurs_risque"}}{{/tr}}</th>
          <td style="text-align: center;">
            {{mb_value object=$dossier_medical field="facteurs_risque"}}
          </td>
          <td></td>
        </tr>
     </table>
   </td>
  </tr>
  {{/if}}

  {{assign var=hide_visite value='dPsalleOp COperation hide_visite_pre_anesth'|gconf}}
  {{if !$operation->urgence || ($operation->urgence && !$hide_visite)}}
    <tr>
      <th class="category" colspan="2">Visite préanesthésique
        {{if $operation->date_visite_anesth}}
          - {{$operation->date_visite_anesth|date_format:$conf.date}}
          {{if "dPsalleOp COperation use_time_vpa"|gconf && $operation->time_visite_anesth}}
            à {{$operation->time_visite_anesth|date_format:$conf.time}}
          {{/if}}
        {{/if}}
      </th>
    </tr>
    {{if $operation->date_visite_anesth}}
      <tr>
        <td colspan="2">
          <table>
            <tr>
              <th>{{mb_label object=$operation field="prat_visite_anesth_id"}}</th>
              <td>{{mb_value object=$operation field="prat_visite_anesth_id"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$operation field="rques_visite_anesth"}}</th>
              <td>{{mb_value object=$operation field="rques_visite_anesth"}}</td>
            </tr>
            <tr>
              <th>{{mb_label object=$operation field="autorisation_anesth"}}</th>
              <td>{{mb_value object=$operation field="autorisation_anesth"}}</td>
            </tr>
          </table>
        </td>
      </tr>
    {{else}}
      <tr>
        <td colspan="2">
          <table>
            <tr>
              <th>{{mb_label object=$operation field="prat_visite_anesth_id"}}</th>
              <td></td>
            </tr>
            <tr style="height: 4em;">
              <th>{{mb_label object=$operation field="rques_visite_anesth"}}</th>
              <td></td>
            </tr>
            <tr>
              <th>{{mb_label object=$operation field="autorisation_anesth"}}</th>
              <td>Oui - Non</td>
            </tr>
          </table>
        </td>
      </tr>
    {{/if}}
  {{/if}}
</table>

{{if !$offline}}
<table class="main">
  <tr>
    <td>
{{/if}}
