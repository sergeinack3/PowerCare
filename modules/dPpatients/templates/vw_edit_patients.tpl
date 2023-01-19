{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients    script=autocomplete}}
{{mb_script module=patients    script=siblings_checker}}
{{mb_script module=patients    script=patient}}
{{mb_script module=patients    script=source_identite}}
{{mb_script module=compteRendu script=document}}
{{mb_script module=compteRendu script=modele_selector}}
{{mb_script module=files       script=files}}
{{mb_script module=files       script=file}}
{{mb_script module=files       script=webcam_image}}
{{mb_script module=files       script=id_interpreter}}

{{if $patient->_id && "dPpatients sharing patient_data_sharing"|gconf}}
  {{mb_script module=patients script=patient_group}}
{{/if}}

{{if $patient->_id}}
  {{mb_script module="patients"  script="correspondant"}}
{{/if}}

{{assign var=modFSE value="fse"|module_active}}
{{assign var=patient_id value=$patient->_id}}


{{if !$ajax}}
  {{if $app->user_prefs.LogicielLectureVitale == 'vitaleVision'}}
    {{mb_include template=inc_vitalevision}}
    <script>
      var lireVitale = VitaleVision.read;
    </script>
  {{elseif $app->user_prefs.LogicielLectureVitale == 'none'}}
    <script>
      var urlFSE = new Url();
      urlFSE.addParam("m", "dPpatients");
      urlFSE.addParam("{{$actionType}}", "vw_edit_patients");
      urlFSE.addParam("modal", "{{$modal}}");
      urlFSE.addParam("useVitale", 1);
      window.urlFSE = urlFSE;
    </script>
  {{/if}}
{{/if}}

<script>
  Main.add(function () {
    var form = getForm('editFrm');

    if ($V(form.prenom) && !$V(form.prenoms) && ['PROV', 'VIDE'].includes('{{$patient->status}}')) {
      $V(form.prenoms, $V(form.prenom));
    }

    initPaysField("editFrm", "_pays_naissance_insee", "profession");
    initPaysField("editFrm", "pays", "tel");

    InseeFields.initCPVille("editFrm", "assure_cp", "assure_ville", null, 'pays', "assure_pays_insee");
    InseeFields.initCPVille("editFrm", "assure_cp_naissance", "assure_lieu_naissance", null, 'pays', "_assure_pays_naissance_insee");

    InseeFields.initCSP("editFrm", "_csp_view");
    InseeFields.initCodeInsee("editFrm", "_code_insee");

    initPaysField("editFrm", "_assure_pays_naissance_insee", "assure_profession");
    initPaysField("editFrm", "assure_pays", "assure_tel");

    Patient.tabs = Control.Tabs.create('tab-patient');

    Patient.adult_age = {{"dPpatients CPatient adult_age"|gconf}};
    Patient.anonymous_sexe = '{{"dPpatients CPatient anonymous_sexe"|gconf}}';
    Patient.anonymous_naissance = '{{"dPpatients CPatient anonymous_naissance"|gconf|date_format:$conf.date}}';

    $('me-edit-patient-actions').addClassName('displayed');

    {{if $patient->_id}}
    setObject({
      objClass: '{{$patient->_class}}',
      keywords: '',
      id:       '{{$patient->_id}}',
      view:     '{{$patient->_view|smarty:nodefaults|JSAttribute}}'
    });
    {{/if}}

    {{if !$modal && $useVitale && $app->user_prefs.LogicielLectureVitale == 'vitaleVision'}}
    lireVitale.delay(1); // 1 second
    {{/if}}

    {{if $useVitale}}
    SiblingsChecker.request(getForm('editFrm'));
    {{/if}}

    SourceIdentite.patient_id = '{{$patient->_id}}';

    Patient.ref_pays = '{{$conf.ref_pays}}';
  });
</script>

<form name="delete-photo-identite-form" method="post">
  <input type="hidden" name="m" value="files" />
  <input type="hidden" name="file_id" value="" />
  <input type="hidden" name="del" value="1" />
  <input type="hidden" name="dosql" value="do_file_aed" />
  <input type="hidden" name="callback" value="reloadCallback">
</form>

<form name="FrmClass" action="?m={{$m}}" method="get" onsubmit="return Patient.reloadListFileEditPatient('load');">
  <input type="hidden" name="selKey" value="" />
  <input type="hidden" name="selClass" value="" />
  <input type="hidden" name="selView" value="" />
  <input type="hidden" name="keywords" value="" />
  <input type="hidden" name="file_id" value="" />
  <input type="hidden" name="typeVue" value="1" />
</form>

