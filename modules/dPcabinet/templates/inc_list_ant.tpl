{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossier_medical value=$patient->_ref_dossier_medical}}
{{assign var=prescription_sejour_id value=""}}
{{assign var=display_antecedents_non_presents value="dPpatients CAntecedent display_antecedents_non_presents"|gconf}}
{{if $sejour->_ref_prescription_sejour}}
  {{assign var=prescription_sejour_id value=$sejour->_ref_prescription_sejour->_id}}
{{/if}}

{{mb_script module=patients script=antecedent ajax=true}}
{{mb_script module=patients script=traitements ajax=true}}
{{mb_default var=type_see value=""}}
{{mb_default var=count_abs value=0}}
{{mb_default var=count_atcd_hidden value=0}}

{{assign var=create_antecedent_only_prat value=0}}
{{if "dPpatients CAntecedent create_antecedent_only_prat"|gconf && !$app->user_prefs.allowed_to_edit_atcd &&
  !$app->_ref_user->isPraticien() && !$app->_ref_user->isSageFemme()}}
  {{assign var=create_antecedent_only_prat value=1}}
{{/if}}

{{assign var=create_treatment_only_prat value=0}}
{{if "dPpatients CAntecedent create_treatment_only_prat"|gconf && !$app->user_prefs.allowed_to_edit_treatment &&
  !$app->_ref_user->isPraticien() && !$app->_ref_user->isSageFemme()}}
  {{assign var=create_treatment_only_prat value=1}}
{{/if}}

<script>
  Traitement.prescription_sejour_id = '{{$prescription_sejour_id|@json}}';
  Traitement.copyLine = function(prescription_id) {
    Traitement.prescription_sejour_id = prescription_id;
    var oFormTransfert = getForm("transfert_line_TP-{{$patient->_id}}");

    $V(oFormTransfert.prescription_id, prescription_id);
    onSubmitFormAjax(oFormTransfert, DossierMedical.reloadDossierSejour);
  };

  showModalTP = function() {
    window.modalUrlTp = new Url("prescription", "ajax_vw_traitements_personnels");
    window.modalUrlTp.addParam("object_guid", '{{$sejour->_guid}}');
    window.modalUrlTp.addParam("dossier_anesth_id", "{{$dossier_anesth_id}}");
    window.modalUrlTp.requestModal("100%", "100%", {
      onClose: function() {
        if (window.DossierMedical) {
          window.DossierMedical.reloadDossiersMedicaux();
        }
        if (window.tab_sejour) {
          window.tab_sejour.setActiveTab("prescription_sejour");
        }
        if (window.tabsConsultAnesth) {
          window.tabsConsultAnesth.setActiveTab("prescription_sejour");
        }
        if (window.tabs_operation) {
          window.tabs_operation.setActiveTab("prescription_sejour_tab");
        }
      Prescription.reloadPrescSejour(null, '{{$sejour->_id}}');
    } });
  };

  showModalTPDossierMedical = function() {
    window.modalUrlTpDossierMedical = new Url("prescription", "ajax_vw_tp_dossier_medical");
    window.modalUrlTpDossierMedical.addParam("dossier_medical_id", "{{$dossier_medical->_id}}");

    window.modalUrlTpDossierMedical.requestModal("80%", "80%", {onClose: DossierMedical.reloadDossierPatient});
  };

  searchAtcdMaman = function(sejour_id) {
    new Url("patients", "ajax_list_atcd_maman")
      .addParam("sejour_id", sejour_id)
      .requestModal("60%", "60%");
  };

  Main.add(function() {
    if ($('tab_atcd_{{$dossier_medical->_guid}}')) {
      var tabs = Control.Tabs.create('tab_atcd_{{$dossier_medical->_guid}}', false, {
        afterChange: function(Element) {
          DossierMedical.ant_tab = Element.id;
        },
        defaultTab: ((DossierMedical.ant_tab === null) ? 'first' : DossierMedical.ant_tab)
      });

      tabs.setActiveTab("antecedents-{{$dossier_medical->_guid}}");
    }
  });
</script>

