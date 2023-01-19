{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=offline value=false}}
{{mb_default var=embed value=false}}
{{assign var=forms_limit value=10000}}

{{mb_script module=monitoringPatient script=surveillance_perop         ajax=1}}
{{mb_script module=monitoringPatient script=surveillance_timeline      ajax=1}}
{{mb_script module=monitoringPatient script=surveillance_timeline_item ajax=1}}
{{mb_script module=monitoringPatient script=supervision_graph_defaults ajax=1}}

{{if !$offline}}
    <!-- Fermeture du tableau pour faire fonctionner le page-break -->
    </td>
    </tr>
    </table>
{{/if}}

{{mb_default var=in_modal value=0}}
{{assign var=object value=$sejour->_ref_patient}}

{{if !$offline}}
    <style>
      @media print {
        div.modal_view {
          display: block !important;
          height: auto !important;
          width: 100% !important;
          font-size: 8pt !important;
          left: auto !important;
          top: auto !important;
          position: static !important;
        }
      }
    </style>
{{/if}}

<style>
  thead {
    display: table-header-group;
  }

  tr {
    page-break-inside: avoid;
  }
</style>

<script>
    getDossierSoin = function (sejour_id) {
        return $("dossier-" + sejour_id) || $(document.documentElement);
    };

    printDossierFromSejour = function (sejour_id, date_min, date_max) {
        var dossier = $("dossier-" + sejour_id);
        if (dossier) {
            Element.print(dossier.childElements());
        } else {
            var form = getForm("filterPrint");
            // Génération d'un pdf si ajout des items documentaires
            let c = form.getElementsByTagName('input');
            let checkbox_selected = [];
            for (let i = 0; i < c.length; i++) {
              if (c[i].type === 'checkbox' && c[i].checked && c[i].name) {
                checkbox_selected.push(c[i].name);
              }
            }
            if (form.print_docs.checked) {
                new Url("soins", "print_dossier_soins_docs", "raw")
                    .addParam("sejour_id", sejour_id)
                    .addParam("date_min", date_min)
                    .addParam("date_max", date_max)
                    .addParam("checkbox_selected", JSON.stringify(checkbox_selected))
                    .pop(null, null, null, null, null, {}, DOM.iframe());
            } else {
                window.print();
            }
        }
    };

    togglePrintZone = function (name, sejour_id) {
        var dossier_soin = getDossierSoin(sejour_id);

        var zones = dossier_soin.select("." + name);

        if (zones.length) {
            var first_zone = zones[0];

            // Pas d'appel à toggleClassName pour cause des sous-zones du séjour
            // qui peuvent avoir un statut différent de la classe not-printable
            var action = first_zone.hasClassName("not-printable") ? "removeClassName" : "addClassName";

            zones.invoke(action, "not-printable");

            var display = first_zone.hasClassName("not-printable") ? "hide" : "show";

            // Gestion de l'affichage du tableau qui contient les transmissions et observations
            if (name === "print_transmission" || name === "print_observation") {
                var form = getForm("filterPrint");
                var other_input = form.elements[name === "print_transmission" ? "print_observation" : "print_transmission"];

                if (display === "show" || !other_input.checked) {
                    var table = first_zone.up("table");
                    table[display]();
                    table.removeClassName("not-printable");
                }
            }

            // En plus de ne pas imprimer, on masque dans la popup
            zones.invoke(display);
        }

        var tables = ["print_forms", "print_tasks", "print_prescription", "print_constante", "print_sejour", "print_patient", "print_glycemie"];

        // On remet les page break always sur l'ensemble des blocs
        tables.each(function (table_class) {
            dossier_soin.select('.' + table_class + ':not(li)').invoke('setStyle', {pageBreakAfter: 'always'});
        });

        var last_table_found = false;

        // On retire le page break du dernier bloc visible
        tables.each(function (table_class) {
            if (last_table_found) {
                return;
            }

            var table = dossier_soin.down('.' + table_class + ":not(li):not(.not-printable)");

            if (!table) {
                return;
            }

            last_table_found = true;

            dossier_soin.select('.' + table_class + ':not(li)').invoke('setStyle', {pageBreakAfter: 'auto'});
        });

        // Si le volet actif est masqué, on passe au premier volet affiché
        if (window.tabs_sejour_{{$sejour->_id}}.activeLink.up('li').getStyle('display') === 'none') {
            var tab_activated = false;
            window.tabs_sejour_{{$sejour->_id}}.links.each(function (_link) {
                if (!tab_activated && _link.up('li').getStyle('display') !== 'none') {
                    tab_activated = true;
                    window.tabs_sejour_{{$sejour->_id}}.setActiveTab(_link.getAttribute('href').replace(/#/, ''));
                }
            });
        }
    };

    loadExForms = function (checkbox, sejour_id, date_time_min, date_time_max) {
        if (checkbox._loaded) {
            return;
        }

        var loading = $('forms-loading-' + sejour_id);

        // On affiche le volet Formulaires
        window.tabs_sejour_{{$sejour->_id}}.links.each(function (_link) {
            if (_link.getAttribute('href') === '#forms') {
                _link.up('li').show();
            }
        });

        // Indication du chargement
        loading.setStyle({display: "inline-block"});
        $$("button.print").each(function (e) {
            e.disabled = true;
        });

        ExObject.loadExObjects("CSejour", sejour_id, "ex-objects-" + sejour_id, 3, null, {
            print: 1,
            limit: {{$forms_limit}},
            onComplete: function () {
                loading.hide();
                $$("button.print").each(function (e) {
                    e.disabled = null;
                });
            }
        }, date_time_min, date_time_max);

        checkbox._loaded = true;
    };

    toggleStatusSejour = function (status) {
        var form = getForm("filterPrint");

        var inputs = ["print_tasks", "print_transmission", "print_observation"];

        inputs.each(function (input_name) {
            var input = form.elements[input_name];
            if ((!status && input.checked) || (status && !input.checked)) {
                input.click();
            }
            input.writeAttribute("checked", status);
        });
    };

    toggleStatusPrescription = function (status, sejour_id) {
        var form = getForm("filterPrint");
        $$("table.print_prescription tbody").each(function (tbody) {
            if (tbody.className.indexOf('print_prescription_') >= 0) {
                var name_class = tbody.className.split(' ');
                name_class = name_class[0];
                var input = form.elements[name_class];
                if (input) {
                    if ((!status && input.checked) || (status && !input.checked)) {
                        togglePrintZone(name_class, sejour_id);
                    }
                    input.writeAttribute("checked", status);
                }
            }
        });
        togglePrintZone('print_prescription', sejour_id);
    };

    togglePrescription = function (zone, sejour_id) {
        togglePrintZone(zone, sejour_id);
        var form = getForm("filterPrint");
        var count_show = 0;
        $$("table.print_prescription tbody").each(function (tbody) {
            if (tbody.className.indexOf('print_prescription_') >= 0) {
                var name_class = tbody.className.split(' ');
                name_class = name_class[0];
                var input = form.elements[name_class];
                if (input && input.checked) {
                    count_show++;
                }
            }
        });
        var input_prescription = form.elements['print_prescription'];
        var tableau_visible = $$('table.print_prescription')[0].visible();
        if ((!count_show && tableau_visible) || (count_show && !tableau_visible)) {
            togglePrintZone('print_prescription', sejour_id);
            input_prescription.checked = count_show ? 'checked' : '';
        }
    };

    resetPrintable = function (sejour_id) {
        var dossier_soin = getDossierSoin(sejour_id);
        dossier_soin.down("table.print_patient").removeClassName("not-printable").setStyle({pageBreakAfter: "always"});
        dossier_soin.down("table.print_sejour").removeClassName("not-printable").setStyle({pageBreakAfter: "always"});
        {{if "dPprescription"|module_active}}
        dossier_soin.down("table.print_prescription").removeClassName("not-printable").setStyle({pageBreakAfter: "always"});
        {{/if}}
        dossier_soin.down("table.print_tasks").removeClassName("not-printable").setStyle({pageBreakAfter: "auto"});

        var print_transmissions = dossier_soin.down("table.print_transmission");
        if (print_transmissions) {
            print_transmissions.removeClassName("not-printable").setStyle({pageBreakAfter: "auto"});
        }

        var print_observations = dossier_soin.down("table.print_observation");
        if (print_observations) {
            print_observations.removeClassName("not-printable").setStyle({pageBreakAfter: "auto"});
        }

        dossier_soin.down("table.print_constante").removeClassName("not-printable").setStyle({pageBreakAfter: "auto"});
        dossier_soin.down("table.print_glycemie").removeClassName("not-printable").setStyle({pageBreakAfter: "auto"});
        dossier_soin.down("table.print_block_sheet").removeClassName("not-printable").setStyle({pageBreakAfter: "auto"});
        {{if "monitoringPatient"|module_active && (("monitoringBloc"|module_active && "monitoringBloc general active_graph_supervision"|gconf) ||
    ("monitoringMaternite"|module_active && "monitoringMaternite general active_graph_supervision"|gconf) && $sejour->grossesse_id)}}
        dossier_soin.down("table.print_surveillance_perop").removeClassName("not-printable").setStyle({pageBreakAfter: "auto"});
        {{/if}}
    };

    Main.add(function () {
        window.tabs_sejour_{{$sejour->_id}} = Control.Tabs.create('tabs_dossier_soins_{{$sejour->_id}}');
    });
</script>

<style>
  @media print {
    div.tab_soins {
      display: block !important;
    }
  }
</style>

<table class="main">
    {{if !$embed}}
        <tr class="not-printable">
            <td style="vertical-align: middle;" class="button">
                {{if !$offline}}
                    <form name="filterDatePrint" action="?" method="get" style="float: left;">
                        <input type="hidden" name="m" value="soins"/>
                        <input type="hidden" name="a" value="print_dossier_soins"/>
                        <input type="hidden" name="sejour_id" value="{{$sejour->_id}}"/>
                        <input type="hidden" name="in_modal" value="{{$in_modal}}"/>
                        <input type="hidden" name="embed" value="{{$embed}}"/>
                        <input type="hidden" name="dialog" value="1"/>
                        <label for="entree">Date min
                            {{mb_field object=$filter_date field=entree register=true form="filterDatePrint"}}
                        </label>
                        <label for="sortie">Date max
                            {{mb_field object=$filter_date field=sortie register=true form="filterDatePrint"}}
                        </label>
                        <button class="search" type="button" onclick="this.form.submit();">{{tr}}Filter{{/tr}}</button>
                    </form>
                {{/if}}
                <br/>
                <form name="filterPrint" method="get" style="clear:both;float: left;">
                    <strong>Choix des blocs à imprimer : </strong>
                    <label><input type="checkbox" name="print_patient" checked
                                  onclick="togglePrintZone('print_patient', '{{$sejour->_id}}');"/> {{tr}}CPatient{{/tr}}
                    </label>
                    <label><input type="checkbox" name="print_sejour" checked
                                  onclick="toggleStatusSejour(this.checked); togglePrintZone('print_sejour', '{{$sejour->_id}}');"/> {{tr}}CSejour{{/tr}}
                    </label>
                    {{if "dPprescription"|module_active}}
                        <label>
                            <input type="checkbox" checked name="print_prescription"
                                   onclick="toggleStatusPrescription(this.checked, '{{$sejour->_id}}');"/>
                            {{tr}}CPrescription{{/tr}}
                        </label>
                        {{if $prescription->_ref_lines_med_comments.med|@count || $prescription->_ref_lines_med_comments.comment|@count}}
                            <label>
                                <input type="checkbox" name="print_prescription_med" checked
                                       onclick="togglePrescription('print_prescription_med', '{{$sejour->_id}}');"/>
                                {{tr}}CPrescription._chapitres.med{{/tr}}
                            </label>
                        {{/if}}

                        {{if $prescription->_ref_prescription_line_mixes|@count}}
                            {{foreach from=$prescription->_ref_prescription_line_mixes_by_type key=type item=_prescription_line_mixes}}
                                <label>
                                    <input type="checkbox" name="print_prescription_mix_{{$type}}" checked
                                           onclick="togglePrescription('print_prescription_mix_{{$type}}', '{{$sejour->_id}}');"/>
                                    {{tr}}CPrescriptionLineMix.type_line.{{$type}}{{/tr}}
                                </label>
                            {{/foreach}}
                        {{/if}}
                        {{foreach from=$prescription->_ref_lines_elements_comments key=_chap item=_lines_by_chap}}
                            {{if $_lines_by_chap|@count}}
                                <label>
                                    <input type="checkbox" name="print_prescription_elt_{{$_chap}}" checked
                                           onclick="togglePrescription('print_prescription_elt_{{$_chap}}', '{{$sejour->_id}}');"/>
                                    {{tr}}CCategoryPrescription.chapitre.{{$_chap}}{{/tr}}
                                </label>
                            {{/if}}
                        {{/foreach}}
                        {{if "dmi"|module_active && $prescription->_ref_lines_dmi|@count}}
                            <label>
                                <input type="checkbox" name="print_prescription_dmi" checked
                                       onclick="togglePrescription('print_prescription_dmi', '{{$sejour->_id}}');"/>
                                {{tr}}CCategoryPrescription.chapitre.dmi{{/tr}}
                            </label>
                        {{/if}}
                    {{/if}}
                    <label><input type="checkbox" name="print_tasks" checked
                                  onclick="togglePrintZone('print_tasks', '{{$sejour->_id}}');"/> Tâches</label>
                    <label><input type="checkbox" name="print_transmission" checked
                                  onclick="togglePrintZone('print_transmission', '{{$sejour->_id}}');"/>
                        Transmissions</label>
                    <label><input type="checkbox" name="print_observation" checked
                                  onclick="togglePrintZone('print_observation', '{{$sejour->_id}}');"/>
                        Observations</label>
                    <label><input type="checkbox" name="print_constante" checked
                                  onclick="togglePrintZone('print_constante', '{{$sejour->_id}}');"/> Constantes</label>
                    <label><input type="checkbox" name="print_glycemie"
                                  onclick="togglePrintZone('print_glycemie', '{{$sejour->_id}}');"/> {{tr}}CConstantesMedicales-_glycemie{{/tr}}</label>
                    {{if !$offline}}
                        <label><input type="checkbox" name="print_docs"/> Documents</label>
                    {{/if}}
                    {{if "forms"|module_active}}
                        <label>
                            <input type="checkbox"
                                   onclick="{{if !$offline}} loadExForms(this, '{{$sejour->_id}}', '{{$filter_date->entree}}', '{{$filter_date->sortie}}'); {{/if}} togglePrintZone('print_forms', '{{$sejour->_id}}')"/>
                            {{tr}}CExClass|pl{{/tr}}
                        </label>
                        <div class="loading" style="height: 16px; display: none;" id="forms-loading-{{$sejour->_id}}">
                            Chargement des
                            formulaires en cours
                        </div>
                    {{/if}}

                    {{if $operation->_id}}
                        <label>
                            <input type="checkbox" name="print_block_sheet"
                                   onclick="togglePrintZone('print_block_sheet', '{{$sejour->_id}}');"/> {{tr}}COperation-action-Block
                                sheet{{/tr}}
                        </label>
                        {{if "monitoringPatient"|module_active && "monitoringBloc"|module_active && "monitoringBloc general active_graph_supervision"|gconf}}
                            <label>
                                <input type="checkbox" name="print_surveillance_perop"
                                       onclick="togglePrintZone('print_surveillance_perop', '{{$sejour->_id}}');"/> {{tr}}CSupervisionGraph-Surveillances
                                    perop{{/tr}}
                            </label>
                        {{/if}}
                    {{/if}}
                </form>
                <div style="clear:both;">
                    <a class="button print" onclick="printDossierFromSejour({{$sejour->_id}}, '{{$filter_date->entree}}', '{{$filter_date->sortie}}')">{{tr}}Print{{/tr}}</a>
                    <a class="button download"
                       href="?{{$smarty.server.QUERY_STRING|html_entity_decode}}&offline=1&embed=1&_aio=savefile&limit={{$forms_limit}}"
                       target="_blank">{{tr}}Download{{/tr}}</a>
                    {{if $offline}}
                        <a class="button cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</a>
                    {{/if}}
                </div>
            </td>
        </tr>
    {{/if}}

    {{if !$offline && ($filter_date->entree ||$filter_date->sortie)}}
        <tr>
            <td>
                <div class="small-warning">
                    Le dossier de soins est filtré sur la période suivante:
                    {{mb_include module=system template=inc_interval_date from=$filter_date->entree to=$filter_date->sortie format=$conf.datetime}}
                </div>
            </td>
        </tr>
    {{/if}}
</table>

<ul id="tabs_dossier_soins_{{$sejour->_id}}" class="control_tabs not-printable">
    <li class="print_patient">
        <a href="#patient_{{$sejour->_id}}">{{tr}}CPatient{{/tr}}</a>
    </li>
    <li class="print_sejour">
        <a href="#sejour_{{$sejour->_id}}">{{tr}}CSejour{{/tr}}</a>
    </li>
    <li class="print_constante">
        <a href="#constantes_{{$sejour->_id}}">{{tr}}CConstantesMedicales{{/tr}}</a>
    </li>
    <li class="print_glycemie not-printable" style="display: none;">
        <a href="#glycemie_{{$sejour->_id}}">{{tr}}CConstantesMedicales-_glycemie{{/tr}}</a>
    </li>
    {{if $sejour->_ref_chung_scores|@count}}
        <li class="print_sejour">
            <a href="#scores_{{$sejour->_id}}">{{tr}}CChungScore{{/tr}}</a>
        </li>
    {{/if}}
    {{if $sejour->_ref_exams_igs|@count}}
        <li class="print_sejour">
            <a href="#exams_{{$sejour->_id}}">{{tr}}CExamIgs{{/tr}}</a>
        </li>
    {{/if}}
    {{if "dPprescription"|module_active}}
        <li class="print_prescription">
            <a href="#prescription_{{$sejour->_id}}">{{tr}}CPrescription{{/tr}}</a>
        </li>
    {{/if}}
    <li class="print_tasks">
        <a href="#tasks_{{$sejour->_id}}">{{tr}}CSejourTask{{/tr}}</a>
    </li>
    {{if "forms"|module_active}}
        <li class="print_forms not-printable" style="display: none;">
            <a href="#forms_{{$sejour->_id}}">{{tr}}CExObject{{/tr}}</a>
        </li>
    {{/if}}

    {{if $operation->_id}}
        <li class="print_block_sheet not-printable" style="display: none;">
            <a href="#block_sheet_{{$sejour->_id}}">{{tr}}COperation-action-Block sheet{{/tr}}</a>
        </li>
        {{if "monitoringPatient"|module_active && "monitoringBloc"|module_active && "monitoringBloc general active_graph_supervision"|gconf}}
            <li class="print_surveillance_perop not-printable" style="display: none;">
                <a href="#surveillance_perop_{{$sejour->_id}}">{{tr}}CSupervisionGraph-Surveillances perop{{/tr}}</a>
            </li>
        {{/if}}
    {{/if}}
</ul>

{{if $checkbox_selected|@count == 0 || in_array("print_patient", $checkbox_selected)}}
  <div id="patient_{{$sejour->_id}}" class="tab_soins" style="display: none;">
      {{mb_include module=patients template=CPatient_complete no_header=true embed=true offline_sejour=true}}
  </div>
{{/if}}

{{if $checkbox_selected|@count == 0 || in_array("print_sejour", $checkbox_selected)}}
<div id="sejour_{{$sejour->_id}}" class="tab_soins" style="display: none;">
    {{assign var=object value=$sejour}}

    <div>
        {{mb_include module=planningOp template=CSejour_complete no_header=true see_type_user=true sejour=$object with_thead=true}}
    </div>

    {{if $dossier|@count}}
        <div>
            {{mb_include module=prescription template=inc_vw_dossier_cloture with_thead=true}}
        </div>
    {{/if}}

        {{foreach from=$fiches_anesthesies key=operation_id item=_fiche}}
            <div style="display: none;" id="fiche_anesth_{{$operation_id}}" class="modal_view fiche_anesth">
                {{$_fiche|smarty:nodefaults}}
            </div>
        {{/foreach}}
    </div>
</div>
{{/if}}

{{if $checkbox_selected|@count == 0 || in_array("print_constante", $checkbox_selected)}}
  <div id="constantes_{{$sejour->_id}}" class="tab_soins" style="display: none;">
      <table class="tbl print_constante" style="border: none !important; page-break-after: always;">
          <thead>
          <tr>
              <th class="title">
                  {{$object}}
                  {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
                  <br/>
                  {{$object->_ref_curr_affectation->_ref_lit}}
              </th>
          </tr>
          </thead>
          <tr>
              <td>
                  {{foreach from=$constantes_medicales_grids item=_grid}}
                      {{mb_include module=patients template=print_constantes_vert constantes_medicales_grid=$_grid print=1}}
                  {{/foreach}}
              </td>
          </tr>
      </table>
  </div>

  {{if $sejour->_ref_chung_scores|@count}}
      <div id="scores_{{$sejour->_id}}" class="tab_soins" style="display: none;">
          <table class="tbl print_sejour">
              <tr>
                  <th class="title" colspan="8">{{tr}}CChungScore{{/tr}}</th>
              </tr>
              <tr>
                  <th class="category">{{mb_title class=CChungScore field="total"}}</th>
                  {{foreach from='Ox\Mediboard\Soins\CChungScore'|static:fields item=_field}}
                      <th class="category text ">{{mb_title class=CChungScore field=$_field}}</th>
                  {{/foreach}}
              </tr>
              {{foreach from=$sejour->_ref_chung_scores item=_chung_score}}
                  <tr>
                      <td
                        style="font-weight: bold; font-size: 1.3em; text-align: center;">{{mb_value object=$_chung_score field="total"}}</td>
                      {{foreach from='Ox\Mediboard\Soins\CChungScore'|static:fields item=_field}}
                          <td class="text"
                              style="text-align: center;">{{mb_value object=$_chung_score field=$_field}}</td>
                      {{/foreach}}
                  </tr>
              {{/foreach}}
          </table>
      </div>
  {{/if}}

  {{if $sejour->_ref_exams_igs|@count}}
      <div id="exams_{{$sejour->_id}}" class="tab_soins" style="display: none;">
          <table class="tbl print_sejour">
              <tr>
                  <th class="title" colspan="19">{{tr}}CExamIgs{{/tr}}</th>
              </tr>
              <tr>
                  {{foreach from='Ox\Mediboard\Cabinet\CExamIGS'|static:fields item=_field}}
                      <th class="text">{{mb_label class="CExamIgs" field=$_field}}</th>
                  {{/foreach}}
              </tr>
              {{foreach from=$sejour->_ref_exams_igs item=_igs}}
                  <tr>
                      <th colspan="19" class="title">
                          Score : {{mb_value object=$_igs field="scoreIGS"}} &mdash; Score simplifié
                          : {{mb_value object=$_igs field=simplified_igs}} &mdash;
                          Date : {{mb_value object=$_igs field=date}}
                      </th>
                  </tr>
                  <tr>
                      {{foreach from='Ox\Mediboard\Cabinet\CExamIGS'|static:fields item=_field}}
                          <td class="text {{if $_igs->$_field == ''}}empty{{/if}}"
                              style="text-align: center;">{{mb_value object=$_igs field=$_field}}</td>
                      {{/foreach}}
                  </tr>
              {{/foreach}}
          </table>
      </div>
  {{/if}}
{{/if}}

{{if $checkbox_selected|@count == 0 || in_array("print_glycemie", $checkbox_selected)}}
  <div id="glycemie_{{$sejour->_id}}" class="tab_soins" style="display: none;">
      <table class="tbl print_glycemie" style="border: none !important; page-break-after: always;">
          <thead>
          <tr>
              <th class="title">
                  {{$object}}
                  {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
                  <br/>
                  {{$object->_ref_curr_affectation->_ref_lit}}
              </th>
          </tr>
          <tr>
            <td>
              {{mb_include module=patients template=print_followup_glycemie ref_unit_glycemie=false}}
            </td>
          </tr>
          </thead>
      </table>
  </div>
{{/if}}

{{if "dPprescription"|module_active}}
  {{if $checkbox_selected|@count == 0 || in_array("print_prescription", $checkbox_selected)}}
    <div id="prescription_{{$sejour->_id}}" class="tab_soins" style="display: none;">
        {{assign var=stroke_lines_finished value=1}}

        <table class="tbl print_prescription" style="page-break-after: always;">
            <thead>
            <tr>
                <th class="title">
                    {{$sejour}}
                    {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
                </th>
            </tr>
            </thead>
            <tr>
                <th class="title">
                    Prescription
                </th>
            </tr>
            {{if $checkbox_selected|@count == 0 || in_array("print_prescription_med", $checkbox_selected)}}
              <tbody class="print_prescription_med">
              {{if $prescription->_ref_lines_med_comments.med|@count || $prescription->_ref_lines_med_comments.comment|@count}}
                  <tr>
                      <th>
                          Médicaments
                      </th>
                  </tr>
              {{/if}}
              {{foreach from=$prescription->_ref_lines_med_comments.med key=atc_code item=lines_med_by_atc}}
                  <tr>
                      <th class="section">
                          {{if isset($atc_classes.$atc_code|smarty:nodefaults)}}
                              {{assign var=_libelle_ATC value=$atc_classes.$atc_code}}
                              {{$_libelle_ATC}}
                          {{/if}}
                      </th>
                  </tr>
                  {{foreach from=$lines_med_by_atc item=line_med}}
                      <tr>
                          <td class="text">
                              {{mb_include module=prescription template=inc_print_medicament med=$line_med nodebug=true print=false dci=0}}
                          </td>
                      </tr>
                  {{/foreach}}
              {{/foreach}}

              {{foreach from=$prescription->_ref_lines_med_comments.comment item=line_med_comment}}
                  <tr>
                      <td class="text">
                          {{mb_include module=prescription  template=inc_print_commentaire comment=$line_med_comment nodebug=true}}
                      </td>
                  </tr>
              {{/foreach}}
              </tbody>
            {{/if}}

            {{if $prescription->_ref_prescription_line_mixes|@count}}
                {{foreach from=$prescription->_ref_prescription_line_mixes_by_type key=type item=_prescription_line_mixes}}
                  {{if $checkbox_selected|@count == 0 || in_array("print_prescription_mix_$type", $checkbox_selected)}}
                    <tbody class="print_prescription_mix_{{$type}}">
                    <tr>
                        <th>{{tr}}CPrescriptionLineMix.type_line.{{$type}}{{/tr}}</th>
                    </tr>
                    {{foreach from=$_prescription_line_mixes item=_prescription_line_mix}}
                        <tr>
                            <td class="text">
                                {{mb_include module=prescription template=inc_print_prescription_line_mix perf=$_prescription_line_mix nodebug=true}}
                            </td>
                        </tr>
                    {{/foreach}}
                    </tbody>
                  {{/if}}
                {{/foreach}}
            {{/if}}

            {{foreach from=$prescription->_ref_lines_elements_comments key=_chap item=_lines_by_chap}}
                {{if $checkbox_selected|@count == 0 || in_array("print_prescription_elt_$_chap", $checkbox_selected)}}
                <tbody class="print_prescription_elt_{{$_chap}}">
                {{if $_lines_by_chap|@count}}
                    <tr>
                        <th>
                            {{tr}}CCategoryPrescription.chapitre.{{$_chap}}{{/tr}}
                        </th>
                    </tr>
                {{/if}}
                {{if "dPprescription general display_cat_for_elt"|gconf}}
                    {{foreach from=$_lines_by_chap item=_lines_by_cat}}
                        {{assign var=cat_displayed value="0"}}
                        {{if array_key_exists('element', $_lines_by_cat) || array_key_exists('comment', $_lines_by_cat)}}
                            <tr>
                                <td class="text">
                                    {{if array_key_exists('element', $_lines_by_cat)}}
                                        {{foreach from=$_lines_by_cat.element item=line_elt name=foreach_lines_a}}
                                            {{if $smarty.foreach.foreach_lines_a.first}}
                                                {{assign var=cat_displayed value="1"}}
                                                <strong>{{$line_elt->_ref_element_prescription->_ref_category_prescription->nom}}
                                                    :</strong>
                                            {{/if}}
                                            {{if $line_elt->_chapitre == "dm" && $line_elt->_ref_administrations_dm|@count}}
                                                {{foreach from=$line_elt->_ref_administrations_dm item=line_dmi}}
                                                    {{mb_include module=prescription template=inc_print_dm}}
                                                {{/foreach}}
                                            {{else}}
                                                {{mb_include module=prescription template=inc_print_element elt=$line_elt nodebug=true}}
                                            {{/if}}
                                        {{/foreach}}
                                    {{/if}}
                                    {{if array_key_exists('comment', $_lines_by_cat)}}
                                        {{foreach from=$_lines_by_cat.comment item=line_elt_comment name=foreach_lines_b}}
                                            {{if $smarty.foreach.foreach_lines_b.first && !$cat_displayed}}
                                                <strong>{{$line_elt_comment->_ref_category_prescription->nom}}
                                                    :</strong>
                                            {{/if}}
                                            <li>
                                                ({{$line_elt_comment->_ref_praticien->_view}})
                                                {{$line_elt_comment->commentaire|nl2br}}
                                            </li>
                                        {{/foreach}}
                                    {{/if}}
                                </td>
                            </tr>
                        {{/if}}
                    {{/foreach}}
                {{else}}
                    {{foreach from=$_lines_by_chap item=_lines_by_cat}}
                        {{if array_key_exists('element', $_lines_by_cat)}}
                            {{foreach from=$_lines_by_cat.element item=line_elt}}
                                <tr>
                                    <td class="text">
                                        {{if $line_elt->_chapitre == "dm" && $line_elt->_ref_administrations_dm|@count}}
                                            {{foreach from=$line_elt->_ref_administrations_dm item=line_dmi}}
                                                {{mb_include module=prescription template=inc_print_dm}}
                                            {{/foreach}}
                                        {{else}}
                                            {{mb_include module=prescription template=inc_print_element elt=$line_elt nodebug=true}}
                                        {{/if}}
                                    </td>
                                </tr>
                            {{/foreach}}
                        {{/if}}
                        {{if array_key_exists('comment', $_lines_by_cat)}}
                            {{foreach from=$_lines_by_cat.comment item=line_elt_comment}}
                                <tr>
                                    <td class="text">
                                        <li>
                                            ({{$line_elt_comment->_ref_praticien->_view}})
                                            {{$line_elt_comment->commentaire|nl2br}}
                                        </li>
                                    </td>
                                </tr>
                            {{/foreach}}
                        {{/if}}
                    {{/foreach}}
                {{/if}}
                </tbody>
              {{/if}}
            {{/foreach}}
            {{if "dmi"|module_active && $prescription->_ref_lines_dmi|@count}}
                {{if $checkbox_selected|@count == 0 || in_array("print_prescription_dmi", $checkbox_selected)}}
                <tbody class="print_prescription_dmi">
                <tr>
                    <th>{{tr}}CCategoryPrescription.chapitre.dmi{{/tr}}</th>
                </tr>
                {{foreach from=$prescription->_ref_lines_dmi item=line_dmi}}
                    <tr>
                        <td class="text">
                            {{mb_include module=prescription template=inc_print_dm}}
                        </td>
                    </tr>
                {{/foreach}}
                </tbody>
                {{/if}}
            {{/if}}
        </table>
    </div>
  {{/if}}
{{/if}}

<div id="tasks_{{$sejour->_id}}" class="tab_soins" style="display: none;">
    {{mb_include module=soins template=inc_vw_tasks_sejour mode_realisation=0 readonly=1}}
</div>

{{if "forms"|module_active}}
  {{if $checkbox_selected|@count == 0 || in_array("print_forms", $checkbox_selected)}}
    <div id="forms_{{$sejour->_id}}" class="tab_soins" style="display: none;">
        <table class="main tbl print_forms {{if !$show_forms}}not-printable{{/if}}"
               {{if !$show_forms}}style="display: none;"{{/if}}>
            {{mb_include module=soins template=inc_thead_dossier_soins colspan=1 with_thead=true}}

            <tr>
                <th class="title">{{tr}}CExClass|pl{{/tr}}</th>
            </tr>
            <tr>
                <td>
                    <div id="ex-objects-{{$sejour->_id}}">{{if $offline}}{{$formulaires|smarty:nodefaults}}{{/if}}</div>
                </td>
            </tr>
        </table>
    </div>
  {{/if}}
{{/if}}

{{if $operation->_id}}
    <div id="block_sheet_{{$sejour->_id}}" class="tab_soins" style="display: none; page-break-after: always;">
        <div class="print_block_sheet not-printable">
            {{mb_include module=dPsalleOp template=print_feuille_bloc}}
        </div>
    </div>
    {{if "monitoringPatient"|module_active && "monitoringBloc"|module_active && "monitoringBloc general active_graph_supervision"|gconf}}
        <div id="surveillance_perop_{{$sejour->_id}}" class="tab_soins"
             style="display: none; page-break-after: always;">
            <div class="print_surveillance_perop not-printable">
                {{mb_include module=salleOp template=vw_print_supervision}}
            </div>
        </div>
    {{/if}}
{{/if}}

{{if $embed}}
    {{mb_include module=files template=inc_embed_document_items object=$sejour}}
    <table class="main tbl">
        <tr>
            <th class="title">{{tr}}CConsultAnesth{{/tr}}</th>
        </tr>
        <tr>
            <td>
                {{mb_include module=files template=inc_embed_document_items object=$sejour->_ref_consult_anesth section=true}}
                {{mb_include module=files template=inc_embed_document_items object=$sejour->_ref_consult_anesth->_ref_consultation section=true}}
            </td>
        </tr>
    </table>
    <table class="main tbl">
        <tr>
            <th class="title">{{tr}}CSejour-back-operations{{/tr}}</th>
        </tr>
        {{foreach from=$sejour->_ref_operations item=_object}}
            <tr>
                <th class="category">{{$_object}}</th>
            </tr>
            <tr>
                <td>
                    {{mb_include module=files template=inc_embed_document_items object=$_object section=true}}
                </td>
            </tr>
            {{foreachelse}}
            <tr>
                <td class="empty">{{tr}}COperation.none{{/tr}}</td>
            </tr>
        {{/foreach}}
    </table>
    <table class="main tbl">
        <tr>
            <th class="title">{{tr}}CSejour-back-consultations{{/tr}}</th>
        </tr>
        {{foreach from=$sejour->_ref_consultations item=_object}}
            <tr>
                <th class="category">{{$_object}}</th>
            </tr>
            <tr>
                <td>
                    {{mb_include module=files template=inc_embed_document_items object=$_object section=true}}
                </td>
            </tr>
            {{foreachelse}}
            <tr>
                <td class="empty">{{tr}}CConsultation.none{{/tr}}</td>
            </tr>
        {{/foreach}}
    </table>
{{/if}}

{{if !$offline}}

<!-- re-ouverture du tableau -->
<table>
    <tr>
        <td>
            {{/if}}
