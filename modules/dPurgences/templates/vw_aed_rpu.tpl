{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$group->service_urgences_id}}
    <div class="small-warning">{{tr}}dPurgences-no-service_urgences_id{{/tr}}</div>
    {{mb_return}}
{{/if}}

{{mb_script module=patients script=patient ajax=true}}
{{mb_script module=files script=file}}

{{assign var=consult value=$rpu->_ref_consult}}

{{mb_script module=urgences script=CCirconstance}}
{{mb_script module=urgences script=urgences}}
{{mb_script module=urgences script=ecg}}
{{mb_script module=compteRendu script=modele_selector}}
{{mb_script module=compteRendu script=document}}

{{if "dPprescription"|module_active}}
    {{mb_script module=prescription script=prescription}}
    {{mb_script module=prescription script=element_selector}}
    {{mb_script module=soins script=soins}}
{{/if}}

{{if "planSoins"|module_active}}
    {{mb_script module=planSoins script=plan_soins}}
{{/if}}

{{if "dPmedicament"|module_active}}
    {{mb_script module=medicament script=medicament_selector}}
    {{mb_script module=medicament script=equivalent_selector}}
{{/if}}

{{assign var=planSoinsInstalled value="planSoins"|module_active}}
{{assign var=vue_condensee_dossier_soins value="soins Other vue_condensee_dossier_soins"|gconf}}

{{mb_include module=soins template=inc_common_forms}}

<style>
    div.shadow {
        box-shadow: 0 8px 5px -3px rgba(0, 0, 0, .4);
    }
</style>

<script>
    Main.add(function () {
        Urgences.pays = '{{$conf.ref_pays}}';
        Urgences._responsable_id = "{{$_responsable_id}}";
        Urgences.tab_mode = {{if $actionType == "tab"}}1{{else}}0{{/if}};
        Urgences.loadRPU('{{$rpu->_id}}', '{{$rpu->sejour_id}}');
    });
