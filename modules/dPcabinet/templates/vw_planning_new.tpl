{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet  script=plage_consultation}}
{{mb_script module=ssr      script=planning}}
{{mb_script module=patients script=identity_validator}}

{{assign var=refresh_planning value='dPcabinet Planning auto_refresh_planning_frequency'|gconf}}

{{mb_default var=multiple value=0}}

{{if $listChirs|@count && $function->_id}}
  {{assign var=multiple value=1}}
{{/if}}

<script>
  var tabs = null;
  window.save_dates = {
    prev: '{{$prev}}',
    next: '{{$next}}',
    today: '{{$today}}'
  };

  Main.add(function() {
    {{if $multiple}}
      tabs = Control.Tabs.create('tabs_prats', true);
      tabs.activeLink.onmousedown();
    {{else}}
      refreshPlanning(null, '{{$debut}}');
    {{/if}}

    {{if $refresh_planning}}
      //Raffraichissement périodique de l'agenda
      setInterval(refreshPlanning, parseInt('{{$refresh_planning}}')*1000);
    {{/if}}

    {{if "dPpatients CPatient manage_identity_vide"|gconf}}
      IdentityValidator.active = true;
    {{/if}}

    new Url('mediusers', 'ajax_functions_autocomplete')
      .addParam('type', 'cabinet')
      .addParam('edit', '1')
      .addParam('input_field', 'function_view')
      .autoComplete(getForm('changeDate').elements['function_view'], null, {
          minChars: 0,
          method: 'get',
          select: 'view',
          dropdown: true,
          afterUpdateElement: function (field, selected) {
              $V(field, selected.down('.view').innerHTML);
              $V(field.form.elements['function_id'], selected.getAttribute('id').split('-')[2]);
          }
      });

    new Url('mediusers', 'ajax_users_autocomplete')
      .addParam('praticiens', 1)
      .addParam('edit', '1')
      .addParam('input_field', 'chir_view')
      .autoComplete(getForm('changeDate').elements['chir_view'], null, {
          minChars: 0,
          method: 'get',
          select: 'view',
          dropdown: true,
          afterUpdateElement: function (field, selected) {
              $V(field.form.elements['chirSel'], selected.get('id'));
          }
      });
  });

  function onSelectChir(form) {
      if ($V(form.elements['chirSel']) !== '') {
          $V(form.elements['function_id'], '', false);
          $V(form.elements['function_view'], '', false);
          form.submit();
      }
  };

  function onSelectFunction(form) {
      if ($V(form.elements['function_id']) !== '') {
          $V(form.elements['chirSel'], '', false);
          $V(form.elements['chir_view'], '', false);
          form.submit();
      }
  };

  function emptyFunctionSelector(form) {
      $V(form.elements['function_id'], '');
      $V(form.elements['function_view'], '');
  };

  function changeTabPlanningNew(chir_id) {
    var form = getForm("changeDate");
    $V(form.chirSel, chir_id, false);
    tabs.setActiveTab("planning-plages_"+chir_id);
    refreshPlanning();
  }

  function printPlanning(function_mode) {
    var form = getForm("changeDate");
    if (function_mode) {
      var url = new Url("cabinet", "print_planning_function");
      url.addParam("date", $V(form.debut));
      url.addParam("function_id", $V(form.function_id));
      url.popup(900, 600, "Planning");
    }
    else {
      var url = new Url("cabinet", "print_planning");
      url.addParam("date", $V(form.debut));
      url.addParam("chir_id", $V(form.chirSel));
      url.popup(900, 600, "Planning");
    }
  }

  function showConsultSiDesistement(){
    var form = getForm("changeDate");
    var url = new Url("cabinet", "vw_list_consult_si_desistement");
    {{if $multiple}}
      url.addParam("function_id", $V(form.function_id));
    {{else}}
      url.addParam("chir_id", $V(form.chirSel));
    {{/if}}
    url.requestModal();
  }

  function updateStatusCut() {
    var div = $("status_cut");
    if (window.copy_consult_id) {
      div.update("Copier en cours");
      div.setStyle({borderColor: "#080"});
    }
    else if (window.cut_consult_id) {
      div.update("Couper en cours");
      div.setStyle({borderColor: "#080"});
    }
    else {
      div.update();
      div.setStyle({borderColor: "#ddd"});
      if (window.save_elt) {
        save_elt.removeClassName("opacity-50");
      }
    }
  }

  function cutCopyConsultation(consultation_id, plageconsult_id, heure, action) {
    var form = getForm("cutCopyConsultFrm");
    $V(form.consultation_id, consultation_id);
    $V(form.plageconsult_id, plageconsult_id);
    $V(form.heure, heure);
    $V(form.dosql, action);
    onSubmitFormAjax(form, {onComplete: refreshPlanning});
  }

  function refreshPlanning(type_date, date) {
    var form = getForm("changeDate");
    var url = new Url("cabinet", "ajax_vw_planning");
    url.addParam('type_view', null);
    url.addParam("chirSel", $V(form.chirSel));
    url.addParam("function_id", $V(form.function_id));
    if (type_date == "prev") {
      url.addParam("debut", window.save_dates.prev);
      $V(form.debut, window.save_dates.prev, false);
    }
    else if (type_date == "next") {
      url.addParam("debut", window.save_dates.next);
      $V(form.debut, window.save_dates.next, false);
    }
    else if (type_date == "today") {
      url.addParam("debut", window.save_dates.today);
      $V(form.debut, window.save_dates.today, false);
    }
    else if (date) {
      url.addParam("debut", date);
      $V(form.debut, date, false);
    }
    var week_containers = $$(".week-container");
    if (week_containers.length > 0) {
      $V(form.scroll_top, week_containers[0].scrollTop);
    }

    // filters
    url.addParam("show_free", $V(form.show_free));
    url.addParam("show_cancelled", $V(form.cancelled));
    url.addParam("hide_in_conge", $V(form.hide_in_conge));
    url.addParam("hide_empty_range", $V(form.hide_empty_range));
    url.addParam("facturated", $V(form.facturated));
    url.addParam("status", $V(form.finished));
    url.addParam("actes", $V(form.actes));
    url.addParam("scroll_top", $V(form.scroll_top));
    $V(form.export_chir, $V(form.chirSel));

    url.requestUpdate('planning-plages');
  }

  function setClose(heure, plage_id, date, chir_id, consult_id) {
    if (window.action_in_progress) {
      window.action_in_progress = false;
      return;
    }

    // Action de coller d'un couper
    if (window.cut_consult_id) {
      cutCopyConsultation(window.cut_consult_id, plage_id, heure, 'do_consultation_aed');
      // On garde la consultation d'origine, et on permet de la coller ultérieurement
      window.copy_consult_id = window.cut_consult_id;
      window.cut_consult_id = null;
      updateStatusCut();
      return;
    }

    // Action de coller d'un copier
    if (window.copy_consult_id) {
      cutCopyConsultation(window.copy_consult_id, plage_id, heure, 'do_copy_consultation_aed');
      return;
    }

    // Clic sur une consultation
    if (consult_id) {
      modalPriseRDV(consult_id);
    }
    else {
      modalPriseRDV(0, Date.fromLocaleDate(date.split(" ")[1]).toDATE(), heure, plage_id);
    }
  }

  function modalPriseRDV(consult_id, date, heure, plage_id) {
    var url = new Url("cabinet", "edit_planning");

    url.addParam("dialog", 1);
    url.addParam("consultation_id", consult_id);

    url.addParam("date_planning", date);
    url.addParam("heure", heure);
    url.addParam("plageconsult_id", plage_id);

    url.modal({
      width: "100%",
      height: "100%"
    });

    url.modalObject.observe("afterClose", refreshPlanning);
  }

  cancelRdv = function(consult_id) {
    var url = new Url("cabinet", "ajax_cancel_rdv_planning");
    url.addParam("consultation_id", consult_id);
    url.requestModal(null, null, {onClose: function() {
        refreshPlanning();
      }
    });
  };

  downloadPlanningCSV = function () {
    var url = new Url("cabinet", "export_agenda_csv", "raw");
    url.addParam("prat_id", $V(getForm('changeDate').export_chir));
    url.pop(500, 300, "Export planning CSV");
  };

  function openLegend() {
    var url = new Url("cabinet", "ajax_legend_planning_new");
    url.requestModal(300);
  }