{{if $patient->_id && !$modal}}
  <div class="me-text-align-left me-padding-0 me-margin-top-4">
    <a class="button new me-primary" href="?m={{$m}}&{{$actionType}}={{$action}}&dialog={{$dialog}}&patient_id=0">
        {{tr}}CPatient-title-create{{/tr}}
    </a>
  </div>
{{/if}}

<!-- modale Vitale -->
<div id="modal-beneficiaire" style="display:none; text-align:center;">
  <p id="msg-multiple-benef">
    {{tr}}CPatient.card_lot_of_benifits{{/tr}} :
  </p>
  <p id="msg-confirm-benef" style="display: none;">
    {{tr}}CPatient.info_card_replace{{/tr}}
  </p>
  <p id="benef-nom">
    <select id="modal-beneficiaire-select"></select>
    <span></span>
  </p>
  <div>
    <button type="button" class="tick"
            onclick="VitaleVision.modalWindow.close(); {{if $app->user_prefs.update_patient_from_vitale_behavior == 'choice'}}VitaleVision.prepareUpdatePatient($V($('modal-beneficiaire-select')), '{{$patient->_id}}');{{elseif $app->user_prefs.update_patient_from_vitale_behavior == 'always'}}VitaleVision.fillForm(getForm('editFrm'), $V($('modal-beneficiaire-select')), 1);{{else}}VitaleVision.fillForm(getForm('editFrm'), $V($('modal-beneficiaire-select')));{{/if}}">{{tr}}Choose{{/tr}}</button>
    <button type="button" class="cancel" onclick="VitaleVision.modalWindow.close();">{{tr}}Cancel{{/tr}}</button>
  </div>
</div>

