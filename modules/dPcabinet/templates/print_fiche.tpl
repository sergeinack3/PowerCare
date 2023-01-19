{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=display value=false}}
{{mb_default var=offline value=false}}

<script>
  printFiche = function (dossier_anesth_id) {
    var url = new Url("dPcabinet", "print_fiche");
    url.addParam("dossier_anesth_id", dossier_anesth_id);
    url.addParam("print", true);
    url.popup(700, 500, "printFiche");
  };

  Main.add(function () {
    if ($('anesth_tab_group')) {
      $('anesth_tab_group').select('a[href=#fiche_anesth]')[0].removeClassName('wrong');
    }
    var cpa_operation = $('cpa_' + '{{$dossier_anesth->_ref_operation->_guid}}');
    if (cpa_operation) {
      {{if $dossier_anesth->_id}}
      cpa_operation.hide();
      {{else}}
      cpa_operation.show();
      {{/if}}
    }
  });
</script>

{{if !@$offline || @$multi}}
  {{if !$offline}}
    </td>
    </tr>
    </table>
  {{/if}}

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
{{assign var="operation" value=$dossier_anesth->_ref_operation}}
{{assign var="sejour"    value=$operation->_ref_sejour}}
{{assign var=consult_anesth value=$operation->_ref_consult_anesth}}

{{if $operation->_id && $display && !$offline}}
  {{mb_script module="cabinet" script="edit_consultation" ajax=true}}
  <script>
    refreshFicheAnesthOp = function (form) {
      var rep = confirm('Êtes-vous sûr de vouloir délier ce dossier à l\'intervention?');
      if (rep) {
        return onSubmitFormAjax(form, {
          onComplete: function () {
            var url = new Url("cabinet", "print_fiche");
            url.addParam("operation_id", "{{$operation->_id}}");
            url.addParam("dossier_anesth_id", 0);
            url.addParam("offline", 0);
            url.addParam("display", 1);
            url.addParam("pdf", 0);
            url.requestUpdate("fiche_anesth");
          }
        });
      }
    }
  </script>
  {{if !$offline}}
    <button type="button" class="print" onclick="printFiche('{{$dossier_anesth->_id}}');" style="float:left;">
      {{tr}}CConsultation-Print the card{{/tr}}
    </button>
    <button type="button" class="edit"
            onclick="Consultation.editModalDossierAnesth('{{$consult->_id}}', '{{$dossier_anesth->_id}}', refreshFicheAnesth);"
            style="float:left;">
      Modifier le dossier d'anesthésie
    </button>
    <form name="addInterv-{{$operation->_id}}" action="?m={{$m}}" method="post" onsubmit="return refreshFicheAnesthOp(this);">
      <input type="hidden" name="dosql" value="do_consult_anesth_aed"/>
      <input type="hidden" name="m" value="dPcabinet"/>
      <input type="hidden" name="operation_id" value=""/>
      <input type="hidden" name="sejour_id" value=""/>
      {{mb_key object=$dossier_anesth}}
      <button type="button" class="unlink" onclick="return refreshFicheAnesthOp(this.form);" style="float:left;">
        {{tr}}CConsultation-action-Delete link to intervention{{/tr}}
      </button>
    </form>
  {{/if}}
{{/if}}
{{if $display && $dossiers|@count != 0 && !$offline}}
  <span style="display:inline-block;float:right;" onmouseover="ObjectTooltip.createDOM(this, 'DetailDossiers');">
    {{$dossiers|@count}} Dossiers d'anesthésie
  </span>
  <div style="display: none;">
    <table class="tbl" id="DetailDossiers">
      <tr>
        <th class="title" colspan="4">Dossiers d'anesthésie</th>
      </tr>
      {{foreach from=$dossiers item=_dossier}}
        <tr>
          <td>{{tr}}CConsultation{{/tr}} du {{$_dossier->_ref_consultation->_date|date_format:$conf.date}}</td>
          <td>{{if $_dossier->_ref_consultation->_ref_chir->isPraticien()}}Dr{{/if}} {{$_dossier->_ref_consultation->_ref_chir->_view}}</td>
          <td>{{if $_dossier->operation_id}} <strong>{{$_dossier->_ref_operation->_view}}</strong>{{/if}}</td>
          <td>
            <button type="button" class="print notext" onclick="printFiche('{{$_dossier->_id}}');"></button>
          </td>
        </tr>
      {{/foreach}}
    </table>
  </div>
  <br/>
{{/if}}

{{mb_include module=cabinet template=inc_header_fiche_anesth}}