</script>

    <script>
        submitSuivi = function (oForm) {
            var sejour_id = $V(oForm.sejour_id);
            onSubmitFormAjax(oForm, function () {
                Control.Modal.close();
                Soins.loadSuivi(sejour_id);
                Soins.loadObservations(sejour_id);
            });
        };

        function refreshConstantesMedicales(context_guid) {
            if (context_guid) {
                var url = new Url("patients", "httpreq_vw_constantes_medicales");
                url.addParam("context_guid", context_guid);
                if (window.oGraphs) {
                    url.addParam('hidden_graphs', JSON.stringify(window.oGraphs.getHiddenGraphs()));
                }
                url.requestUpdate("constantes-medicales");
            }
        }

        var constantesMedicalesDrawn = false;

        function refreshConstantesHack(sejour_id) {
            (function () {
                if (constantesMedicalesDrawn == false && $('constantes-medicales').visible() && sejour_id) {
                    refreshConstantesMedicales('CSejour-' + sejour_id);
                    constantesMedicalesDrawn = true;
                }
            }).delay(0.5);
        }

        function showExamens(consult_id) {
            if (!consult_id) {
                return;
            }

            var url = new Url("urgences", "ajax_show_examens");
            url.addParam("consult_id", consult_id);
            url.requestUpdate("examens");
        }

        function loadDocItems(sejour_id) {
            if (!sejour_id) {
                return;
            }

            new Url("hospi", "httpreq_documents_sejour")
              .addParam("sejour_id", sejour_id)
              .addParam("with_patient", 1)
              .requestUpdate("doc-items");
        }

        function loadActes(sejour_id) {
            if (!sejour_id) {
                return;
            }

            var url = new Url("urgences", "ajax_show_actes");
            url.addParam("sejour_id", sejour_id);
            url.requestUpdate("actes");
        }

        {{if $isPrescriptionInstalled}}
        function reloadPrescription(prescription_id) {
            Prescription.reloadPrescSejour(prescription_id, '', '', null, null, null, '');
        }
        {{/if}}

        function loadResultLabo(sejour_id) {
            var url = new Url("Imeds", "httpreq_vw_sejour_results");
            url.addParam("sejour_id", sejour_id);
            url.requestUpdate('Imeds');
        }

        addScroll = function () {
            var content = $("content-rpu");
            var header = $("header-rpu");
            if (content && header) {
                ViewPort.SetAvlHeight('content-rpu', 1.02);
                content.on('scroll', function () {
                    header.setClassName('shadow', content.scrollTop);
                });
            }
        };

        showDossierSoins = function (sejour_id, date, default_tab) {
            var url = new Url("soins", "viewDossierSejour");
            url.addParam("sejour_id", sejour_id);
            url.addParam("modal", 1);
            if (default_tab) {
                url.addParam("default_tab", default_tab);
            }
            url.requestModal("100%", "100%", {
                onClose: function () {
                    if (window.closeModal) {
                        closeModal();
                    }
                }
            });
            modalWindow = url.modalObject;
        };

        loadSuiviSoins = function () {
            Soins.loadSuiviSoins('{{$sejour->_id}}');
            {{if $vue_condensee_dossier_soins}}
            loadSuiviLite();
            {{/if}}
        };

        loadSuiviLite = function () {
            // Transmissions
            Soins.loadLiteSuivi('{{$sejour->_id}}');

            // Constantes
            var url = new Url("patients", "httpreq_vw_constantes_medicales_widget");
            url.addParam("context_guid", "{{$sejour->_guid}}");
            url.requestUpdate("constantes-medicales-widget");

            // Formulaires
            {{if "forms"|module_active}}
            {{unique_id var=unique_id_widget_forms}}
            ExObject.loadExObjects("{{$sejour->_class}}", "{{$sejour->_id}}", "{{$unique_id_widget_forms}}", 0.5);
            {{/if}}
        };

        Main.add(function () {
            {{if $rpu->_id && $can->edit}}
            Urgences.nb_printers = {{$nb_printers}};

            if (window.DossierMedical) {
                DossierMedical.reloadDossierPatient();
            }
            var tab_sejour = Control.Tabs.create('tab-dossier', false, {
                afterChange: function (container) {
                    switch (container.id) {
                        case 'suivi_clinique':
                            Soins.loadSuiviClinique('{{$rpu->sejour_id}}');
                            break;
                        case 'obs':
                            Soins.loadObservations('{{$rpu->sejour_id}}');
                            break;
                    {{if $rpu->sejour_id}}
                        case 'prescription_sejour':
                            Prescription.reloadPrescSejour('', '{{$rpu->sejour_id}}', '', null, null, null, '');
                            break;
                        case 'dossier_traitement{{if $vue_condensee_dossier_soins}}_compact{{/if}}':
                            loadSuiviSoins();
                            break;
                    {{/if}}
                    {{if $ecg_tabs && $ecg_tabs|@count}}
                      {{foreach from=$ecg_tabs item=ecg_tab}}
                        case 'ecgTab-{{$ecg_tab->_id}}':
                          ECG.getListEcgPdfFromCategory({{$ecg_tab->_id}},'ecgTab-{{$ecg_tab->_id}}','{{$rpu->sejour_id}}');
                          break;
                      {{/foreach}}
                    {{/if}}
                        case 'dossier_suivi':
                            Soins.loadSuivi({{$rpu->sejour_id}});
                            break;
                        case 'constantes-medicales':
                            refreshConstantesHack('{{$rpu->sejour_id}}');
                            break;
                        case 'examens':
                            showExamens('{{$consult->_id}}');
                            break;
                        case 'actes':
                            loadActes('{{$rpu->sejour_id}}');
                            break;
                        case 'Imeds':
                            loadResultLabo('{{$rpu->sejour_id}}');
                            break;
                        case 'doc-items':
                            loadDocItems('{{$rpu->sejour_id}}');
                            break;
                    }
                }
            });

            {{if $isPrescriptionInstalled}}
            Prescription.hide_header = true;
            {{/if}}

            {{if "forms"|module_active}}
            if ($("ex-forms-rpu")) {
                ExObject.loadExObjects("{{$rpu->_class}}", "{{$rpu->_id}}", "ex-forms-rpu", 0.5);
            }
            {{/if}}

            if (document.editAntFrm) {
                document.editAntFrm.type.onchange();
            }

            addScroll();
            {{/if}}
        });

    </script>

    {{if !$rpu->_id || ($can->read && !$can->edit) || ($can->edit && $rpu->mutation_sejour_id)}}
        <div id="rpu_{{$rpu->_id}}"></div>
    {{if $can->edit && $rpu->mutation_sejour_id}}
        <div class="small-info">
            Une mutation du séjour a été effectuée, il est possible de visualiser le dossier de soins en cliquant sur le
            bouton suivant
            <button type="button" class="search"
                    onclick="showDossierSoins('{{$rpu->mutation_sejour_id}}');">{{tr}}soins.button.Dossier-soins{{/tr}}</button>
        </div>
    {{/if}}

    {{if $fragment}}
        <script>
            Main.add(function () {
                showDossierSoins('{{if $rpu->mutation_sejour_id}}{{$rpu->mutation_sejour_id}}{{else}}{{$rpu->sejour_id}}{{/if}}', null, '{{$fragment}}');
            });
        </script>
    {{/if}}

        {{mb_return}}
    {{/if}}

    <!-- Dossier Médical du patient -->
    {{if $can->edit}}
        {{assign var=consult value=$rpu->_ref_consult}}
        <div id="header-rpu" style="position: relative;">
            <div id="patient-banner">
                {{mb_include module=soins template=inc_patient_banner object=$sejour patient=$patient}}
            </div>

            <ul id="tab-dossier" class="control_tabs me-margin-top-0">
                {{mb_include module=forms template=inc_form_tabs_title form_tabs=$form_tabs position="before"}}

                <li><a href="#rpu">{{tr}}soins.tab.rpu{{/tr}}</a></li>

                <li><a href="#suivi_clinique">{{tr}}soins.tab.synthese{{/tr}}</a></li>

                <li><a href="#antecedents">{{tr}}soins.tab.antecedent_and_treatment{{/tr}}</a></li>

                {{if $app->_ref_user->isPraticien()}}
                    <li><a href="#obs">{{tr}}soins.tab.obs{{/tr}}</a></li>
                {{/if}}

                <li>
                    <a href="#constantes-medicales">{{tr}}soins.tab.surveillance{{/tr}}</a>
                </li>

                {{if $isPrescriptionInstalled && $modules.dPprescription->_can->read && !"dPprescription CPrescription prescription_suivi_soins"|gconf}}
                  <li><a
                      href="#dossier_traitement{{if $vue_condensee_dossier_soins}}_compact{{/if}}">
                          {{tr}}soins.tab.suivi_soins{{/tr}}
                    </a>
                  </li>
                    {{mb_include module=dPurgences template=inc_tab_ecg}}
                  <li><a href="#prescription_sejour">{{tr}}soins.tab.prescription{{/tr}}</a></li>
                {{else}}
                  <li><a href="#dossier_suivi">{{tr}}soins.tab.suivi_soins{{/tr}}</a></li>
                    {{mb_include module=dPurgences template=inc_tab_ecg}}
                {{/if}}

                {{if $app->user_prefs.ccam_sejour == 1 }}
                    <li><a href="#actes">{{tr}}soins.tab.cotation-infirmiere{{/tr}}</a></li>
                {{/if}}

                {{if "dPImeds"|module_active}}
                    <li><a href="#Imeds">{{tr}}soins.tab.labo.imeds{{/tr}}</a></li>
                {{/if}}

                <li>
                    <a href="#examens">{{tr}}soins.tab.dossier-medical{{/tr}}</a>
                </li>

                {{if "forms"|module_active}}
                    <li><a href="#ex-forms-rpu">{{tr}}soins.tab.formulaires{{/tr}}</a></li>
                {{/if}}

                <li><a href="#doc-items">{{tr}}soins.tab.documents{{/tr}}</a></li>

                {{mb_include module=forms template=inc_form_tabs_title form_tabs=$form_tabs position="after"}}
            </ul>
        </div>
        <div id="content-rpu">
            <div id="rpu" class="me-padding-2" style="display: none;">
                <div id="rpu_{{$rpu->_id}}"></div>
            </div>

            <div id="suivi_clinique" style="display: none"></div>

            <div id="antecedents" style="display: none">
                {{assign var="current_m"  value="dPurgences"}}
                {{assign var="_is_anesth" value="0"}}
                {{assign var=sejour_id    value=""}}

                {{if $patient->_ref_dossier_medical && $patient->_ref_dossier_medical->_id && !$patient->_ref_dossier_medical->_canEdit}}
                    {{mb_include module=dPpatients template=CDossierMedical_complete object=$patient->_ref_dossier_medical}}
                {{else}}
                    {{mb_include module=cabinet template=inc_ant_consult chir_id=$app->user_id show_header=0}}
                {{/if}}
            </div>

            {{if $app->_ref_user->isPraticien()}}
                <div id="obs" style="display: none;"></div>
            {{/if}}

            <div id="constantes-medicales" style="display:none"></div>
            <div id="ex-forms-rpu" style="display: none"></div>

            <div id="examens" style="display:none">
                <div class="small-info">
                    Aucune prise en charge médicale
                </div>
            </div>

            {{if $app->user_prefs.ccam_sejour == 1 }}
                <div id="actes" style="display: none;"></div>
            {{/if}}

            {{if $isPrescriptionInstalled && $modules.dPprescription->_can->read && !"dPprescription CPrescription prescription_suivi_soins"|gconf}}
                <div id="prescription_sejour" style="display: none;">
                    <div class="small-info">
                        Aucune prescription
                    </div>
                </div>
                <div id="dossier_traitement{{if $vue_condensee_dossier_soins}}_compact{{/if}}">
                    {{if $vue_condensee_dossier_soins}}
                        {{mb_include module=soins template=inc_dossier_soins_widgets}}
                    {{/if}}
                </div>
            {{else}}
                <div id="dossier_suivi" style="display:none"></div>
            {{/if}}

          {{if $ecg_tabs && $ecg_tabs|@count}}
            {{foreach from=$ecg_tabs item=ecg_tab}}
              <div id="ecgTab-{{$ecg_tab->_id}}" style="display:none" class="me-no-align me-overflow-hidden">
              </div>
            {{/foreach}}
          {{/if}}

            {{if "dPImeds"|module_active}}
                <div id="Imeds" style="display: none;"></div>
            {{/if}}

            <div id="doc-items" style="display: none;"></div>

            {{mb_include module=forms template=inc_form_tabs_content form_tabs=$form_tabs object=$rpu}}
        </div>
    {{/if}}
