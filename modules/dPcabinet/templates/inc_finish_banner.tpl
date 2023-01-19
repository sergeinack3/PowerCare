{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=current_m value=""}}
{{mb_default var=can_change_prat value=true}}

{{mb_script module=urgences script=contraintes_rpu}}
{{mb_script module=files script=file}}

<script>
  function checkConsult(dossier_anesth_id) {
    new Url("cabinet", "ajax_check_consult_anesth")
      .addParam("consult_id", "{{$consult->_id}}")
      .addParam("dossier_anesth_id", dossier_anesth_id)
      .requestModal();
  }

  function submitConsultWithChrono(chrono) {
    var oForm = getForm("editFrmFinish");
    oForm.chrono.value = chrono;
    return onSubmitFormAjax(oForm, reloadFinishBanner);
  }

  function reloadFinishBanner() {
    new Url("cabinet", "httpreq_vw_finish_banner")
      .addParam("selConsult", document.editFrmFinish.consultation_id.value)
      .addParam("_is_anesth", "{{$_is_anesth}}")
      .requestUpdate('finishBanner');
  }

  function printConsult() {
    new Url("cabinet", "print_consult")
      .addParam("consult_id", "{{$consult->_id}}")
      .popup(700, 550, "Consultation");
  }

  function changePratPec(prat_id, old_prat_id) {
    if (confirm('Etes-vous sur de vouloir changer le praticien de la consultation ?')) {
      var oForm = getForm("editPratPec");
      $V(oForm.prat_id, prat_id);
      oForm.submit();
    }
    else {
      $V(getForm("editFrmFinish").prat_id, old_prat_id, false);
    }
  }

  function reloadAtcd() {
    new Url('patients', 'httpreq_vw_antecedent_allergie')
      .addParam('consult_id', "{{$consult->_id}}")
      .requestUpdate('atcd_allergies', {insertion: function(element, content) {
      element.innerHTML = content;
    } });
  }
</script>

<!-- Formulaire de changement de praticien pour la pec -->
<form name="editPratPec" method="post">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="current_m" value="{{$current_m}}" />
  <input type="hidden" name="dosql" value="do_change_prat_pec" />
  {{mb_key object=$consult}}
  <input type="hidden" name="prat_id" />
  <input type="hidden" name="dialog" value="{{$dialog}}" />
</form>