<table class="{{$tbl_class}}" style="page-break-after: always">
  <tr>
    <td colspan="2" class="text" style="word-break: break-all">
      <table style="width: 100%">
        <tr>
          <th class="category" colspan="2">
            Intervention
            {{if $other_intervs|@count > 1 && $operation->_id && $pos_curr_interv}}
              ({{$pos_curr_interv}}/{{$other_intervs|@count}})
            {{/if}}
          </th>
        </tr>
        <tr>
          <td colspan="2">
            {{if $operation->_id}}
            {{if $sejour}}
              Admission en {{tr}}CSejour.type.{{$sejour->type}}{{/tr}}
              le
              <strong>{{$sejour->entree|date_format:"%A %d/%m/%Y à %Hh%M"}}</strong>
              pour
              <strong>{{$sejour->_duree_prevue}} nuit(s)</strong>
              <br/>
            {{/if}}
            {{else}}
            {{tr}}dPplanningOp-COperation of{{/tr}} <strong>{{$dossier_anesth->date_interv|date_format:"%A %d/%m/%Y"}}</strong>
            par {{if $dossier_anesth->_ref_chir->isPraticien()}}le <strong>Dr{{else}}
              <strong>{{/if}} {{$dossier_anesth->_ref_chir->_view}}</strong>
              <br/>
              {{$dossier_anesth->libelle_interv}}
              {{/if}}
          </td>
        </tr>
        {{if $operation->_id}}
          <tr>
            <td colspan="2" class="text">
              {{tr}}dPplanningOp-COperation of{{/tr}}
              <strong>
                {{$operation->date|date_format:"%A %d/%m/%Y"}}
              </strong>
              {{if $operation->libelle}}
                - {{$operation->libelle}}
              {{/if}}
              <ul>
                {{if $operation->libelle}}
                  <li><em>[{{$operation->libelle}}]</em></li>
                {{/if}}
                {{foreach from=$operation->_ext_codes_ccam item=curr_code}}
                  <li><em>{{$curr_code->libelleLong}}</em> ({{$curr_code->code}})
                    (coté {{tr}}COperation.cote.{{$operation->cote}}{{/tr}})
                  </li>
                {{/foreach}}
              </ul>
            </td>
          </tr>
        {{/if}}
        <tr>
          <td class="halfPane">
            <table>
              {{if $conf.dPplanningOp.COperation.show_duree_uscpo != "0" && $operation->_id}}
                <tr>
                  <th style="font-weight: normal;">USCPO</th>
                  <td style="font-weight: bold;">
                    {{if !$operation->passage_uscpo}}
                      Non
                    {{else}}
                      {{$operation->duree_uscpo}} nuit(s)
                    {{/if}}
                  </td>
                </tr>
              {{/if}}

              {{if $dossier_anesth->type_anesth != $operation->type_anesth && $dossier_anesth->type_anesth != ""}}
                <tr>
                  <th style="font-weight: normal;">{{tr}}CConsultAnesth-Type of anesthesia planned{{/tr}}</th>
                  <td style="font-weight: bold;">
                    {{mb_value object=$dossier_anesth field=type_anesth}}
                  </td>
                </tr>
              {{/if}}

              {{if $dossier_anesth->position_id != $operation->position_id && $dossier_anesth->position_id != ""}}
                <tr>
                  <th style="font-weight: normal;">{{tr}}CConsultAnesth-Position planned{{/tr}}</th>
                  <td style="font-weight: bold;">
                    {{mb_value object=$dossier_anesth field=position_id}}
                  </td>
                </tr>
              {{/if}}

              {{if $operation->_id}}
                <tr>
                  <th style="font-weight: normal;">{{tr}}COperation-Type of anesthesia performed{{/tr}}</th>
                  <td style="font-weight: bold;">
                    {{$operation->_lu_type_anesth}}
                  </td>
                </tr>
                <tr>
                  <th style="font-weight: normal;">{{tr}}COperation-Position performed{{/tr}}</th>
                  <td style="font-weight: bold;">
                    {{mb_value object=$operation field=position_id}}
                  </td>
                </tr>
                <tr>
                  <th style="font-weight: normal;">{{tr}}COperation-cote{{/tr}}</th>
                  <td style="font-weight: bold;">
                    {{tr}}COperation.cote.{{$operation->cote}}{{/tr}}
                  </td>
                </tr>
              {{/if}}
            </table>
          </td>
          <td class="halfPane text">
            <strong>Techniques Complémentaires</strong>
            <ul>
              {{foreach from=$dossier_anesth->_ref_techniques item=curr_tech}}
                <li>
                  {{$curr_tech->technique}}
                </li>
                {{foreachelse}}
                <li>Pas de technique complémentaire prévue</li>
              {{/foreach}}
            </ul>
          </td>
        </tr>
        {{if $operation->rques}}
          <tr>
            <td colspan="2" style="font-weight: bold;">
              <div class="small-warning">
                {{mb_value object=$operation field=rques}}
              </div>
            </td>
          </tr>
        {{/if}}
        {{if $consult->rques || ($dossier_anesth->rques && !$operation->rques)}}
          <tr>
            <td colspan="2">
              <strong>Remarques</strong>
              {{$consult->rques|nl2br}}
              {{if !$operation->rques}}
                {{if $consult->rques}}
                  <br />
                {{/if}}
                {{$dossier_anesth->rques|nl2br}}
              {{/if}}
            </td>
          </tr>
        {{/if}}
        {{if $dossier_anesth->_refs_info_check_items|@count}}
          <tr>
            <td colspan="2">
              <strong>{{tr}}CInfoChecklistItem-title-send_to_patient{{/tr}}</strong>
              <ul>
                {{foreach from=$dossier_anesth->_refs_info_check_items item=_item}}
                  <li>{{$_item->_view}}</li>
                {{/foreach}}
              </ul>
            </td>
          </tr>
        {{/if}}
        {{if $dossier_anesth->strategie_antibio}}
          <tr>
            <td colspan="2">
              <strong>{{mb_label object=$dossier_anesth field=strategie_antibio}}</strong>
              {{$dossier_anesth->strategie_antibio|nl2br}}
            </td>
          </tr>
        {{/if}}
        {{if $dossier_anesth->strategie_prevention}}
          <tr>
            <td colspan="2">
              <strong>{{mb_label object=$dossier_anesth field=strategie_prevention}}</strong>
              {{$dossier_anesth->strategie_prevention|nl2br}}
            </td>
          </tr>
        {{/if}}
        {{if $other_intervs|@count >= 2}}
          <tr>
            <th class="category" colspan="2">Autres interventions reliées</th>
          </tr>
          {{foreach from=$other_intervs item=_op}}
            {{if $_op->_id != $dossier_anesth->operation_id}}
              <tr>
                <td colspan="2">
                  {{tr}}dPplanningOp-COperation of{{/tr}} <strong>{{$_op->_datetime_best|date_format:"%A %d/%m/%Y"}}</strong>
                  {{if $_op->libelle}}
                    - {{$_op->libelle}}
                  {{/if}}
                  {{if $_op->cote}}
                    - {{mb_label object=$_op field=cote}} {{mb_value object=$_op field=cote}}
                  {{/if}}
                </td>
              </tr>
            {{/if}}
          {{/foreach}}
        {{/if}}
      </table>
    </td>
  </tr>

  {{assign var=const_med value=$patient->_ref_constantes_medicales}}
  {{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
  {{assign var=ant value=$dossier_medical->_ref_antecedents_by_type}}
  <tr>
    <td class="halfPane text" {{if !$dossier_medical->_count_allergies}}colspan="2"{{/if}} style="word-break: break-all">
      <table style="width: 100%">
        <tr>
          <th class="category" colspan="2">{{tr}}CPatient-Patient information{{/tr}}</th>
        </tr>
        <tr>
          <td colspan="2">
            {{$patient->_view}}
            {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
          </td>
        </tr>
        {{if $patient->nom_jeune_fille}}
          <tr>
            <th>{{mb_label object=$patient field=nom_jeune_fille}}</th>
            <td>{{$patient->nom_jeune_fille}}</td>
          </tr>
        {{/if}}
        <tr>
          <td colspan="2" class="text">
            Né{{if $patient->sexe != "m"}}e{{/if}} le {{mb_value object=$patient field=naissance}}
            ({{$patient->_age}})
            - sexe {{tr}}CPatient.sexe.{{$patient->sexe}}{{/tr}}<br/>
            {{if $patient->profession}}Profession : {{$patient->profession}}<br/>{{/if}}
            {{if $const_med->poids}}<strong>{{$const_med->poids}} kg</strong> - {{/if}}
            {{if $const_med->taille}}<strong>{{$const_med->taille}} cm</strong> - {{/if}}
            {{if $const_med->_imc}}IMC :
              <strong>{{$const_med->_imc}}</strong>
              {{if $const_med->_imc_valeur}}({{$const_med->_imc_valeur}}){{/if}}
            {{/if}}
            {{if $const_med->_poids_ideal}}
              - Poids idéal (Formule de Lorentz) :
              <strong>{{$const_med->_poids_ideal}} kg</strong>
            {{/if}}
          </td>
        </tr>
        <tr>
          <td {{if !$dossier_medical->_count_allergies && !$dossier_medical->risque_viral_rq}}colspan="2"{{/if}}>
            <table>
              {{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
              {{if $dossier_medical->groupe_sanguin != "?" || $dossier_medical->rhesus != "?"}}
                <tr>
                  <th style="font-weight: normal;">Groupe sanguin</th>
                  <td style="font-weight: bold; font-size:130%;">
                    &nbsp;{{tr}}CDossierMedical.groupe_sanguin.{{$dossier_medical->groupe_sanguin}}{{/tr}}
                    &nbsp;{{tr}}CDossierMedical.rhesus.{{$dossier_medical->rhesus}}{{/tr}}</td>
                </tr>
              {{/if}}
              {{if $dossier_anesth->rai && $dossier_anesth->rai!="?"}}
                <tr>
                  <th style="font-weight: normal;">RAI</th>
                  <td style="font-weight: bold; font-size:130%;">&nbsp;{{tr}}CConsultAnesth.rai.{{$dossier_anesth->rai}}{{/tr}}</td>
                </tr>
              {{/if}}
              <tr>
                <th style="font-weight: normal;">ASA</th>
                <td style="font-weight: bold;">
                  {{if $dossier_anesth->_ASA}}
                    {{tr}}COperation.ASA.{{$dossier_anesth->_ASA}}{{/tr}}
                  {{/if}}
                </td>
              </tr>
              <tr>
                <th style="font-weight: normal;">VST</th>
                <td style="font-weight: bold;">
                  {{if $const_med->_vst}}{{$const_med->_vst}} ml{{/if}}
                </td>
              </tr>
              <tr>
                <th style="font-weight: normal;">APFEL</th>
                <td style="font-weight: bold;">{{$dossier_anesth->_score_apfel}}</td>
              </tr>
              <tr>
                <th style="font-weight: normal;">{{tr}}CExamLee-_score_lee-court{{/tr}}</th>
                <td style="font-weight: bold;">{{$dossier_anesth->_ref_score_lee->_score_lee}}</td>
              </tr>
              <tr>
                <th style="font-weight: normal;">{{tr}}CExamMet-_score_met-court{{/tr}}</th>
                <td style="font-weight: bold;">{{$dossier_anesth->_ref_score_met->_score_met}}</td>
              </tr>
              {{if $dossier_anesth->_ref_score_hemostase}}
                <tr>
                  <th style="font-weight: normal;">{{tr}}CExamHemostase-_score_hemostase-court{{/tr}}</th>
                  <td style="font-weight: bold;">{{$dossier_anesth->_ref_score_hemostase->_score_hemostase}}</td>
                </tr>
              {{/if}}
              {{if $dossier_anesth->_psa}}
                <tr>
                  <th style="font-weight: normal;">PSA</th>
                  <td style="font-weight: bold;">
                    {{$dossier_anesth->_psa}} ml/GR
                  </td>
                  <td colspan="2"></td>
                </tr>
              {{/if}}
            </table>
          </td>
          {{if !$dossier_medical->_count_allergies && $dossier_medical->risque_viral_rq}}
            <td class="halfPane">
              <table>
                <tr>
                  <th style="font-weight: bold;">{{tr}}CMoebius.risque_viral.short{{/tr}}</th>
                  <td>{{$dossier_medical->risque_viral_rq}}</td>
                </tr>
              </table>
            </td>
          {{/if}}
        </tr>
      </table>
    </td>
    {{if $dossier_medical->_count_allergies}}
      <td class="halfPane text" style="word-break: break-all">
        <table style="width: 100% ">
          <tr>
            <th class="category" colspan="2">{{tr}}CAntecedent-Allergie|pl{{/tr}}</th>
          </tr>
          <tr>
            <td class="text" style="font-weight: bold; font-size:130%;">
              {{if $dossier_medical->_ref_antecedents_by_type && $dossier_medical->_ref_antecedents_by_type.alle|@count}}
                <div class="small-warning">
                  {{foreach from=$dossier_medical->_ref_antecedents_by_type.alle item=currAnt}}
                    <ul>
                      <li>
                        {{if $currAnt->date}}
                          {{mb_value object=$currAnt field=date}} :
                        {{/if}}
                        {{$currAnt->rques}}
                      </li>
                    </ul>
                  {{/foreach}}
                </div>
              {{else}}
                <ul>
                  <li>{{tr}}CAntecedent-No known allergy-desc{{/tr}}</li>
                </ul>
              {{/if}}
            </td>
          </tr>
          {{if $dossier_medical->risque_viral_rq}}
          <tr>
            <td>
              <table>
                <tr>
                  <th style="font-weight: bold;">{{tr}}CMoebius.risque_viral.short{{/tr}}</th>
                  <td>{{$dossier_medical->risque_viral_rq}}</td>
                </tr>
              </table>
            </td>
          </tr>
          {{/if}}
        </table>
      </td>
    {{/if}}
  </tr>
  <tr>
    <td class="halfPane text" rowspan="2" style="word-break: break-all">
      <table style="width: 100%">
        <tr>
          <th class="category">{{tr}}CAntecedent.more{{/tr}}</th>
        </tr>
        <tr>
          <td class="text">
            {{if $dossier_medical->_ref_antecedents_by_type}}
              {{foreach from=$dossier_medical->_ref_antecedents_by_type key=keyAnt item=currTypeAnt}}
                {{if $currTypeAnt}}
                  <strong>{{tr}}CAntecedent.type.{{$keyAnt}}{{/tr}}</strong>
                  {{foreach from=$currTypeAnt item=currAnt}}
                    <ul>
                      <li>
                        {{if $currAnt->appareil}}<strong>{{tr}}CAntecedent.appareil.{{$currAnt->appareil}}{{/tr}}</strong>{{/if}}
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
                {{/if}}
              {{/foreach}}
            {{else}}
              <ul>
                <li>Pas d'antécédents</li>
              </ul>
            {{/if}}
          </td>
        </tr>
      </table>
    </td>

    {{if is_array($dossier_medical->_ref_traitements) || $dossier_medical->_ref_prescription}}
      <!-- Traitements -->
      <td class="halfPane text">
        <table style="width: 100%">
          <tr>
            <th class="category">Traitements</th>
          </tr>
          {{if is_array($dossier_medical->_ref_traitements)}}
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
                    {{if $dossier_medical->absence_traitement}}
                      <li>{{tr}}CTraitement.absence{{/tr}}</li>
                    {{elseif !($dossier_medical->_ref_prescription && $dossier_medical->_ref_prescription->_ref_prescription_lines|@count) && !($lines_tp|@count)}}
                      <li>{{tr}}CTraitement.none{{/tr}}</li>
                    {{/if}}
                  {{/foreach}}
                </ul>
              </td>
            </tr>
          {{/if}}
          <tr>
            <td class="text">
              <ul>
                {{mb_include module=prescription template=inc_print_prescription_conduite pdf_mode=0}}

                {{if $dossier_medical->_ref_prescription}}
                  {{foreach from=$dossier_medical->_ref_prescription->_ref_prescription_lines item=_line_med}}
                    <li>
                      <a href="#1" onclick="Prescription.viewProduit(null,'{{$_line_med->code_ucd}}','{{$_line_med->code_cis}}');">
                        {{$_line_med->_ucd_view}}
                      </a>
                      {{if $_line_med->_ref_prises|@count}}
                        ({{foreach from=$_line_med->_ref_prises item=_prise name=foreach_prise}}
                        {{$_prise->_view}}{{if !$smarty.foreach.foreach_prise.last}},{{/if}}
                      {{/foreach}})
                      {{/if}}
                      {{if $_line_med->commentaire}}
                        ({{$_line_med->commentaire}})
                      {{/if}}
                      {{if $_line_med->debut || $_line_med->fin}}
                        <span
                          class="compact">({{mb_include module=system template=inc_interval_date from=$_line_med->debut to=$_line_med->fin}})</span>
                      {{/if}}
                    </li>
                  {{/foreach}}
                {{/if}}
              </ul>
            </td>
          </tr>
        </table>
      </td>
    {{/if}}
  </tr>
  <tr>

    <!-- Examens cliniques -->
    <td class="halfPane text">
      <table style="width: 100%">
        <tr>
          <th class="category" colspan="6">Examens Clinique</th>
        </tr>
        <tr>
          <th style="font-weight: normal;">Pouls</th>
          <td style="font-weight: bold;">
            {{if $const_med->pouls}}
              {{$const_med->pouls}} / min
            {{else}}
              ?
            {{/if}}
          </td>
          <th style="font-weight: normal;">TA</th>
          <td style="font-weight: bold;">
            {{if $const_med->ta_gauche}}
              {{$const_med->_ta_gauche_systole}} / {{$const_med->_ta_gauche_diastole}} cm Hg
            {{elseif $const_med->ta_droit}}
              {{$const_med->_ta_droit_systole}} / {{$const_med->_ta_droit_diastole}} cm Hg
            {{elseif $const_med->ta}}
              {{$const_med->_ta_systole}} / {{$const_med->_ta_diastole}} cm Hg
            {{else}}
              ?
            {{/if}}
          </td>
          <th style="font-weight: normal;">Spo2</th>
          <td class="text" style="font-weight: bold;">
            {{if $const_med->spo2}}
              {{$const_med->spo2}} %
            {{else}}
              ?
            {{/if}}
          </td>
        </tr>
        <tr>
          <th style="font-weight: normal;">{{mb_label object=$dossier_anesth field=examenCardio}}</th>
          <td colspan="5" style="font-weight: bold;" class="text">{{$dossier_anesth->examenCardio}}</td>
        </tr>
        <tr>
          <th style="font-weight: normal;">{{mb_label object=$dossier_anesth field=examenPulmo}}</th>
          <td colspan="5" style="font-weight: bold;" class="text">{{$dossier_anesth->examenPulmo}}</td>
        </tr>
        <tr>
          <th style="font-weight: normal;">{{mb_label object=$dossier_anesth field=examenDigest}}</th>
          <td colspan="5" style="font-weight: bold;" class="text">{{$dossier_anesth->examenDigest}}</td>
        </tr>
        <tr>
          <th style="font-weight: normal;">{{mb_label object=$dossier_anesth field=examenAutre}}</th>
          <td colspan="5" style="font-weight: bold;" class="text">{{$dossier_anesth->examenAutre}}</td>
        </tr>
        {{if $consult->examen}}
          <tr>
            <th style="font-weight: normal;">{{mb_label object=$consult field=examen}}</th>
            <td colspan="5" style="font-weight: bold;" colspan="5" class="text">{{$consult->examen|nl2br}}</td>
          </tr>
        {{/if}}
      </table>
    </td>

  </tr>
</table>

{{if !$display}}
  {{mb_include module=cabinet template=inc_header_fiche_anesth}}
{{/if}}

{{assign var=intubation_auto value="dPcabinet CConsultAnesth risque_intubation_auto"|gconf}}

<table class="{{$tbl_class}}">
  <tr>
    <td style="word-break: break-all">
      <table style="width: 100%">
        <tr>
          <th colspan="3" class="category">{{tr}}CConsultAnesth-legend-Conditions of intubation{{/tr}}</th>
        </tr>
        <tr>
          {{if $dossier_anesth->mallampati}}
            <td rowspan="9" class="button text">
              <img src="images/pictures/{{$dossier_anesth->mallampati}}.png"
                   alt="{{mb_value object=$dossier_anesth field=mallampati}}"/>
              <br/>{{mb_label object=$dossier_anesth field=mallampati}}<br/>de {{mb_value object=$dossier_anesth field=mallampati}}
            </td>
          {{/if}}
          <th style="font-weight: normal;">{{mb_label object=$dossier_anesth field=bouche}}</th>
          <td style="font-weight: bold;">{{mb_value object=$dossier_anesth field=bouche}}</td>
        </tr>
        {{if !$intubation_auto}}
          <tr>
            <th style="font-weight: normal;">{{mb_label object=$dossier_anesth field=risque_intub}}</th>
            <td style="font-weight: bold;">{{mb_value object=$dossier_anesth field=risque_intub}}</td>
          </tr>
        {{/if}}
        <tr>
          <th style="font-weight: normal;">{{mb_label object=$dossier_anesth field=distThyro}}</th>
          <td style="font-weight: bold;">{{mb_value object=$dossier_anesth field=distThyro}}</td>
        </tr>
        <tr>
          <th style="font-weight: normal;">{{mb_label object=$dossier_anesth field=mob_cervicale}}</th>
          <td style="font-weight: bold;">{{mb_value object=$dossier_anesth field=mob_cervicale}}</td>
        </tr>
        <tr>
          <th style="font-weight: normal;">{{tr}}CConsultAnesth-legend-Criteria for ventilation{{/tr}}</th>
          <td style="font-weight: bold;" class="text">
            {{if $dossier_anesth->plus_de_55_ans}}{{mb_label object=$dossier_anesth field=plus_de_55_ans}}<br/>{{/if}}
            {{if $dossier_anesth->imc_sup_26}}{{mb_label object=$dossier_anesth field=imc_sup_26}}<br/>{{/if}}
            {{if $dossier_anesth->edentation}}{{mb_label object=$dossier_anesth field=edentation}}<br/>{{/if}}
            {{if $dossier_anesth->ronflements}}{{mb_label object=$dossier_anesth field=ronflements}}<br/>{{/if}}
            {{if $dossier_anesth->barbe}}{{mb_label object=$dossier_anesth field=barbe}}{{/if}}
            {{if $dossier_anesth->piercing}}{{mb_label object=$dossier_anesth field=piercing}}{{/if}}
          </td>
        </tr>
        <tr>
          <th style="font-weight: normal;">{{mb_title object=$dossier_anesth field="cormack"}}</th>
          <td style="font-weight: bold;">
            {{if $dossier_anesth->cormack}}
              {{mb_value object=$dossier_anesth field="cormack"}} ({{$dossier_anesth->com_cormack}})
            {{else}}
              -
            {{/if}}
          </td>
        </tr>
        <tr>
          <th style="font-weight: normal;">{{mb_title object=$dossier_anesth field=etatBucco}}</th>
          <td style="font-weight: bold;" class="text">{{$dossier_anesth->etatBucco}}
            <br/>
            {{if $etatDents}}
              {{$etatDents|nl2br}}
            {{/if}}
          </td>
        </tr>
        <tr>
          <th style="font-weight: normal;">Conclusion</th>
          <td style="font-weight: bold;" class="text">{{$dossier_anesth->conclusion}}</td>
        </tr>
        {{if $intubation_auto}}
          <tr>
            {{if $dossier_anesth->_intub_difficile}}
              <td colspan="3" style="font-weight: bold; text-align:center; color:#F00;">
                {{tr}}CConsultAnesth-_intub_difficile{{/tr}}
              </td>
            {{else}}
              <td colspan="3" style="font-weight: bold; text-align:center;">
                Pas d'intubation difficile prévisible
              </td>
            {{/if}}
          </tr>
        {{/if}}
      </table>

      <table style="width: 100%">
        <tr>
          <th class="category" colspan="3">{{tr}}CConsultation-back-examcomp{{/tr}}</th>
        </tr>

        <tr>
          {{foreach from=$listChamps item=aChamps name=champ key=key_champ}}
            <td {{if in_array($key_champ, array(1,2))}}style="width: 25%;"{{/if}}>
              <table>
                {{foreach from=$aChamps item=champ}}
                  {{assign var="donnees" value=$unites.$champ}}
                  <tr>
                    <th class="text" style="font-weight: normal;">{{$donnees.nom}}</th>
                    <td class="text" style="font-weight: bold;">
                      {{if $champ=="tca"}}
                        {{$dossier_anesth->tca_temoin}} s / {{$dossier_anesth->tca}}
                      {{elseif $champ=="tsivy"}}
                        {{$dossier_anesth->tsivy|date_format:"%Mm%Ss"}}
                      {{elseif $champ=="ecbu"}}
                        {{tr}}CConsultAnesth.ecbu.{{$dossier_anesth->ecbu}}{{/tr}}
                      {{elseif $champ == "date_analyse"}}
                        {{mb_value object=$dossier_anesth field=date_analyse}}
                      {{else}}
                        {{mb_value object=$dossier_anesth field=$champ}}
                      {{/if}}
                      {{$donnees.unit}}
                    </td>
                  </tr>
                {{/foreach}}
              </table>
            </td>
          {{/foreach}}
        </tr>
      </table>
    </td>
  </tr>
  {{if $dossier_anesth->result_ecg || $dossier_anesth->result_rp || ($dossier_anesth->result_autre && $app->user_prefs.viewAutreResult)}}
    <tr>
      <td style="word-break: break-all">
        <table style="width: 100%">
          <tr>
            <td style="width: {{if $app->user_prefs.viewAutreResult}}33%{{else}}50%{{/if}}">
              {{if $dossier_anesth->result_ecg}}
                <strong>{{mb_label object=$dossier_anesth field="result_ecg"}}</strong>
                <br/>
                {{mb_value object=$dossier_anesth field="result_ecg"}}
              {{/if}}
            </td>
            <td>
              {{if $dossier_anesth->result_rp}}
                <strong>{{mb_label object=$dossier_anesth field="result_rp"}}</strong>
                <br/>
                {{mb_value object=$dossier_anesth field="result_rp"}}
              {{/if}}
            </td>
            {{if $app->user_prefs.viewAutreResult}}
              <td>
                {{if $dossier_anesth->result_autre}}
                  <strong>{{mb_label object=$dossier_anesth field="result_autre"}}</strong>
                  <br/>
                  {{mb_value object=$dossier_anesth field="result_autre"}}
                {{/if}}
              </td>
            {{/if}}
          </tr>
        </table>
      </td>
    </tr>
  {{/if}}
  <tr>
    <td style="word-break: break-all">
      <table style="width: 100%">
        {{foreach from=$consult->_types_examen key=curr_type item=list_exams}}
          {{if $list_exams|@count}}
            <tr>
              <th>
                {{tr}}CConsultation-back-examcomp{{/tr}} : {{tr}}CExamComp.realisation.{{$curr_type}}{{/tr}}
              </th>
              <td>
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
      </table>

      <table style="width: 100%">
        {{if $consult->_ref_exampossum->_id}}
          <tr>
            <th>{{tr}}CExamPossum{{/tr}}</th>
            <td>
              {{tr}}CConsult-Morbidity{{/tr}} : {{mb_value object=$consult->_ref_exampossum field=_morbidite}}%<br/>
              {{tr}}CConsult-Mortality{{/tr}} : {{mb_value object=$consult->_ref_exampossum field=_mortalite}}%
            </td>
          </tr>
        {{/if}}

        {{if $consult->_ref_examnyha->_id}}
          <tr>
            <th>{{tr}}CConsult-NYHA Classification{{/tr}}</th>
            <td>{{mb_value object=$consult->_ref_examnyha field=_classeNyha}}</td>
          </tr>
        {{/if}}
      </table>

      <table style="width: 100%; padding-bottom: 10px;">
        <tr>
          <th class="category">
            Liste des Documents Edités
          </th>
        </tr>
        <tr>
          <td>
            <ul>
              {{foreach from=$dossier_anesth->_ref_documents item=currDoc}}
              <li>{{$currDoc->nom}}<br/>
                {{foreachelse}}
                Aucun Document de consultation préanesthésique
                {{/foreach}}
                {{foreach from=$consult->_ref_documents item=currDoc}}
              <li>{{$currDoc->nom}}<br/>
                {{/foreach}}
            </ul>
          </td>
        </tr>
      </table>

      <table style="width: 100%; padding-bottom: 10px;">
        <tr>
          <th class="category">{{mb_label object=$dossier_anesth field=premedication}}</th>
        </tr>

        {{if $dossier_anesth->premedication}}
          <tr>
            <td>
              {{$dossier_anesth->premedication|nl2br}}
            </td>
          </tr>
        {{/if}}
        <tr>
          <td>
            <ul>
              {{foreach from=$lines item=_line}}
                {{assign var=prescription value=$_line->_ref_prescription}}
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
      </table>

      {{if $lines_per_op|@count}}
        <table style="width: 100%; padding-bottom: 10px;">
          <tr>
            <th class="category">{{tr}}CPrescription._chapitres.perop{{/tr}}</th>
          </tr>
          <tr>
            <td>
              <ul>
                {{foreach from=$lines_per_op item=_line_per_op}}
                  {{assign var=prescription value=$_line_per_op->_ref_prescription}}
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
        </table>
      {{/if}}

      {{if $dossier_anesth->prepa_preop}}
        <table style="width: 100%">
          <tr>
            <th class="category">{{mb_label object=$dossier_anesth field=prepa_preop}}</th>
          </tr>
          <tr>
            <td>{{$dossier_anesth->prepa_preop|nl2br}}</td>
          </tr>
          <tr>
            <td>
              {{tr}}CConsultAnesth-accord_patient_debout_aller{{/tr}} :
              {{if $dossier_anesth->accord_patient_debout_aller}}
                {{tr}}common-Yes{{/tr}}
              {{else}}
                {{tr}}common-No{{/tr}}
              {{/if}}
            </td>
          </tr>
        </table>
      {{/if}}

      {{if $dossier_medical->_ext_codes_cim}}
        <table style="width: 100%">
          <tr>
            <th class="category">Diagnostics PMSI du patient</th>
          </tr>
          <tr>
            <td>
              <ul>
                {{foreach from=$dossier_medical->_ext_codes_cim item=curr_code}}
                  <li>
                    {{$curr_code->code}}: {{$curr_code->libelle}}
                  </li>
                  {{foreachelse}}
                  <li>Pas de diagnostic</li>
                {{/foreach}}
              </ul>
            </td>
          </tr>
        </table>
      {{/if}}
    </td>
  </tr>

  {{if "dPcabinet CConsultAnesth show_facteurs_risque"|gconf}}
    <tr>
      <td style="word-break: break-all">
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
      <th class="category">Visite préanesthésique
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
        <td style="word-break: break-all">
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
        <td style="word-break: break-all">
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

{{if !@$offline}}
<table class="main">
  <tr>
    <td>
      {{/if}}
