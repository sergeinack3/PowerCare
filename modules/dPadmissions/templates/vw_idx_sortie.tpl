{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admissions  script=admissions}}
{{mb_script module=compteRendu script=document}}
{{mb_script module=compteRendu script=modele_selector}}
{{mb_script module=files       script=file}}
{{mb_script module=planningOp  script=sejour}}
{{mb_script module=planningOp  script=prestations}}
{{mb_script module=planningOp  script=appel}}

{{if "web100T"|module_active}}
  {{mb_script module=web100T script=web100T}}
{{/if}}

{{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
  {{mb_script module=appFineClient  script=appFineClient}}
{{/if}}

<script>
  var sejours_enfants_ids;

  function printAmbu(type) {
    var form = getForm("selType");
    var url = new Url("admissions", "print_ambu");
    url.addParam("date", $V(form.date));
    url.addParam("type", type);
    url.popup(800, 600, "Ambu");
  }

  function printPlanning() {
    var form = getForm("selType");
    var url = new Url("admissions", "print_sorties");
    url.addParam("date", $V(form.date));
    url.addParam("type", $V(form._type_admission));
    url.addParam("service_id", [$V(form.service_id)].flatten().join(","));
    url.addParam("period", $V(form.period));
    url.popup(700, 550, "Sorties");
  }

  function printDHE(type, object_id) {
    var url = new Url("planningOp", "view_planning");
    url.addParam(type, object_id);
    url.popup(700, 550, "DHE");
  }

  function changeEtablissementId(form) {
    $V(form._modifier_sortie, '0');
    var type = $V(form.type);
    submitSortie(form, type);
  }

  function submitMultiple(form) {
    return onSubmitFormAjax(form, reloadFullSorties);
  }

  sortie_preparee = function (sejour_id, value, trigger) {
    var form = getForm("edit_sejour_sortie_preparee");
    $V(form.sejour_id, sejour_id);
    $V(form.sortie_preparee, '' + value);
    $V(form._sortie_preparee_trigger, trigger);
    form.onsubmit();
  };

  function commonParams(url, form) {
    url.addParam("date", $V(form.date))
    .addParam("selSortis", $V(form.selSortis))
    .addParam("type", $V(form._type_admission))
    .addParam("service_id", [$V(form.service_id)].flatten().join(","))
    .addParam("prat_id", $V(form.prat_id))
    .addParam("only_confirmed", $V(form.only_confirmed))
    .addParam("active_filter_services", $V(form.elements['active_filter_services']))
    .addParam("type_pec[]", $V(form.elements["type_pec[]"]), true)
    .addParam("circuit_ambu[]", $V(form.elements["circuit_ambu[]"]), true)
    .addParam("mode_sortie[]", $V(form.elements["mode_sortie[]"]), true)
    .addParam("prestations_p_ids[]", $V(form.prestations_p_ids), true);


    if ($(form.name + '_reglement_dh')) {
      url.addParam('reglement_dh', $V(form.elements['reglement_dh']));
    }
  }

  function reloadFullSorties() {
    var form = getForm("selType");
    var url = new Url("admissions", "httpreq_vw_all_sorties");
    commonParams(url, form);
    url.requestUpdate("allSorties");
    reloadSorties();
  }

  function reloadSorties(page = 0) {
    var form = getForm("selType");
    var url = new Url("admissions", "httpreq_vw_sorties");
    commonParams(url, form);
    url.addParam("order_col", $V(form.order_col));
    url.addParam("order_way", $V(form.order_way));
    url.addParam("page", page);
    url.addParam("period", $V(form.period));
    url.addParam("filterFunction", $V(form.filterFunction));
    url.requestUpdate("listSorties");
  }

  function reloadSortiesDate(elt, date) {
    var form = getForm("selType");
    $V(form.date, date);
    var old_selected = elt.up("table").down("tr.selected");
    old_selected.select('td').each(function (td) {
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
    if ($V(form.selSortis) == 'np') {
      pos = 2;
    }
    else if ($V(form.selSortis) == 'n') {
      pos = 3;
    }
    var td = elt_tr.down("td", pos);
    td.writeAttribute("style", "font-weight: bold");

    reloadSorties();
  }

  function reloadSortieLine(sejour_id) {
    var url = new Url("admissions", "ajax_sortie_line");
    url.addParam("sejour_id", sejour_id);
    url.requestUpdate("CSejour-" + sejour_id);
  }

  function submitSortie(form) {
    if (!Object.isUndefined(form.elements["_sejours_enfants_ids"]) && $V(form._modifier_sortie) == 1) {
      sejours_enfants_ids = $V(form._sejours_enfants_ids);
      sejours_enfants_ids.split(",").each(function (elt) {
        var formSejour = getForm("editFrmCSejour-" + elt);
        if (!Object.isUndefined(formSejour) && formSejour.down("button.tick")) {
          if (confirm('Voulez-vous effectuer dans un même temps la sortie de l\'enfant ' + formSejour.get("patient_view"))) {
            formSejour.down("button.tick").onclick();
          }
        }
      });

      sejours_enfants_ids = undefined;
      return onSubmitFormAjax(form, reloadSortieLine.curry($V(form.sejour_id)));
    }

    if (!Object.isUndefined(sejours_enfants_ids) && sejours_enfants_ids.indexOf($V(form.sejour_id)) != -1) {
      return onSubmitFormAjax(form);
    }

    return onSubmitFormAjax(form, reloadSortieLine.curry($V(form.sejour_id)));
  }

  function confirmation(form, type) {
    if (!checkForm(form)) {
      return false;
    }
    if (confirm('La date de sortie enregistrée est différente de la date prévue, souhaitez-vous confimer la sortie du patient ?')) {
      submitSortie(form, type);
    }
    else {
      sejours_enfants_ids = undefined;
    }
  }

  function confirmation(date_actuelle, date_demain, sortie_prevue, entree_reelle, form) {
    if (entree_reelle == "") {
      if (!confirm('Attention, ce patient ne possède pas de date d\'entrée réelle, souhaitez-vous confirmer la sortie du patient ?')) {
        sejours_enfants_ids = undefined;
        return false;
      }
    }
    if (date_actuelle > sortie_prevue || date_demain < sortie_prevue) {
      if (!confirm('La date de sortie enregistrée est différente de la date prévue, souhaitez-vous confimer la sortie du patient ?')) {
        sejours_enfants_ids = undefined;
        return false;
      }
    }
    submitSortie(form);
  }

  function updateModeSortie(select) {
    var selected = select.options[select.selectedIndex];
    var form = select.form;
    $V(form.elements.mode_sortie, selected.get("mode"));
  }

  function sortBy(order_col, order_way) {
    var form = getForm("selType");
    $V(form.order_col, order_col);
    $V(form.order_way, order_way);
    reloadSorties($V(form.page));
  }

  function changePage(page) {
    let form = getForm("selType");
    $V(form.page, page)
    reloadSorties(page);
  }

  function filterAdm(selSortis) {
    var form = getForm("selType");
    $V(form.selSortis, selSortis);
    reloadFullSorties();
  }

  {{assign var=auto_refresh_frequency value="dPadmissions automatic_reload auto_refresh_frequency_sorties"|gconf}}

  Main.add(function() {
    Admissions.table_id = "listSorties";

    var totalUpdater = new Url("admissions", "httpreq_vw_all_sorties");
    var listUpdater = new Url("admissions", "httpreq_vw_sorties");

    {{if $auto_refresh_frequency != 'never'}}
      Admissions.totalUpdater = totalUpdater.periodicalUpdate('allSorties', {frequency: {{$auto_refresh_frequency}}});
      Admissions.listUpdater = listUpdater.periodicalUpdate('listSorties', {
        frequency: {{$auto_refresh_frequency}},
        onCreate:  function () {
          WaitingMessage.cover($('listSorties'));
          Admissions.rememberSelection();
        }
      });
    {{else}}
      totalUpdater.requestUpdate('allSorties');
      listUpdater.requestUpdate('listSorties', {
        onCreate: function() {
          WaitingMessage.cover($('listSorties'));
          Admissions.rememberSelection();
        }});
    {{/if}}

    $("listSorties").fixedTableHeaders();
    $("allSorties").fixedTableHeaders();
  });
</script>

{{mb_include module=admissions template=inc_prompt_modele type=sortie}}

<form name="edit_sejour_sortie_preparee" method="post" onsubmit="return onSubmitFormAjax(this, reloadFullSorties)">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_sejour_aed" />
  <input type="hidden" name="sejour_id" value="" />
  <input type="hidden" name="sortie_preparee" value="" />
  <input type="hidden" name="_sortie_preparee_trigger" value="" />
</form>

<table class="main">
  <tr>
    <td id="idx_admission_legend" colspan="2">
      <a id="idx_admission_legend_button" href="#legend" onclick="Admissions.showLegend()" class="button search me-tertiary me-dark">Légende</a>
      {{if "astreintes"|module_active}}{{mb_include module=astreintes template=inc_button_astreinte_day date=$date}}{{/if}}
    </td>
    <td id="filter_sortie">
      {{mb_include module=admissions template=inc_admission_filter reload_full_function='reloadFullSorties' reload_lite_function='reloadSorties' view='sorties' table_id='sortie'}}
    </td>
  </tr>
  <tr>
    <td style="width: 250px">
      <div id="allSorties" class="me-align-auto"></div>
    </td>
    <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).previous()); Admissions.updateAdmissionIdxLayout();" style="padding: 0;"></td>
    <td style="width: 100%">
      <div id="listSorties" class="me-align-auto"></div>
    </td>
  </tr>
</table>