<!--  Formulaire de création de prescription si inexistante -->
<form name="prescription-sejour-{{$patient->_id}}" method="post" onsubmit="return false;">
  <input type="hidden" name="m" value="prescription" />
  <input type="hidden" name="prescription_id" value="" />
  <input type="hidden" name="dosql" value="do_prescription_aed" />
  <input type="hidden" name="type" value="sejour" />
  <input type="hidden" name="object_class" value="CSejour" />
  <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="praticien_id" value="{{$sejour->praticien_id}}" />
  <input type="hidden" name="ajax" value="1" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="callback" value="Traitement.copyLine" />
</form>

<!--  Formulaire de duplication de traitement -->
<form name="transfert_line_TP-{{$patient->_id}}" action="?" method="post" onsubmit="return false;">
  <input type="hidden" name="m" value="prescription" />
  <input type="hidden" name="dosql" value="do_transfert_line_tp_aed" />
  <input type="hidden" name="praticien_id" value="{{$app->user_id}}" />
  <input type="hidden" name="prescription_line_medicament_id" value="" />
  <input type="hidden" name="debut" value="{{$sejour->entree|date_format:'%Y-%m-%d'}}" />
  <input type="hidden" name="prescription_id" value="{{$prescription_sejour_id}}" />
</form>

{{if !$type_see || $type_see == "antecedent"}}
  {{if $dossier_medical->_count_cancelled_antecedents}}
    <button class="search not-printable me-tertiary" style="float: right" onclick="Antecedent.toggleCancelled('antecedents-{{$dossier_medical->_guid}}')">
      {{tr var1=$dossier_medical->_count_cancelled_antecedents}}CAntecedent-see_cancelled_nb{{/tr}}
    </button>
  {{/if}}
  <button class="vslip not-printable me-tertiary" style="float:right" onclick="DossierMedical.toggleSortAntecedent('{{$type_see}}')">
    {{tr}}CAntecedent-sort_by_{{if $sort_by_date}}type{{else}}date{{/if}}{{/tr}}
  </button>

  {{if "soins Other ignore_allergies"|gconf && array_key_exists("alle", $dossier_medical->_ref_antecedents_by_type) && !$dossier_medical->_ref_antecedents_by_type.alle|@count && !$create_antecedent_only_prat}}
    {{assign var=ignore_allergies value="soins Other ignore_allergies"|gconf}}
    {{assign var=ignore_allergies_one value="|"|explode:$ignore_allergies}}
    <form name="save_aucun_atcd" action="?" method="post" onsubmit="return onSubmitAnt(this, '{{$type_see}}');" style="float: right;" class="not-printable">
      <input type="hidden" name="m" value="dPpatients" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="dosql" value="do_antecedent_aed" />
      <input type="hidden" name="type" value="alle" />
      <input type="hidden" name="rques" value="{{$ignore_allergies_one[0]}}" />
      <input type="hidden" name="_patient_id" value="{{$patient->_id}}" />
      {{if $sejour->_id}}
        <input type="hidden" name="_sejour_id" value="{{$sejour->_id}}" />
      {{/if}}
      <button type="button" class="tick" onclick="return onSubmitAnt(this.form, '{{$type_see}}');">{{tr}}Allergie.none{{/tr}}</button>
    </form>
  {{/if}}

  {{if "maternite"|module_active && $sejour->_ref_naissance && $sejour->_ref_naissance->_id}}
    <button type="button" class="search not-printable me-tertiary" style="float: right;" onclick="searchAtcdMaman('{{$sejour->_id}}');">
     {{tr}}CAntecedent-mother|pl{{/tr}}
    </button>
  {{/if}}

  {{if $count_atcd_hidden > 0}}
    <form action="?" onsubmit="return DossierMedical.submitHideAtcd(this)" method="post">
      <input type="hidden" name="m" value="admin"/>
      <input type="hidden" name="dosql" value="do_preference_aed">
      <input type="hidden" name="user_id" value="{{$app->user_id}}">
      {{mb_ternary var=show_diff_func_atcd test=$app->user_prefs.hide_diff_func_atcd value=0 other=1}}
      <input type="hidden" name="pref[hide_diff_func_atcd]" value="{{$show_diff_func_atcd}}">
      {{mb_ternary var=atcd_button_class test=$app->user_prefs.hide_diff_func_atcd value="zoom-in" other="zoom-out"}}
      <button class="{{$atcd_button_class}} button me-tertiary not-printable" style="float:right" type="button" onclick="this.form.onsubmit()">
        {{tr var1=$count_atcd_hidden}}CAntecedent-{{if $app->user_prefs.hide_diff_func_atcd}}see_all{{else}}hide{{/if}}_atcd{{/tr}}
      </button>
    </form>
  {{/if}}

  <strong {{if $dossier_medical->_count_cancelled_antecedents}}style="line-height: 22px;"{{/if}}>
   {{tr}}CAntecedent-by_{{if $sort_by_date}}date{{else}}type_appareil{{/if}}{{/tr}}
  </strong>

  {{if $count_atcd_hidden > 0 && $app->user_prefs.hide_diff_func_atcd}}
    <div class="small-warning" style="width: 175px;">{{tr}}CAntecedent-atcd_hidden{{/tr}}</div>
  {{/if}}

  <ul id="tab_atcd_{{$dossier_medical->_guid}}" class="control_tabs small me-list me-list-style-inside">
    <li>
      <a href="#antecedents-{{$dossier_medical->_guid}}" {{if !$count_atcd}}class="empty"{{/if}}>
        {{tr}}CAntecedent|pl{{/tr}} <small>({{$count_atcd}})</small>
      </a>
    </li>
    {{if $display_antecedents_non_presents || $count_abs > 0}}
      <li>
        <a href="#antecedents-absence-{{$dossier_medical->_guid}}" {{if !$count_abs}}class="empty"{{/if}}>
          {{tr}}CAntecedent-No antecedent|pl{{/tr}} <small>({{$count_abs}})</small>
        </a>
      </li>
    {{/if}}
  </ul>

  <ul id="antecedents-{{$dossier_medical->_guid}}" class="me-list-style-inside">
    <button type="button" class="cancel not-printable me-tertiary me-small" style="float: right;position: relative;z-index: 1;" onclick="Antecedent.cancelAllAntecedents();">
      {{tr}}CAntecedent-action-Cancel all antecedents{{/tr}}
    </button>
    {{if $dossier_medical->_count_antecedents || $dossier_medical->_count_cancelled_antecedents}}
      {{if $sort_by_date}}
        {{mb_include module=dPcabinet template=antecedents/inc_list_ants_date}}
      {{else}}
        {{mb_include module=dPcabinet template=antecedents/inc_list_ants_type}}
      {{/if}}
    {{else}}
      <li style="list-style-position: inside;" class="empty">{{tr}}CAntecedent.unknown{{/tr}}</li>
    {{/if}}
  </ul>
  {{if $display_antecedents_non_presents || $count_abs > 0}}
    <ul id="antecedents-absence-{{$dossier_medical->_guid}}" class="me-list-style-inside">
      <button type="button" class="cancel not-printable me-tertiary" style="float: right;position: relative;z-index: 1;" onclick="Antecedent.cancelAllAntecedents('_NP');">
        {{tr}}CAntecedent-action-Cancel all antecedents NP{{/tr}}
      </button>
      {{if $dossier_medical->_count_antecedents || $dossier_medical->_count_cancelled_antecedents}}
        {{if $sort_by_date}}
          {{mb_include module=dPcabinet template=antecedents/inc_list_ants_date see_absence=true}}
        {{else}}
          {{mb_include module=dPcabinet template=antecedents/inc_list_ants_type see_absence=true}}
        {{/if}}
      {{else}}
        <li style="list-style-position: inside;" class="empty">{{tr}}CAntecedent.unknown{{/tr}}</li>
      {{/if}}
    </ul>
{{/if}}
  {{if $sejours|@count}}
    <hr style="width: 50%;" />
    <strong>{{tr}}CSejour-last_motifs{{/tr}}</strong>
    <ul>
      {{foreach from=$sejours item=_sejour}}
        {{if $_sejour->_motif_complet != "[Att] "}}
          <li>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
              {{$_sejour->entree|date_format:"%m/%Y"}}: {{$_sejour->_motif_complet|nl2br}}
            </span>
          </li>
        {{/if}}
      {{/foreach}}
    </ul>
  {{/if}}
{{/if}}

