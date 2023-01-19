{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "planSoins"|module_active}}
    {{mb_script module="planSoins" script="plan_soins"}}
{{/if}}
{{mb_script module="soins" script=soins}}

{{if "dPprescription"|module_active}}
    {{mb_script module="prescription" script="prescription"}}
    {{mb_script module="prescription" script="element_selector"}}
{{/if}}

{{if "dPmedicament"|module_active}}
    {{mb_script module="medicament" script="medicament_selector"}}
    {{mb_script module="medicament" script="equivalent_selector"}}
{{/if}}

{{mb_script module=cim10 script=CIM}}
{{mb_script module="compteRendu" script="document"}}
{{mb_script module="compteRendu" script="modele_selector"}}
{{mb_script module="files"     script="file"}}
{{mb_script module="system"      script="alert"}}
{{mb_script module=files script=file_category}}

{{if "dPImeds"|module_active}}
    {{mb_script module="Imeds" script="Imeds_results_watcher"}}
{{/if}}
{{if "brancardage"|module_active && "brancardage General see_demande_ecap"|gconf && "brancardage General use_brancardage"|gconf}}
    {{mb_script module=brancardage script=brancardage}}
{{/if}}
{{if "syntheseMed"|module_active && "syntheseMed general show_vue_medecin"|gconf}}
    {{mb_script module=syntheseMed script=vue_medecin ajax=true}}
{{/if}}

{{if "oxLaboClient"|module_active && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
  {{mb_script module=oxLaboClient script=oxlaboclient ajax=true}}
{{/if}}

{{assign var=auto_refresh_frequency value="soins Sejour refresh_vw_sejours_frequency"|gconf}}

<style type="text/css">
    tr + tr { /* Avoid page break before a TR following another TR */
        page-break-before: avoid;
    }
</style>

<script>
    showDossierSoins = function (sejour_id, date, default_tab) {
        var url = new Url("soins", "viewDossierSejour");
        url.addParam("sejour_id", sejour_id);
        if (default_tab) {
            url.addParam("default_tab", default_tab);
        }
        url.modal({width: "100%", height: "100%",
            onClose: function () {
                if (window.closeModal) {
                    closeModal();
                }
            }
        });
        modalWindow = url.modalObject;
    };

    refreshLineSejour = function (sejour_id) {
        var url = new Url("soins", "vwSejours");
        url.addParam("sejour_id", sejour_id);
        url.addParam("service_id", "{{$service_id}}");
        url.addParam("function_id", "{{$function->_id}}");
        url.addParam("discipline_id", "{{$discipline->_id}}");
        url.addParam("praticien_id", "{{$praticien->_id}}");
        url.addParam("show_affectation", '{{$show_affectation}}');
        url.addParam("select_view", '{{$select_view}}');
        url.addParam("mode", '{{$mode}}');
        url.addParam("date", '{{$date}}');
        url.requestUpdate("line_sejour_" + sejour_id, {
            onComplete: function () {
                {{if "dPImeds"|module_active}}
                ImedsResultsWatcher.loadResults();
                {{/if}}

                {{if $app->user_prefs.show_file_view}}
                FilesCategory.iconInfoReadFilesGuid('CSejour', ['{{$sejour_id}}']);
                {{/if}}
            }
        });
    };

    Main.add(function () {
        {{if "dPImeds"|module_active}}
        ImedsResultsWatcher.loadResults();
        {{/if}}

        {{if $app->user_prefs.show_file_view}}
        FilesCategory.showUnreadFiles();
        {{/if}}

        {{if !$ecap && $auto_refresh_frequency != 'disabled'}}
        /* Utilisation d'un timeout pour éviter que la page soit rechargée après le 1er chargement */
        setTimeout(function () {
            var url = new Url('soins', 'vwSejours');
            url.addParam('service_id', '{{$service_id}}');
            {{if $select_view}}
            url.addParam('praticien_id', '{{$praticien_id}}');
            url.addParam('function_id', '{{$function_id}}');
            {{/if}}
            url.addParam('sejour_id', '{{$sejour_id}}');
            url.addParam('show_affectation', '{{$show_affectation}}');
            url.addParam('only_non_checked', '{{$only_non_checked}}');
            url.addParam('print', '{{$print}}');
            url.addParam('select_view', '{{$select_view}}');
            url.addParam('mode', '{{$mode}}');
            url.addParam('date', '{{$date}}');
            url.addParam('refresh', true);
            url.periodicalUpdate('idx_sejours', {frequency: {{$auto_refresh_frequency}}});
        }, {{$auto_refresh_frequency}} * 1000
    )
        ;
        {{/if}}

        if (window.Prestations) {
            Prestations.callback = refreshLineSejour;
        }

        {{if "oxLaboClient"|module_active && $labo_alert_by_nda|@count && "oxLaboClient alert_result_critical modal_alert_result_critical"|gconf && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
          OxLaboClient.showModaleCriticalResult('{{$id_sejours}}');
        {{/if}}
    });


    printSejours = function (date) {
        var url = new Url("soins", "vwSejours");
        url.addParam("date", date);
        url.addParam("service_id", "{{$service_id}}");
        url.addParam("praticien_id", "{{$praticien->_id}}");
        url.addParam("function_id", "{{$function->_id}}");
        url.addParam("sejour_id", "{{$sejour_id}}");
        url.addParam("show_affectation", "{{$show_affectation}}");
        url.addParam("only_non_checked", "{{$only_non_checked}}");
        url.addParam("mode", "{{$mode}}");
        url.addParam("print", true);
        url.popup(800, 600);
    };

    function seeVisitesPrat() {
        var url = new Url("soins", "vw_visites_praticien");
        url.addParam("sejours_id[]", {{$visites.all|@json}});
        url.requestModal(600, 400);
    }
</script>

<div id="idx_sejours">
    {{mb_include module=soins template=inc_vw_sejours_global}}
</div>
