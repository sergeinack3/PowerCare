{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hprim21     script=pat_hprim_selector}}
{{mb_script module=hprim21     script=sejour_hprim_selector}}
{{mb_script module=admissions  script=admissions}}
{{mb_script module=patients    script=antecedent}}
{{mb_script module=compteRendu script=document}}
{{mb_script module=compteRendu script=modele_selector}}
{{mb_script module=files       script=file}}
{{mb_script module=planningOp  script=sejour}}
{{mb_script module=planningOp  script=prestations}}
{{mb_script module=planningOp  script=appel}}
{{mb_script module=patients    script=identity_validator}}

{{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
  {{mb_script module=appFineClient  script=appFineClient}}
{{/if}}

{{if "dPsante400"|module_active}}
  {{mb_script module=dPsante400  script=Idex}}
{{/if}}

{{if "web100T"|module_active}}
  {{mb_script module=web100T script=web100T}}
{{/if}}

<script>
  function printPlanning() {
    var form = getForm("selType");
    var url = new Url("admissions", "print_entrees");
    url.addParam("date"      , $V(form.date));
    url.addParam("type"      , $V(form._type_admission));
    url.addParam("service_id", [$V(form.service_id)].flatten().join(","));
    url.addParam("period"    , $V(form.period));
    url.addParam("group_by_prat", $V(form.group_by_prat));
    url.addParam("order_by", $V(form.order_print_way));
    url.popup(700, 550, "Entrees");
  }

  function commonParams(url, form) {
    url.addParam("date"     , $V(form.date))
      .addParam("type"      , $V(form._type_admission))
      .addParam("service_id", [$V(form.service_id)].flatten().join(","))
      .addParam("prat_id"   , $V(form.prat_id))
      .addParam("selAdmis"  , $V(form.selAdmis))
      .addParam("selSaisis" , $V(form.selSaisis))
      .addParam("notification_status", $V(form.status))
      .addParam("type_pec[]" , $V(form.elements['type_pec[]']), true)
      .addParam("active_filter_services" , $V(form.elements['active_filter_services']))
      .addParam("date_interv_eg_entree", ($V(form._date_interv_eg_entree)) ? 1 : 0)
      .addParam("circuit_ambu[]", $V(form.elements['circuit_ambu[]']), true)
      .addParam("prestations_p_ids[]", $V(form.prestations_p_ids), true);

    if ($(form.name + '_reglement_dh')) {
      url.addParam('reglement_dh', $V(form.elements['reglement_dh']));
    }
  }

  function reloadFullAdmissions() {
    var form = getForm("selType");
    var url = new Url("admissions", "httpreq_vw_all_admissions");
    commonParams(url, form);
    url.requestUpdate('allAdmissions' , reloadAdmission);
  }

  function reloadAdmission(page = 0) {
    var form = getForm("selType");
    var url = new Url("admissions", "httpreq_vw_admissions");
    commonParams(url, form);
    url.addParam("period"    , $V(form .period));
    url.addParam("order_way" , $V(form.order_way));
    url.addParam("order_col" , $V(form.order_col));
    url.addParam("page"      , page);
    url.addParam("filterFunction", $V(form.filterFunction));
    url.requestUpdate('listAdmissions');
  }

  function reloadAdmissionDate(elt, date) {
    var form = getForm("selType");
    $V(form.date, date);
    var old_selected = elt.up("table").down("tr.selected");
    old_selected.select('td').each(function(td) {
      // Supprimer le style appliqué sur le nombre d'admissions
      var style = td.readAttribute("style");
      if (/bold/.match(style)) {
        td.writeAttribute("style", "");
      }
    });
    old_selected.removeClassName("selected");

    // Mettre en gras le nombre d'admissions
    var elt_tr = elt.up("tr");
    elt_tr.addClassName("selected");
    var pos = 1;
    if ($V(form.selSaisis) == 'n') {
      pos = 2;
    }
    else if ($V(form.selAdmis) == 'n') {
      pos = 3;
    }
    var td = elt_tr.down("td", pos);

    td.writeAttribute("style", "font-weight: bold");
    reloadAdmission();
  }

  function confirmation(form) {
    if (confirm('La date enregistrée d\'admission est différente de la date prévue, souhaitez vous confimer l\'admission du patient ?')) {
      submitAdmission(form);
    }
  }

  function submitCote(form, sejour_id) {
    return onSubmitFormAjax(form, Admissions.reloadAdmissionLine.curry(sejour_id));
  }

  function submitMultiple(form) {
      return onSubmitFormAjax(form, reloadFullAdmissions);
  }

  function submitAdmission(form, bPassCheck) {
    {{if "dPsante400"|module_active && "dPsante400 CIdSante400 admit_ipp_nda_obligatory"|gconf}}
      var oIPPForm    = getForm("editIPP" + $V(form.patient_id));
      var oNumDosForm = getForm("editNumdos" + $V(form.sejour_id));
      if (!bPassCheck && oIPPForm && oNumDosForm && (!$V(oIPPForm.id400) || !$V(oNumDosForm.id400)) ) {
        Idex.edit_manually($V(oNumDosForm.object_class)+"-"+$V(oNumDosForm.object_id),
                           $V(oIPPForm.object_class)+"-"+$V(oIPPForm.object_id),
                           Admissions.reloadAdmissionLine.curry($V(form.sejour_id)));
      }
      else {
        return onSubmitFormAjax(form, Admissions.reloadAdmissionLine.curry($V(form.sejour_id)));
      }
    {{else}}
      return onSubmitFormAjax(form, Admissions.reloadAdmissionLine.curry($V(form.sejour_id)));
    {{/if}}
  }

  function sortBy(order_col, order_way) {
    var form = getForm("selType");
    $V(form.order_col, order_col);
    $V(form.order_way, order_way);
    reloadAdmission($V(form.page));
  }

  function changePage(page) {
    let form = getForm("selType");
    $V(form.page, page);
    reloadAdmission(page);
  }

  function filterAdm(selAdmis, selSaisis) {
    var form = getForm("selType");
    $V(form.selAdmis, selAdmis);
    $V(form.selSaisis, selSaisis);
    reloadFullAdmissions();
  }

  function changeEtablissementId(oForm) {
    submitAdmission(oForm);
  }

  {{assign var=auto_refresh_frequency value="dPadmissions automatic_reload auto_refresh_frequency_admissions"|gconf}}

  Main.add(function() {
    Admissions.table_id = "listAdmissions";
    var form = getForm("selType");

    var totalUpdater = new Url("admissions", "httpreq_vw_all_admissions");
    var listUpdater = new Url("admissions", "httpreq_vw_admissions");

    {{if $auto_refresh_frequency != 'never'}}
      Admissions.totalUpdater = totalUpdater.periodicalUpdate('allAdmissions', {frequency: {{$auto_refresh_frequency}}});
      Admissions.listUpdater = listUpdater.periodicalUpdate('listAdmissions', {
        frequency: {{$auto_refresh_frequency}},
        onCreate: function() {
          WaitingMessage.cover($('listAdmissions'));
          Admissions.rememberSelection();
        }
      });
    {{else}}
      totalUpdater.requestUpdate('allAdmissions');
      listUpdater.requestUpdate('listAdmissions', {
        onCreate: function() {
          WaitingMessage.cover($('listAdmissions'));
          Admissions.rememberSelection();
        }});
    {{/if}}

    {{if "dPpatients CPatient manage_identity_vide"|gconf}}
      IdentityValidator.active = true;
    {{/if}}

    $("listAdmissions").fixedTableHeaders();
    $("allAdmissions").fixedTableHeaders();
  });
</script>

{{mb_include module=admissions template=inc_prompt_modele type=admissions}}

<table class="main">
  <tr>
    <td id="idx_admission_legend" colspan="2">
      <a href="#legend" id="idx_admission_legend_button" onclick="Admissions.showLegend()" class="button search me-tertiary me-dark">Légende</a>
      {{if "astreintes"|module_active}}{{mb_include module=astreintes template=inc_button_astreinte_day date=$date}}{{/if}}
    </td>
    <td id="filter_admission">
      {{mb_include module=admissions template=inc_admission_filter reload_full_function='reloadFullAdmissions' reload_lite_function='reloadAdmission' view='admissions' table_id='admissions'}}
    </td>
  </tr>
  <tr>
    <td>
      <div id="allAdmissions" class="admissionScrollbar me-align-auto"></div>
    </td>
    <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).previous()); Admissions.updateAdmissionIdxLayout();" style="padding: 0;"></td>
    <td style="width: 100%">
      <div id="listAdmissions" class="me-align-auto"></div>
    </td>
  </tr>
</table>