</script>

<style>
  .event.rdvfull {
    z-index: 299;
  }

  .event.rdvfull:hover {
    z-index: 400;
  }

  .event.rdvfree {
    z-index: 300;
  }
</style>

<form name="cutCopyConsultFrm" method="post">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="dosql" />
  <input type="hidden" name="consultation_id" />
  <input type="hidden" name="plageconsult_id" />
  <input type="hidden" name="heure" />
</form>

<form name="editConsult" method="post">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="dosql" value="do_consultation_aed" />
  <input type="hidden" name="consultation_id" />
  <input type="hidden" name="plageconsult_id" />
  <input type="hidden" name="heure" />
</form>

<form name="chronoPatient" method="post">
  <input type="hidden" name="m" value="cabinet"/>
  <input type="hidden" name="dosql" value="do_consultation_aed" />
  <input type="hidden" name="consultation_id" />
  <input type="hidden" name="chrono" />
  <input type="hidden" name="arrivee" />
</form>


  <form action="?" name="changeDate" method="get">
    <input type="hidden" name="scroll_top" value="0" />
    <input type="hidden" name="m" value="{{$m}}" />
    <input type="hidden" name="tab" value="{{$tab}}" />
    <input type="hidden" name="plageconsult_id" value="0" />
    <input type="hidden" name="export_chir" value="{{$chirSel}}"/>
    <table class="me-margin-bottom--3">
      <tr>
        <td style="min-width: 230px; vertical-align: top;">
            <input type="hidden" name="function_id" value="{{$function->_id}}" onchange="onSelectFunction(this.form);">
            <input type="text" name="function_view" value="{{if $function->_id}}{{$function}}{{/if}}">
            <button type="button" class="cancel notext compact me-tertiary me-dark" onclick="emptyFunctionSelector(this.form);">{{tr}}Empty{{/tr}}</button>
        </td>
        <td class="me-valign-bottom">
          {{if $multiple}}
            <ul class="control_tabs small me-no-border-bottom me-control-tabs-wraped" id="tabs_prats">
              {{foreach from=$listChirs item=_chir}}
                <li>
                  <a href="#planning-plages_{{$_chir->_id}}" onmousedown="changeTabPlanningNew('{{$_chir->_id}}');">
                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_chir use_chips=0}}
                    <div id="planning-plages_{{$_chir->_id}}" class="me-no-display"></div>
                  </a>
                </li>
              {{/foreach}}
            </ul>
          {{/if}}
        </td>
      </tr>
    </table>
    <table class="main me-no-align me-bg-white me-border-top">
      <tr>
        <th style="width: 25%; text-align: left;" class="me-padding-top-8">
            <input type="hidden" name="chirSel" value="{{$chirSel}}" onchange="onSelectChir(this.form);">
            <input type="text" name="chir_view" value="{{if $user->_id && !$function->_id}}{{$user}}{{/if}}">
          {{if $canEditPlage}}
            <p><button type="button" class="new me-primary" onclick="PlageConsultation.edit('0', $V(getForm('changeDate').debut));">{{tr}}CPlageconsult-title-create{{/tr}}</button></p>
          {{/if}}
        </th>
        <th style="width: 50%" class="me-padding-top-8 me-consult-semainier-header">
          <a href="#1" onclick="refreshPlanning('prev')">&lt;&lt;&lt;</a>

          Semaine du <span id="debut_periode">{{$debut|date_format:$conf.longdate}}</span> au
          <span id="fin_periode">{{$fin|date_format:$conf.longdate}}</span>
          <input type="hidden" name="debut" class="date" value="{{$debut}}" onchange="refreshPlanning(null, this.value)" />

          <a href="#1" onclick="refreshPlanning('next')">&gt;&gt;&gt;</a>
          <br />
          <a href="#1" onclick="refreshPlanning('today')" class="me-consult-semainier-header-today">{{tr}}Today{{/tr}}</a>
        </th>
        <th style="width: 15%; text-align: right;" class="me-padding-8">
          {{if $function->_id}}
            <button class="new me-tertiary me-dark" type="button" onclick="CreneauConsultation.modalPriseRDVTimeSlot('', '{{$function->_id}}', 0, 1);">
              {{tr}}CPlageconsult-action-Next available time slot|pl{{/tr}} ({{tr}}CFunction{{/tr}})
            </button>
          {{else}}
            <button class="new me-tertiary me-dark" type="button" onclick="CreneauConsultation.modalPriseRDVTimeSlot($V(getForm('changeDate').chirSel),'', 0, 0);">
              {{tr}}CPlageconsult-action-Next available time slot|pl{{/tr}}
            </button>
          {{/if}}

          <button class="lookup me-tertiary me-dark" type="button"
                  onclick="Modal.open('filter_more', {showClose: true, onClose:refreshPlanning, title:'Filtres'})">
            {{tr}}Filter{{/tr}}
          </button>
          <div id="filter_more" style="display: none;">
            {{mb_include module=cabinet template=inc_filter_new_planning}}
          </div>

          {{if $app->user_prefs.dPcabinet_offline_mode_frequency}}
            {{me_button icon="download" label="common-Backup" onclick="PlageConsultation.downloadBackup();"}}
          {{/if}}

          {{me_button icon="download" label="CConsultation-action-Download planning" onclick="downloadPlanningCSV();"}}

          <button type="button" class="help me-tertiary me-dark" onclick="openLegend();">{{tr}}Legend{{/tr}}</button>

          {{me_button icon="print" label="Print" onclick="printPlanning();"}}

          {{if $function->_id}}
              {{me_button icon="print" label="Print" label_suf="(cabinet)" onclick="printPlanning(1);"}}
          {{/if}}
          <br class="me-no-display" />

          {{assign var=btn_attr value=""}}
          {{if !$count_si_desistement}}
              {{assign var=btn_attr value="disabled"}}
          {{/if}}
          {{me_button icon=lookup id="desistement_count" label="CConsultation-si_desistement" attr=$btn_attr label_suf="($count_si_desistement)" onclick="showConsultSiDesistement()"}}

          {{me_dropdown_button button_label=Actions button_icon="opt" button_class="notext me-tertiary"
          container_class="me-dropdown-button-right"}}
        </th>
        <th class="me-padding-top-12">
          <div id="status_cut" onclick="window.cut_consult_id = null; window.copy_consult_id = null; updateStatusCut();"
            style="width: 100px; height: 14px; border: 2px dashed #ddd; font-weight: bold; text-align: center; cursor: pointer;">
          </div>
        </th>
      </tr>
    </table>
  </form>


<div id="planning-plages" class="me-bg-white"></div>