<form class="watch" name="editFrmFinish" action="?m={{$m}}" method="post" onsubmit="return checkForm(this);">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="dosql" value="do_consultation_aed" />
  {{mb_key   object=$consult}}
  {{mb_field object=$consult field="chrono" hidden=1}}
  {{if $consult_anesth && $consult_anesth->_id}}
    <input type="hidden" name="_consult_anesth_id" value="{{$consult_anesth->_id}}" />
  {{/if}}

  <table class="form me-margin-bottom-0 me-no-border-radius-bottom me-no-border-bottom me-finish-banner me-margin-top-0 me-patient-banner">
    <tr>
      <th class="title me-valign-top" style="border: none; width: 90px; text-align: left;">
        {{assign var=patient value=$consult->_ref_patient}}
        {{assign var=sejour value=$consult->_ref_sejour}}
        {{assign var=sejour_id value=$sejour->_id}}
        <span style="float:left">
          {{if $can_change_prat}}
            <button type="button" class="hslip notext me-tertiary" onclick="ListConsults.toggle();">
              {{tr}}Show_or_hide_left_column{{/tr}}
            </button><br/>
          {{/if}}
          {{mb_include module=system template=inc_object_notes object=$patient}}
          {{mb_include module=dPpatients template=inc_view_ins_patient patient=$patient}}
        </span>
        <a class="me-margin-right-8" href="?m=patients&tab=vw_full_patients&patient_id={{$patient->_id}}">
          {{mb_include module=patients template=inc_vw_photo_identite patient=$patient size=52}}
        </a>
      </th>
      <th class="title text" style="border: none;">
        <div style="float:right;width: 180px;" class="me-width-auto">
          {{mb_include module=system template=inc_object_idsante400 object=$consult}}
          {{mb_include module=system template=inc_object_history object=$consult}}
          {{if $can->edit}}
            <a href="#edit-{{$patient->_guid}}" style="float: right;"
               onclick="Patient.editModal(
                 '{{$patient->_id}}'{{if $consult->_id}},
                 null,
                 null,
                 function(){
                   Consultation.edit('{{$consult->_id}}')
                 }{{/if}}
                 )">
              {{me_img_title src="edit.png" icon="edit" alt="modifier"}}
                {{tr}}CPatient-title-modify{{/tr}}
              {{/me_img_title}}
            </a>
          {{/if}}

          <div style="clear: both;float:right;margin-right: 0;">
            {{if $consult_anesth && $consult_anesth->_id}}
              <button class="print me-tertiary" type="button" onclick="printFiche()">
                Fiche
              </button>
            {{/if}}
            <button id="button_documents" class="print me-tertiary" type="button" onclick="printAllDocs()">
              {{tr}}CConsultation-part-Document|pl{{/tr}} (<span>{{$consult->_nb_files_docs}}</span>)
            </button>

            {{if $consult_anesth && $consult_anesth->_id}}
              <button class="pdf notext me-tertiary" type="button" id="print_cs_anesth_pdf"
                      onclick="pdfConsultAnesth('{{$consult_anesth->_id}}')" title="{{tr}}Print_cs_anesth_pdf{{/tr}}">
              </button>
            {{else}}
              {{if $sejour && $sejour->_id}}
                <br />
                <button class="print me-tertiary" type="button" onclick="printConsult();">
                  {{tr}}CConsultation{{/tr}}
                </button><br/>
                <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">{{$sejour->_shortview}} </span>
              {{/if}}
            {{/if}}
          </div>

          {{if "maternite"|module_active && $modules.maternite->_can->read &&
                (!$_is_anesth || $consult_anesth && $consult_anesth->_id)}}
            <div style="float: right;">
              {{mb_include module=maternite template=inc_input_grossesse object=$consult submit=1 large_icon=1}}
            </div>
          {{/if}}
        </div>

        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">{{$patient}}</span>

        {{mb_ternary var=covid_diag test=$sejour->_id value=$sejour->_covid_diag other=$consult->_covid_diag}}

        {{if $covid_diag}}
          <span class="texticon texticon-stup" title="{{$covid_diag->libelle}}" style="font-size: 10pt;">
            {{$covid_diag->libelle_court}}
          </span>
        {{/if}}

        {{mb_include module=patients template=inc_icon_bmr_bhre}}

        <span style="display:inline-block;max-height: 15px;">
          {{mb_include module=patients template=vw_antecedents_allergies}}
        </span>

        {{if "maternite"|module_active && !$consult->grossesse_id}}
          {{if $patient->_ref_last_grossesse && $patient->_ref_last_grossesse->_id && !$patient->_ref_last_grossesse->datetime_cloture}}
            <span class="texticon texticon-grossesse" onmouseover="ObjectTooltip.createEx(this, '{{$patient->_ref_last_grossesse->_guid}}')">
              {{tr}}CGrossesse-in_progress{{/tr}}
            </span>
          {{/if}}
        {{/if}}

        - {{$patient->_age}} -
        {{if $can_change_prat}}
          <select name="prat_id" class="ref notNull" onchange="changePratPec($V(this), '{{$consult->_ref_chir->_id}}');"
                  style="width: 16em;" title="Changer le praticien">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{mb_include module=mediusers template=inc_options_mediuser list=$listPrats selected=$consult->_ref_chir->_id}}
          </select>
        {{else}}
            {{$consult->_ref_chir}}
        {{/if}}

        <br />
        {{if "maternite"|module_active && $consult->_ref_grossesse->_id && $consult->_ref_grossesse->active}}
          <span>
            {{mb_value object=$consult field=_sa}} <span title="{{tr}}CGrossesse-_semaine_grossesse-desc{{/tr}}">{{tr}}CGrossesse-_semaine_grossesse-court{{/tr}}</span>
            + {{mb_value object=$consult->_ref_grossesse field=_reste_semaine_grossesse}} j
            {{tr var1=$consult->_ref_grossesse->terme_prevu|date_format:$conf.date}}CGrossesse-Expected term the %s{{/tr}}
          </span>
          <br />
          {{if $consult->_ref_grossesse->_ref_dossier_perinat}}
            {{mb_include module=cabinet template=inc_conduite_a_tenir dossier=$consult->_ref_grossesse->_ref_dossier_perinat}}
          {{/if}}
        {{/if}}
        {{if $consult->teleconsultation && 'teleconsultation'|module_active && $app->_ref_user->_id == $consult->_ref_praticien->_id}}
            {{assign var=consult_id value=$consult->_id}}
            {{mb_include module=teleconsultation template=inc_shortcut_teleconsultation prat_id=$consult->_ref_praticien->_id _consult=$consult onclick="Teleconsultation.checkRoomActive('$consult_id');" custom_style=false}}
        {{/if}}
        {{tr}}CConsultation{{/tr}}
        (Etat : {{$consult->_etat}}
        {{if $consult->annule && $consult->motif_annulation}}
          <span style="color: #FF3100;">
            ({{tr}}CConsultation.motif_annulation.{{$consult->motif_annulation}}{{/tr}})
          </span>
        {{/if}}
        {{if $consult->chrono <= $consult|const:'EN_COURS'}}
          /
          {{if $consult_anesth && $consult_anesth->_id}}
            <button class="tick" type="button" onclick="checkConsult('{{$consult_anesth->_id}}');">
          {{else}}
            <button class="tick" type="button"
                    onclick="{{if $sejour && $sejour->_ref_rpu && $sejour->_ref_rpu->_id && $consult->_ref_chir->isUrgentiste()}}ContraintesRPU.checkObligatory('{{$sejour->_ref_rpu->_id}}', getForm('editSejour'), function() {submitAll(); submitConsultWithChrono({{$consult|const:'TERMINE'}});});{{else}}submitAll(); submitConsultWithChrono({{$consult|const:'TERMINE'}});{{/if}}">
          {{/if}}
          {{tr}}CConsultation-action-Finish{{/tr}}
          </button>
        {{elseif $consult_anesth && $consult_anesth->_id}}
          <button class="search" type="button" onclick="checkConsult('{{$consult_anesth->_id}}');">
            {{if !$conf.dPpatients.CAntecedent.mandatory_types}}IPAQSS{{else}}Prérequis{{/if}}
          </button>
        {{/if}})

        {{if isset($rpu|smarty:nodefaults) && $rpu && $rpu->_id}}
          {{assign var=color value=""}}
          {{if $rpu->ccmu}}
            {{assign var=color value="dPurgences Display color_ccmu_`$rpu->ccmu`"|gconf}}
          {{/if}}

          <span>
          - Arrivée : {{mb_value object=$rpu->_ref_sejour field=entree date=$dnow}}
            <span style="color: #{{$color}}">({{mb_value object=$rpu field=ccmu}})</span>
          </span>

          {{if $sejour->UHCD}}
            <span class="texticon" style="color: #800; font-weight: bold;">UHCD</span>
          {{/if}}
        {{/if}}

        <span style="font-size: 14pt;">
          <span id="atcd_majeur">
              {{mb_include module=patients template=inc_atcd_majeur}}
          </span>
        </span>

        {{if $consult->_ref_sejour && $consult->_ref_sejour->_ref_prescription_sejour}}
          <div style="font-size: 14pt;">
            {{mb_include module=prescription template=vw_line_important lines=$consult->_ref_sejour->_ref_prescription_sejour->_ref_lines_important}}
          </div>
        {{/if}}
      </th>
    </tr>
  </table>
</form>

<table class="tbl me-no-hover me-margin-top-0 me-no-border-radius-top me-info-patient-table">
  {{mb_include module=soins template=inc_infos_patients_soins add_class=1}}
</table>
