{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=view_prescription value=1}}
{{mb_script module=cabinet     script=exam_comp}}
{{if "planSoins"|module_active}}
  {{mb_script module=planSoins script=plan_soins}}
{{/if}}
{{mb_script module=compteRendu script=document}}

<script>
  function calculClairance() {
    var oFormExam  = document.forms["editExamCompFrm"];
    var oFormConst = document.forms["edit-constantes-medicales"];

    var poids      = parseFloat($V(oFormConst._last_poids));
    var creatinine = parseFloat($V(oFormExam.creatinine));

    if({{if $patient->_annees && $patient->_annees!="??" && $patient->_annees>=18 && $patient->_annees<=110}}1{{else}}0{{/if}} &&
      poids && !isNaN(poids) && poids >= 35 && poids <= 120 &&
      creatinine && !isNaN(creatinine) && creatinine >= 6 && creatinine <= 70) {
      $V(oFormExam._clairance, Math.round(({{if $patient->sexe=="m"}}1.04{{else}}0.85{{/if}}*poids*(140-{{if $patient->_annees!="??"}}{{$patient->_annees}}{{else}}0{{/if}})/(creatinine*7.2))*100)/100);
    }
    else {
      $V(oFormExam._clairance, "");
    }
  }

  function calculPSA() {
    var oFormExam     = getForm("editExamCompFrm");
    var oFormConst    = getForm("edit-constantes-medicales");

    var vst      = parseFloat($V(oFormConst._last__vst));
    var ht       = parseFloat($V(oFormExam.ht));
    var ht_final = parseFloat($V(oFormExam.ht_final));

    if (vst && !isNaN(vst) &&
      ht && !isNaN(ht) && ht > 0 &&
      ht_final && !isNaN(ht_final) && ht_final > 0) {
      $V(oFormExam._psa, Math.round(vst * (ht - ht_final))/100);
    }
    else {
      $V(oFormExam._psa, "");
    }
  }

  function saveDossierMedical() {
    var formDossier = getForm("dossier_medical_patient");
    var formExam    = getForm("editExamCompFrm");
    $V(formDossier.groupe_sanguin, formExam.groupe_sanguin.value);
    $V(formDossier.groupe_ok, formExam.groupe_ok.value);
    $V(formDossier.rhesus, formExam.rhesus.value);
    $V(formDossier.phenotype, formExam.phenotype.value);
    return onSubmitFormAjax(formDossier);
  }

  function callbackExamComp() {
    if (!window.tabsConsultAnesth) {
      return;
    }
    var count = 0;
    count += $("listExamComp").select("button.trash").length;
    var form = getForm("editExamCompFrm");
    if ($V(form.result_ecg)) {
      count ++;
    }
    if ($V(form.result_rp)) {
      count ++;
    }
    Control.Tabs.setTabCount("ExamsComp", count);
  }

  Main.add(function() {
    ExamComp.refresh('{{$consult->_id}}');
  });
</script>