{{if !$type_see && "maternite"|module_active}}
    {{if $patient->_ref_grossesses}}
        <strong>{{tr}}CGrossesse|pl{{/tr}}</strong>
        <ul>
            {{foreach from=$patient->_ref_grossesses item=_grossesse}}
                <li>{{mb_value object=$_grossesse}}</li>
            {{/foreach}}
        </ul>
    {{/if}}
{{/if}}

{{if !$type_see || $type_see == "traitement"}}
  {{assign var=display value="none"}}
  {{if !$dossier_medical->_count_traitements}}
    {{assign var=display value="inline"}}
  {{elseif $dossier_medical->absence_traitement}}
    <script>
      Main.add(function(){
        var form = getForm("save_absence_ttt");
        $V(form.absence_traitement, "0");
      });
    </script>
  {{/if}}
  {{if !$create_treatment_only_prat}}
    <form name="save_absence_ttt" action="?" method="post" onsubmit="return onSubmitFormAjax(this);" style="float: right;display: {{$display}}">
      {{mb_key   object=$dossier_medical}}
      <input type="hidden" name="m"     value="patients" />
      <input type="hidden" name="del"     value="0" />
      <input type="hidden" name="dosql"     value="do_dossierMedical_aed" />
      <input type="hidden" name="object_id" value="{{$patient->_id}}" />
      <input type="hidden" name="object_class" value="{{$patient->_class}}" />
      {{mb_label object=$dossier_medical field=absence_traitement typeEnum=checkbox}}
      {{mb_field object=$dossier_medical field=absence_traitement typeEnum=checkbox onchange="return onSubmitFormAjax(this.form);"}}
    </form>
  {{/if}}

  <!-- Traitements -->
  {{if is_array($dossier_medical->_ref_traitements) || $dossier_medical->_ref_prescription}}
    {{if $dossier_medical->_count_cancelled_traitements}}
      <button class="search not-printable me-tertiary" style="float: right;" onclick="Traitement.toggleCancelled('traitements-{{$dossier_medical->_guid}}')">
        {{tr var1=$dossier_medical->_count_cancelled_traitements}}CTraitement-see_cancelled_nb{{/tr}}
      </button>
    {{/if}}

    {{if $show_gestion_tp && $sejour->_id && ($app->_ref_user->isPraticien() || $app->_ref_user->isSageFemme() || !"mpm general role_propre"|gconf)}}
      <div style="float: right">

        {{if $dossier_medical->_ref_prescription
             && $dossier_medical->_ref_prescription->_ref_prescription_lines|@count != $dossier_medical->_count_cancelled_traitements}}
          <form name="stopAllTP" method="post">
            <input type="hidden" name="m" value="prescription" />
            <input type="hidden" name="dosql" value="do_stop_all_tp" />
            <input type="hidden" name="dossier_medical_id" value="{{$dossier_medical->_id}}" />
            <button type="button" class="cancel not-printable me-tertiary"
                    onclick="Traitement.stopAll(this.form, DossierMedical.reloadDossierPatient.curry(null, '{{$type_see}}'));">{{tr}}common-action-All stop{{/tr}}</button>
          </form>
        {{/if}}

        <button class="tick not-printable me-tertiary" type="button" onclick="showModalTP();">
        {{tr}}CTraitement-perso_gestion{{/tr}} ({{$sejour->_ref_prescription_sejour->_count_lines_tp}}/{{if $dossier_medical->_ref_prescription}}{{$dossier_medical->_ref_prescription->_ref_prescription_lines|@count}}{{else}}0{{/if}})
        </button>
        {{if "dPpatients CTraitement perso_gestion_dossier_med"|gconf}}
          <br />
          <button type="button" class="tick not-printable me-tertiary" onclick="showModalTPDossierMedical();">
            {{tr}}CTraitement-perso_gestion_dossier_med{{/tr}}
          </button>
        {{/if}}
      </div>
    {{/if}}

    <strong>{{tr}}CTraitement-perso|pl{{/tr}}</strong>
  {{/if}}

  <div id="traitements-{{$dossier_medical->_guid}}">
  {{if $dossier_medical->_ref_prescription}}
    <ul>
    {{foreach from=$dossier_medical->_ref_prescription->_ref_prescription_lines item=_line}}
      <li {{if $_line->_stopped}}class="cancelled" style="display: none;"{{/if}}>
        <form name="delTraitementDossierMedPat-{{$_line->_id}}"  action="?" method="post">
          <input type="hidden" name="m" value="mpm" />
          <input type="hidden" name="del" value="1" />
          <input type="hidden" name="dosql" value="do_prescription_line_medicament_aed" />
          <input type="hidden" name="prescription_line_medicament_id" value="{{$_line->_id}}" />

          {{if $_line->creator_id == $app->user_id && !$create_antecedent_only_prat}}
          <button class="trash notext not-printable me-tertiary me-dark" type="button" onclick="Traitement.remove(this.form, function() {DossierMedical.reloadDossierPatient(null, '{{$type_see}}');})">
            {{tr}}Delete{{/tr}}
          </button>
          {{/if}}

          {{mb_include module=cabinet template=antecedents/inc_line_atcd}}
        </form>
      </li>
    {{/foreach}}
    {{if $dossier_medical->_ref_prescription->_ref_prescription_lines_element|@count}}
      <hr style="width: 50%;" />
    {{/if}}
      {{foreach from=$dossier_medical->_ref_prescription->_ref_prescription_lines_element item=_line}}
        <li>
          <form name="delTraitementDossierElemsPat-{{$_line->_id}}"  action="?" method="post">
            <input type="hidden" name="m" value="dPprescription" />
            <input type="hidden" name="del" value="1" />
            <input type="hidden" name="dosql" value="do_prescription_line_element_aed" />
            <input type="hidden" name="prescription_line_element_id" value="{{$_line->_id}}" />

            {{if $_line->creator_id == $app->user_id && !$create_antecedent_only_prat}}
              <button class="trash notext not-printable me-tertiary me-dark" type="button" onclick="Traitement.remove(this.form, function() {DossierMedical.reloadDossierPatient(null, '{{$type_see}}');})">
                {{tr}}Delete{{/tr}}
              </button>
            {{/if}}
            {{mb_include module=system template=inc_interval_date from=$_line->debut to=$_line->fin}}
            {{assign var=creation_date value=$_line->creation_date|date_format:'%Y-%m-%d'}}
            {{assign var=_color value='black'}}
            {{if ($context_date_max && $context_date_min && $creation_date >= $context_date_min && $creation_date <= $context_date_max)
                 || ($context_date_max && !$context_date_min && $creation_date == $context_date_max)}}
              {{assign var=_color value='darkblue'}}
            {{elseif $context_date_max && $creation_date >= $context_date_max}}
              {{assign var=_color value='dimgrey'}}
            {{/if}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_line->_guid}}', 'objectView')" style="color: {{$_color}};">
              {{$_line->_view}}
            </span>
            <span class="compact" style="display: inline;">
              {{$_line->commentaire}}
              {{if $_line->_ref_prises|@count}}
                <br />
                ({{foreach from=`$_line->_ref_prises` item=_prise name=foreach_prise}}
                  {{$_prise}}{{if !$smarty.foreach.foreach_prise.last}},{{/if}}
                {{/foreach}})
              {{/if}}
            </span>
          </form>
        </li>
      {{/foreach}}
    </ul>
    {{if $dossier_medical->_ref_traitements|@count && $dossier_medical->_ref_prescription->_ref_prescription_lines|@count}}
    <hr style="width: 50%;" />
    {{/if}}
  {{/if}}


  {{if is_array($dossier_medical->_ref_traitements)}}
  <ul>
    {{foreach from=$dossier_medical->_ref_traitements item=_traitement}}
    <li {{if $_traitement->annule}}class="cancelled" style="display: none;"{{/if}}>
      <form name="delTrmtFrm-{{$_traitement->_id}}" action="?m=dPcabinet" method="post">
      <input type="hidden" name="m" value="dPpatients" />
      <input type="hidden" name="del" value="0" />
      <input type="hidden" name="dosql" value="do_traitement_aed" />
      {{mb_key object=$_traitement}}

      {{if $_traitement->owner_id == $app->user_id && !$create_antecedent_only_prat}}
      <button class="trash notext not-printable me-tertiary me-dark" type="button" onclick="Traitement.remove(this.form, function() {DossierMedical.reloadDossierPatient(null, '{{$type_see}}');})">
        {{tr}}delete{{/tr}}
      </button>
      {{/if}}

      {{mb_include module=system template=inc_interval_date_progressive object=$_traitement from_field=debut to_field=fin}}

      {{assign var=creation_date value=$_traitement->creation_date|date_format:'%Y-%m-%d'}}
      {{assign var=_color value='black'}}
      {{if ($context_date_max && $context_date_min && $creation_date >= $context_date_min && $creation_date <= $context_date_max)
           || ($context_date_max && !$context_date_min && $creation_date == $context_date_max)}}
        {{assign var=_color value='darkblue'}}
      {{elseif $context_date_max && $creation_date >= $context_date_max}}
        {{assign var=_color value='dimgrey'}}
      {{/if}}
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_traitement->_guid}}')" style="color: {{$_color}};">
        {{$_traitement->traitement|nl2br}}
      </span>

      </form>
    </li>
    {{foreachelse}}
    {{if !($dossier_medical->_ref_prescription && $dossier_medical->_ref_prescription && $dossier_medical->_ref_prescription->_ref_prescription_lines|@count)}}
    <li class="empty">{{tr}}CTraitement.unknown{{/tr}}</li>
    {{/if}}
    {{/foreach}}
  </ul>
  {{/if}}
  </div>
{{/if}}