<!-- main -->
<table class="main form me-margin-bottom-40">
  <tr>
    {{if $patient->_id}}
      <th class="title modify" colspan="5">
        {{if $app->user_prefs.LogicielLectureVitale == 'vitaleVision'}}
          <button class="search singleclick" type="button" onclick="lireVitale();" style="float: left;">
            {{tr}}CPatient.read_card_vitaleVision{{/tr}}
          </button>
        {{elseif $modFSE && $modFSE->canRead()}}
          {{mb_include module=fse template=inc_button_vitale operation='update' patient_id=$patient->_id}}
        {{/if}}

        {{mb_include module=dPpatients template=inc_view_ins_patient patient=$patient}}
        {{mb_include module=patients template=inc_status_icon float=right}}

        {{mb_include module=system template=inc_object_idsante400 object=$patient}}
        {{mb_include module=system template=inc_object_history    object=$patient}}
        {{tr var1=$patient}}CPatient-modify of %s{{/tr}}
        {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
        {{if $patient->_bind_vitale}}{{tr}}UseVitale{{/tr}}{{/if}}
      </th>
    {{else}}
      <th class="title me-th-new" colspan="5">
        {{if $app->user_prefs.LogicielLectureVitale == 'vitaleVision'}}
          <button class="search singleclick" type="button" onclick="lireVitale();" style="float: left;">
            {{tr}}CPatient.read_card_vitaleVision{{/tr}}
          </button>
        {{elseif $modFSE && $modFSE->canRead() && $app->user_prefs.LogicielLectureVitale == 'none'}}
          {{mb_include module=fse template=inc_button_vitale operation='update'}}
        {{/if}}
        {{tr}}Create{{/tr}}
        {{if $patient->_bind_vitale}}{{tr}}UseVitale{{/tr}}{{/if}}
      </th>
    {{/if}}
  </tr>
  {{mb_ternary var=x test=$patient->medecin_traitant value=1 other=0}}
  {{mb_ternary var=z test=$patient->pharmacie_id value=1 other=0}}
  {{math equation="$x+y+$z" y=$patient->_ref_medecins_correspondants|@count assign=count_correspondants}}
  <tr>
    <td colspan="6" class="me-padding-0">
      <ul id="tab-patient" class="control_tabs me-margin-top-0 me-border-only-bottom me-no-border-radius-top">
        <li><a href="#identite">{{tr}}CPatient-part-patient{{/tr}}</a></li>
        <li><a href="#medecins" {{if !$count_correspondants}}class="empty"{{/if}}>{{tr}}CPatient-part-correspondants-medicaux{{/tr}}
            <small>({{$count_correspondants}})</small>
          </a></li>
        <li><a href="#correspondance"
               {{if !$patient->_ref_correspondants_patient|@count}}class="empty"{{/if}}>{{tr}}CPatient-part-correspondants-patient{{/tr}}
            <small>({{$patient->_ref_correspondants_patient|@count}})</small>
          </a></li>
        {{if $patient->_id}}
          <li><a href="#bmr_bhre" {{if !$patient->_ref_bmr_bhre->_id}}class="empty"{{/if}}>{{tr}}CPatient-part-bmr_bhre{{/tr}}</a></li>
        {{/if}}
        <li><a href="#family_links">{{tr}}CPatient-Family link|pl{{/tr}}</a></li>
        {{if $conf.ref_pays == 1}}
          <li><a href="#assure">{{tr}}CPatient-part-assure-social{{/tr}}</a></li>
        {{/if}}
        <li><a href="#beneficiaire">{{tr}}CPatient-part-beneficiaire-soins{{/tr}}</a></li>
        <li><a href="#listView" {{if $patient->_nb_files_docs == 0}}class="empty"{{/if}}
            {{if $patient->_id}}onmousedown="Patient.loadDocItems('{{$patient->_id}}')"{{/if}}>{{tr}}CPatient-part-documents{{/tr}}
            ({{$patient->_nb_files_docs}})</a></li>
      </ul>

      <form name="editFrm" {{if !$modal}}action="?m={{$m}}"{{/if}} method="post" onsubmit="return Patient.confirmCreation(this)"
            enctype="multipart/form-data">
        {{if $modal}}
          <input type="hidden" name="m" value="patients" />
        {{/if}}
        <input type="hidden" name="dosql" value="do_patients_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="_purge" value="0" />
        <input type="hidden" name="modal" value="{{$modal}}" />
        <input type="hidden" name="callback" value="{{$callback}}" />
        <input type="hidden" name="_reason_state" value="">
        <input type="hidden" name="_doubloon_ids" value="" />
        <input type="hidden" name="_status_no_guess" value="0">
        <input type="hidden" name="_open_corresp" value="0">
        <input type="hidden" name="_bind_vitale" value="{{$patient->_bind_vitale}}"/>
        {{mb_field object=$patient field="_vitale_firstname" hidden=1}}
        {{mb_field object=$patient field="_vitale_birthdate" hidden=1}}
        {{mb_field object=$patient field="_vitale_nir_certifie" hidden=1}}
        {{mb_field object=$patient field="_vitale_lastname" hidden=1}}
        {{mb_field object=$patient field="_vitale_quality" hidden=1}}
        {{mb_field object=$patient field="_vitale_birthrank" hidden=1}}
        {{mb_field object=$patient field=_vitale_nir hidden=1}}
        {{mb_field object=$patient field=_vitale_nir_certifie hidden=1}}
        {{mb_field object=$patient field=_vitale_code_regime hidden=1}}
        {{mb_field object=$patient field=_vitale_code_caisse hidden=1}}
        {{mb_field object=$patient field=_vitale_code_centre hidden=1}}
        {{mb_field object=$patient field=_vitale_code_gestion hidden=1}}
        {{mb_field object=$patient field="status" hidden=1}}
        {{mb_field object=$patient field="source_identite_id" hidden=1}}
        {{mb_field object=$patient field="_date_fin_validite" hidden=1}}
        {{mb_field object=$patient field="_source_nom" hidden=1}}
        {{mb_field object=$patient field="_source_nom_jeune_fille" hidden=1 canNull=true}}
        {{mb_field object=$patient field="_source_prenom" canNull=true onchange="\$V(this.form._source_prenoms, this.value);" hidden=1}}
        {{mb_field object=$patient field="_source_prenom_usuel" hidden=1}}
        {{mb_field object=$patient field="_source_prenoms" hidden=1}}
        {{mb_field object=$patient field="_source_sexe" hidden=1}}
        {{mb_field object=$patient field="_source_civilite" hidden=1}}
        {{mb_field object=$patient field="_source_naissance" hidden=1 canNull=true}}
        {{mb_field object=$patient field="_source_naissance_corrigee" hidden=1}}
        {{mb_field object=$patient field="_source_commune_naissance_insee" hidden=1}}
        {{mb_field object=$patient field="_source_lieu_naissance" hidden=1}}
        {{mb_field object=$patient field="_source_cp_naissance" hidden=1}}
        {{mb_field object=$patient field="_source__pays_naissance_insee" hidden=1}}
        {{mb_field object=$patient field="_source__date_fin_validite" hidden=1}}
        {{mb_field object=$patient field="_source__validate_identity" hidden=1}}
        {{mb_field object=$patient field="_identity_proof_type_id" hidden=1}}
        {{mb_field object=$patient field="_mode_obtention" hidden=1}}
        {{mb_field object=$patient field="_previous_ins" hidden=1}}
        {{mb_field object=$patient field="_oid" hidden=1}}
        {{mb_field object=$patient field="_ins" hidden=1}}
        {{mb_field object=$patient field="_ins_type" hidden=1}}
        {{mb_field object=$patient field="_map_source_form_fields" hidden=1}}
        {{mb_field object=$patient field="_force_manual_source" hidden=1}}
        {{mb_field object=$patient field="_force_new_manual_source" hidden=1}}
        {{mb_field object=$patient field="_copy_file_id" hidden=1}}

        {{mb_key object=$patient}}

        {{if $patient->_bind_vitale}}
          <input type="hidden" name="_bind_vitale" value="1" />
        {{/if}}

        <button type="submit" style="display: none;">&nbsp;</button>

        {{if !$patient->_id}}
          {{mb_field object=$patient field="medecin_traitant" hidden=1}}
        {{/if}}

        {{if $dialog}}
          <input type="hidden" name="dialog" value="{{$dialog}}" />
        {{/if}}

        <div id="identite" class="me-no-border" style="display: none;">
            {{mb_include template=inc_acc/inc_acc_identite_v2}}
        </div>

        <div id="assure" class="me-no-border" style="display: none;">
          {{mb_include template=inc_acc/inc_acc_assure}}
        </div>
        <div id="beneficiaire" class="me-no-border" style="display: none;">
          {{mb_include template=inc_acc/inc_acc_beneficiaire}}
        </div>
      </form>
      <div id="correspondance" class="me-no-border" style="display: none;">
        {{mb_include template=inc_acc/inc_acc_corresp}}
      </div>
      {{if $patient->_id}}
        <div id="bmr_bhre" class="me-no-border" style="display: none;">
          {{mb_include template=inc_acc/inc_acc_bmr_bhre}}
        </div>
      {{/if}}
      <div id="medecins" class="me-no-border" style="display: none;">
        {{mb_include template=inc_acc/inc_acc_medecins}}
      </div>
      <div id="family_links" class="me-no-border" style="display: none;">
        {{mb_include template=inc_acc/inc_acc_familyLinks}}
      </div>
      <div id="listView" style="display: none;" class="text me-no-border">
        <div class="big-info">{{tr}}CPatient.save_for_files{{/tr}}</div>
      </div>
    </td>
  </tr>

  <tr id="me-edit-patient-actions" class="me-bottom-actions">
    <td class="button" colspan="5" style="text-align:center;" id="button">
      <div id="divSiblings" class="me-no-border" style="display:none;"></div>
      {{if $patient->_id}}
        <button tabindex="400" id="submit-patient" name="submit_patient" type="{{if $modal}}button{{else}}submit{{/if}}"
                class="submit me-primary" onclick="return document.editFrm.onsubmit();">
          {{tr}}Save{{/tr}}
          {{if $patient->_bind_vitale}}
            & {{tr}}BindVitale{{/tr}}
          {{/if}}
        </button>

        {{if "dPpatients sharing patient_data_sharing"|gconf}}
          <button type="button" class="fa fa-share-alt me-tertiary" onclick="PatientGroup.viewGroups('{{$patient->_id}}');">
            {{tr}}CPatientGroup-action-Data sharing{{/tr}}
          </button>
        {{/if}}
        <button type="button" class="print me-tertiary" onclick="Patient.print('{{$patient->_id}}')">
          {{tr}}Print{{/tr}}
        </button>
        {{if $can->admin && $app->_ref_user->isAdmin()}}
          <button type="button" class="cancel me-tertiary"
                  onclick="Patient.confirmPurge(getForm('editFrm'), '{{$patient->_view|smarty:nodefaults|JSAttribute}}');">
            {{tr}}Purge{{/tr}}
          </button>
        {{/if}}
        <button type="button" class="trash me-tertiary me-dark"
                onclick="confirmDeletion(document.editFrm,{typeName:'le patient',objName:'{{$patient->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
        </button>

      {{else}}
        <button tabindex="400" id="submit-patient" name="submit_patient" type="submit" class="submit me-primary"
                onclick="var form = getForm('editFrm'); $V(form._open_corresp, '0'); form.onsubmit();">
          {{tr}}Create{{/tr}}
          {{if $patient->_bind_vitale}}
            &amp; {{tr}}BindVitale{{/tr}}
          {{/if}}
        </button>
        {{if $modal}}
          <button type="submit" class="submit me-tertiary"
                  onclick="var form = getForm('editFrm'); $V(form._open_corresp, '1'); form.onsubmit();">
            {{tr}}Create{{/tr}} & {{tr}}CPatient-Add correspondants{{/tr}}
          </button>
        {{/if}}
      {{/if}}
    </td>
  </tr>
</table>