<table class="form me-no-box-shadow" style="width: 100%">
  <tr>
    <td class="text me-padding-0 me-valign-top">
      <form name="addExamCompFrm" method="post">
      <input type="hidden" name="m" value="cabinet" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="dosql" value="do_examcomp_aed" />
      {{mb_key object=$consult}}

      <fieldset class="not-printable me-no-box-shadow">
        <legend>{{tr}}CExamComp{{/tr}}</legend>
          <table class="layout main">
            <tr>
              <td>
                {{mb_field object=$examComp field=realisation}}
              </td>
            </tr>
            <tr>
              <td>
                <input type="hidden" name="_hidden_examen" value="" />
                {{mb_field object=$examComp field="examen" rows="4" form="addExamCompFrm"
                  aidesaisie="validateOnBlur: 0"}}
              </td>
            </tr>
            <tr>
              <td class="button" colspan="3">
                <button class="add me-primary" type="button" onclick="if ($V(this.form.examen)) { ExamComp.submit(this.form); }">
                  {{tr}}CExamComp-title-create{{/tr}}
                </button>
              </td>
            </tr>
          </table>
      </fieldset>
      </form>
      {{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
      <form name="dossier_medical_patient" method="post" onsubmit="return onSubmitFormAjax(this);">
        {{mb_key   object=$dossier_medical}}
        <input type="hidden" name="m"     value="patients" />
        <input type="hidden" name="del"     value="0" />
        <input type="hidden" name="dosql"     value="do_dossierMedical_aed" />
        <input type="hidden" name="object_id"     value="{{$patient->_id}}" />
        <input type="hidden" name="object_class"  value="{{$patient->_class}}" />
        <input type="hidden" name="groupe_sanguin"  value="{{$dossier_medical->groupe_sanguin}}" />
        <input type="hidden" name="rhesus"          value="{{$dossier_medical->rhesus}}" />
        <input type="hidden" name="groupe_ok"       value="{{$dossier_medical->groupe_ok}}" />
        <input type="hidden" name="phenotype"       value="{{$dossier_medical->phenotype}}" />
      </form>

      <form name="editExamCompFrm" method="post">
      <input type="hidden" name="m" value="cabinet" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="dosql" value="do_consult_anesth_aed" />
      <input type="hidden" name="callback" value="callbackExamComp" />
      {{mb_key object=$consult_anesth}}
      {{if $app->user_prefs.displayResultsConsult}}
        <fieldset>
          <legend>Résultats d'analyse</legend>
          <table class="layout main">
            {{if "moebius"|module_active && $app->user_prefs.ViewConsultMoebius}}
              <tr>
                <th>{{mb_label object=$consult_anesth field="date_analyse"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="date_analyse" form="editExamCompFrm" register="true" onchange="submitForm(this.form)"}}
                </td>
                <th>{{mb_label object=$dossier_medical field="groupe_sanguin"}}</th>
                <td>
                  {{mb_field object=$dossier_medical field="groupe_sanguin" tabindex="101" onchange="saveDossierMedical()"}}
                  /
                  {{mb_field object=$dossier_medical field="rhesus" tabindex="102" onchange="saveDossierMedical();"}}
                </td>
                <th>{{mb_label object=$dossier_medical field="phenotype"}}</th>
                <td>
                  {{mb_field object=$dossier_medical field="phenotype" size="9" onchange="saveDossierMedical()"}}
                </td>

              </tr>

              <tr>
                <th>{{mb_label object=$dossier_medical field="groupe_ok"}}</th>
                <td>
                  {{mb_field object=$dossier_medical field="groupe_ok" typeEnum="checkbox" tabindex="103" onchange="saveDossierMedical();"}}
                </td>
                <th>{{mb_label object=$consult_anesth field="fibrinogene"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="fibrinogene" tabindex="109" size="4" onchange="submitForm(this.form)"}}
                  g/l
                </td>
              </tr>

              <tr>
                <th>{{mb_label object=$consult_anesth field="rai"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="rai" tabindex="103" onchange="submitForm(this.form)"}}
                </td>
                <th>{{mb_label object=$consult_anesth field="na"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="na" tabindex="109" size="4" onchange="submitForm(this.form)"}}
                  mmol/l
                </td>
              </tr>

              <tr>
                <th>{{mb_label object=$consult_anesth field="k"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="k" tabindex="110" size="4" onchange="submitForm(this.form)"}}
                  mmol/l
                </td>
                <th>{{mb_label object=$consult_anesth field="tp"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="tp" tabindex="111" size="4" onchange="submitForm(this.form)"}}
                  %
                </td>
              </tr>

              <tr>
                <th>{{mb_label object=$consult_anesth field="plaquettes"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="plaquettes" tabindex="107" size="6" onchange="submitForm(this.form)"}} (x1000) /mm3
                </td>
                <th>{{mb_label object=$consult_anesth field="tca" defaultFor="tca_temoin"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="tca_temoin" tabindex="112" maxlength="2" size="2" onchange="submitForm(this.form)"}}
                  s /
                  {{mb_field object=$consult_anesth field="tca" tabindex="113" maxlength="2" size="2" onchange="submitForm(this.form)"}}
                  s
                </td>
              </tr>

              <tr>
                <th>{{mb_label object=$consult_anesth field="chlore"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="chlore" tabindex="118" size="4" onchange="submitForm(this.form)"}}
                  mmol/l
                </td>
                <th>{{mb_label object=$consult_anesth field="ecbu"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="ecbu" tabindex="116" onchange="submitForm(this.form)"}}
                </td>
              </tr>

              <tr>
                <th>{{mb_label object=$consult_anesth field="inr"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="inr" tabindex="120" size="4" onchange="submitForm(this.form)"}}
                </td>
                <th></th>
                <td></td>
              </tr>
            {{else}}
              <tr>
                <th>{{mb_label object=$consult_anesth field="date_analyse"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="date_analyse" form="editExamCompFrm" register="true" onchange="submitForm(this.form)"}}
                </td>
                <th>{{mb_label object=$consult_anesth field="creatinine"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="creatinine" tabindex="108" size="4" onchange="calculClairance();submitForm(this.form);"}}
                  mg/l
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$dossier_medical field="groupe_sanguin"}}</th>
                <td>
                  {{mb_field object=$dossier_medical field="groupe_sanguin" tabindex="101" onchange="saveDossierMedical()"}}
                  /
                  {{mb_field object=$dossier_medical field="rhesus" tabindex="102" onchange="saveDossierMedical();"}}
                </td>
                <th>{{mb_label object=$consult_anesth field="_clairance"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="_clairance"  size="4" readonly="readonly"}}
                  ml/min
                  <span onmouseover="ObjectTooltip.createDOM(this, 'info_calcul_clairance');" >
                     <i class="fa fa-lg fa-info-circle" style="color: #2946c9;" title=""></i>
                  </span>
                  <div id="info_calcul_clairance" style="display: none;">
                    {{tr}}CConsultAnesth-Cockroft and Gault Formula-desc{{/tr}}
                  </div>
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$dossier_medical field="phenotype"}}</th>
                <td>
                  {{mb_field object=$dossier_medical field="phenotype" size="9" onchange="saveDossierMedical();"}}
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$dossier_medical field="groupe_ok"}}</th>
                <td>
                  {{mb_field object=$dossier_medical field="groupe_ok" typeEnum="checkbox" tabindex="103" onchange="saveDossierMedical();"}}
                </td>
                <th>{{mb_label object=$consult_anesth field="fibrinogene"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="fibrinogene" tabindex="109" size="4" onchange="submitForm(this.form)"}}
                  g/l
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$consult_anesth field="rai"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="rai" tabindex="103" onchange="submitForm(this.form)"}}
                </td>
                <th>{{mb_label object=$consult_anesth field="na"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="na" tabindex="109" size="4" onchange="submitForm(this.form)"}}
                  mmol/l
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$consult_anesth field="hb"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="hb" tabindex="104" size="4" onchange="submitForm(this.form)"}}
                  g/dl
                </td>
                <th>{{mb_label object=$consult_anesth field="k"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="k" tabindex="110" size="4" onchange="submitForm(this.form)"}}
                  mmol/l
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$consult_anesth field="ht"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="ht" tabindex="105" size="4" onchange="calculPSA();submitForm(this.form);"}}
                  %
                </td>
                <th>{{mb_label object=$consult_anesth field="tp"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="tp" tabindex="111" size="4" onchange="submitForm(this.form)"}}
                  %
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$consult_anesth field="ht_final"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="ht_final" tabindex="106" size="4" onchange="calculPSA();submitForm(this.form);"}}
                  %
                </td>
                <th>{{mb_label object=$consult_anesth field="tca" defaultFor="tca_temoin"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="tca_temoin" tabindex="112" maxlength="2" size="2" onchange="submitForm(this.form)"}}
                  s /
                  {{mb_field object=$consult_anesth field="tca" tabindex="113" maxlength="2" size="2" onchange="submitForm(this.form)"}}
                   s
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$consult_anesth field="_psa"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="_psa"  size="4" readonly="readonly"}}
                  ml/GR
                </td>
                <th>{{mb_label object=$consult_anesth field="tsivy" defaultFor="_min_tsivy"}}</th>
                <td>
                  {{html_options tabindex="114" name="_min_tsivy" values=$mins output=$mins selected=$consult_anesth->_min_tsivy onchange="submitForm(this.form)"}}
                  min
                  {{html_options tabindex="115" name="_sec_tsivy" values=$secs output=$secs selected=$consult_anesth->_sec_tsivy onchange="submitForm(this.form)"}}
                  s
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$consult_anesth field="plaquettes"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="plaquettes" tabindex="107" size="6" onchange="submitForm(this.form)"}} (x1000) /mm3
                </td>
                <th>{{mb_label object=$consult_anesth field="ecbu"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="ecbu" tabindex="116" onchange="submitForm(this.form)"}}
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$consult_anesth field="protides"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="protides" tabindex="117" size="4" onchange="submitForm(this.form)"}}
                  g/l
                </td>
                <th>{{mb_label object=$consult_anesth field="chlore"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="chlore" tabindex="118" size="4" onchange="submitForm(this.form)"}}
                  mmol/l
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$consult_anesth field="hepatite_b"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="hepatite_b" tabindex="119" onchange="submitForm(this.form)"}}
                </td>
                <th>{{mb_label object=$consult_anesth field="inr"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="inr" tabindex="120" size="4" onchange="submitForm(this.form)"}}
                </td>
              </tr>
              <tr>
                <th>{{mb_label object=$consult_anesth field="hepatite_c"}}</th>
                <td>
                  {{mb_field object=$consult_anesth field="hepatite_c" tabindex="121" onchange="submitForm(this.form)"}}
                </td>
                <th></th>
                <td></td>
              </tr>
              <tr>
                <th>{{mb_label object=$consult_anesth field="result_com"}}</th>
                <td colspan="3">
                  {{mb_field object=$consult_anesth field="result_com" tabindex="123" onchange="submitForm(this.form)" form="editExamCompFrm"}}
                </td>
              </tr>
            {{/if}}
          </table>
        </fieldset>
      {{/if}}
      <table class="layout main">
        <tr>
          <td class="halfPane">
            <fieldset class="me-no-box-shadow">
              <legend>{{mb_label object=$consult_anesth field="result_ecg"}}</legend>
              {{mb_field object=$consult_anesth field="result_ecg" rows="4" onchange="submitForm(this.form)" form="editExamCompFrm"
                  aidesaisie="validateOnBlur: 0"}}
            </fieldset>
          </td>
          <td class="halfPane">
            <fieldset class="me-no-box-shadow">
              <legend>{{mb_label object=$consult_anesth field="result_rp"}}</legend>
              {{mb_field object=$consult_anesth field="result_rp" rows="4" onchange="submitForm(this.form)" form="editExamCompFrm"
                  aidesaisie="validateOnBlur: 0"}}
            </fieldset>
          </td>
        </tr>
        {{if $app->user_prefs.viewAutreResult}}
          <tr>
            <td class="halfPane" colspan="2">
              <fieldset class="me-no-box-shadow">
                <legend>{{mb_label object=$consult_anesth field="result_autre"}}</legend>
                {{mb_field object=$consult_anesth field="result_autre" rows="4" onchange="submitForm(this.form)" form="editExamCompFrm"
                aidesaisie="validateOnBlur: 0"}}
              </fieldset>
            </td>
          </tr>
        {{/if}}
      </table>
      </form>
    </td>
    <td class="text me-padding-0 me-valign-top">
      <div id="listExamComp"></div>

      {{if $isPrescriptionInstalled && "dPcabinet CPrescription view_prescription"|gconf && $view_prescription}}
        <button class="tick not-printable" onclick="tabsConsultAnesth.setActiveTab('prescription_sejour');
          tabsConsultAnesth.activeLink.up('li').onmousedown()">Accéder à la prescription</button>
        {{assign var=sejour value=$consult->_ref_sejour}}
        {{if $consult_anesth->_ref_sejour->_id}}
          {{assign var=sejour value=$consult_anesth->_ref_sejour}}
        {{/if}}
        {{assign var=prescription value=$sejour->_ref_prescription_sejour}}
        {{if "planSoins"|module_active && $prescription && $prescription->_id}}
          <br />
          <button class="print not-printable me-tertiary me-dark" onclick="PlanSoins.printBons('{{$prescription->_id}}')">Bons</button>
        {{/if}}
      {{/if}}

      {{if $app->user_prefs.displayDocsConsult}}
        <table class="form">
          <!-- Documents ExamComp -->
          <tr>
            <th class="category">Documents</th>
          </tr>
          <tr>
            <td id="documents-exam">
              {{mb_ternary var=object test=$consult->_is_anesth value=$consult->_ref_consult_anesth other=$consult}}
              <!-- Documents -->
              <script>
                 Document.register('{{$object->_id}}','{{$object->_class}}','{{$consult->_praticien_id}}','documents-exam');
              </script>
            </td>
          </tr>
        </table>
      {{/if}}
    </td>
  </tr>
</table>