{{if !$type_see || $type_see == "cim"}}
  <strong>{{tr}}CCodeCIM10-diag|pl{{/tr}}</strong>
  <ul>
    {{foreach from=$dossier_medical->_ext_codes_cim item=_code}}
    <li>
      {{if !$create_antecedent_only_prat}}
        <button class="trash notext not-printable me-tertiary me-dark" type="button"
        onclick="{{if "snomed"|module_active}}$V(getForm('editDiagFrm')._del_code_cim_snomed, '{{$_code->code}}');{{/if}} oCimField.remove('{{$_code->code}}')">
          {{tr}}Delete{{/tr}}
        </button>
        {{if $_is_anesth || $sejour->_id}}
          <button class="add notext not-printable me-tertiary" type="button" onclick="oCimAnesthField.add('{{$_code->code}}')">
            {{tr}}Add{{/tr}}
          </button>
        {{/if}}
      {{/if}}
      {{if "vidal"|module_active && 'Ox\Mediboard\Medicament\CMedicament::getBase'|static_call:null == "vidal"}}
        {{mb_include module=vidal template=inc_button_reco_cim code_cim=$_code->code}}
      {{/if}}

      {{$_code->code}}: {{$_code->libelle}}
    </li>
    {{foreachelse}}
    <li class="empty">{{tr}}CDossierMedical-codes_cim.unknown{{/tr}}</li>
    {{/foreach}}
  </ul>

  <!-- Gestion des diagnostics pour le dossier medical du patient -->
  <form name="editDiagFrm" action="?m=dPcabinet" method="post">
    <input type="hidden" name="m" value="dPpatients" />
    <input type="hidden" name="tab" value="edit_consultation" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
    <input type="hidden" name="object_id" value="{{$patient->_id}}" />
    <input type="hidden" name="object_class" value="CPatient" />
    <input type="hidden" name="codes_cim" value="{{$dossier_medical->codes_cim}}" />
  </form>

  <script>
  // FIXME : Modifier le tokenfield, car deux appels à onchange
  Main.add(function(){
    var form = getForm("editDiagFrm");

    // form may be undefined if the page is changed while loading
    if (form) {
      oCimField = new TokenField(form.codes_cim, {
        confirm  : $T('CCodeCIM10-confirm_del'),
        onChange : updateTokenCim10
      });
    }
    {{if $dossier_medical->_id}}
      if (window.tabsConsult || window.tabsConsultAnesth) {
        {{assign var=z value=0}}
        {{if $dossier_medical->_ref_prescription}}
          {{assign var=z value=$dossier_medical->_ref_prescription->_ref_prescription_lines|@count}}
        {{/if}}
        var count_tab = '{{math equation=w+x+y+z
          w=$dossier_medical->_all_antecedents|@count
          x=$dossier_medical->_ref_traitements|@count
          y=$dossier_medical->_ext_codes_cim|@count
          z=$z}}';
        Control.Tabs.setTabCount("AntTrait", count_tab);
      }
    {{/if}}
  });
  </script>
{{/if}}
