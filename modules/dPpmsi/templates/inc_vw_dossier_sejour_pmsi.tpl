{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=prescription_active value=0}}

{{if "dPprescription"|module_active}}
    {{assign var=prescription_active value=1}}

    {{mb_script module=prescription script=prescription}}
{{/if}}

<script>
    refreshConstantesMedicales = function (context_guid) {
        if (context_guid) {
            var url = new Url("patients", "httpreq_vw_constantes_medicales");
            url.addParam("context_guid", context_guid);
            url.addParam("can_edit", 0);
            url.addParam("can_select_context", 0);
            url.addParam('paginate', 1);
            url.addParam('view', 'pmsi');
            if (window.oGraphs) {
                url.addParam('hidden_graphs', JSON.stringify(window.oGraphs.getHiddenGraphs()));
            }
            url.requestUpdate("constantes");
        }
    };

    constantesMedicalesDrawn = false;
    refreshConstantesHack = function (sejour_id) {
        (function () {
            if (constantesMedicalesDrawn == false && $('constantes').visible() && sejour_id) {
                refreshConstantesMedicales('CSejour-' + sejour_id);
                constantesMedicalesDrawn = true;
            }
        }).delay(0.5);
    };

    loadResultLabo = function (sejour_id) {
        var url = new Url("Imeds", "httpreq_vw_sejour_results");
        url.addParam("sejour_id", sejour_id);
        url.requestUpdate('result_labo');
    };

    refreshPrescription = function (sejour_id) {
        if (!window.Prescription) {
            return;
        }

        Prescription.reloadPrescSejour(null, sejour_id);
    };

    refreshAntecedents = function (sejour_id) {
        new Url('cabinet', 'listAntecedents')
            .addParam('sejour_id', sejour_id)
            .addParam('readonly', 1)
            .requestUpdate('antecedents')
    };

    Main.add(function () {
        if (window.Prescription) {
            Prescription.readonly = 1;
        }
        Control.Tabs.create("dossier_sejour_tab_group", true, {
            afterChange: function (container) {
                switch (container.id) {
                    case "div_patient":
                        PMSI.loadPatient(null, '{{$object->_id}}');
                        break;
                    case "constantes":
                        refreshConstantesHack('{{$object->_id}}');
                        break;
                    case "prescription_sejour":
                        refreshPrescription('{{$object->_id}}');
                        break;
                    case "antecedents":
                        refreshAntecedents('{{$object->_id}}');
                    default:
                }
            }
        });
        PMSI.loadPatient(null, {{$object->_id}});
        refreshConstantesHack('{{$object->_id}}');
        {{if $isImedsInstalled}}
        loadResultLabo('{{$object->_id}}');
        {{/if}}
    });

</script>

<table class="main layout">
    <tr>
        <td style="white-space: nowrap" class="narrow">
            <ul id="dossier_sejour_tab_group" class="control_tabs_vertical">
                {{mb_include module=forms template=inc_form_tabs_title form_tabs=$form_tabs position="before"}}

                <li><a href="#div_patient">Identité du patient</a></li>
                <li><a href="#div_sejour">Séjour</a></li>
                <li><a href="#constantes">Constantes</a></li>
                {{if $prescription_active}}
                    <li><a href="#prescription_sejour">{{tr}}CPrescription{{/tr}}</a></li>
                {{/if}}
                {{if $isImedsInstalled}}
                    <li><a href="#result_labo">Labo</a></li>
                {{/if}}
                <li><a href="#antecedents">{{tr}}soins.tab.antecedent_and_treatment{{/tr}}</a></li>
                {{if $sejour && $sejour->grossesse_id}}
                    <li><a href="#grossesse">Grossesse</a></li>
                {{/if}}
                {{if $naissance && $naissance->_id}}
                    {{if $sejour_maman->grossesse_id}}
                        <li><a href="#grossesse">Grossesse</a></li>
                    {{/if}}
                    <li><a href="#naissance">Naissance</a></li>
                {{/if}}

                {{mb_include module=forms template=inc_form_tabs_title form_tabs=$form_tabs position="after"}}
            </ul>
        </td>
        <td>
            <div id="div_patient" style="display:none;"></div>
            <div id="div_sejour" style="display:none;">
                {{mb_include module=planningOp template="CSejour_complete"}}
            </div>
            <div id="constantes" style="display:none;"></div>
            {{if $prescription_active}}
                <div id="prescription_sejour" style="display: none;"></div>
            {{/if}}
            {{if $isImedsInstalled}}
                <div id="result_labo" style="display:none;"></div>
            {{/if}}
            <div id="antecedents" style="display: none;"></div>
            {{if $sejour->grossesse_id}}
                <div id="grossesse" style="display:none;">
                    {{mb_include module=maternite template="CGrossesse_view" object=$sejour->_ref_grossesse}}
                </div>
            {{/if}}
            {{if $naissance && $naissance->_id}}
                {{if $sejour_maman->grossesse_id}}
                    <div id="grossesse" style="display:none;">
                        {{mb_include module=maternite template="CGrossesse_view" object=$sejour_maman->_ref_grossesse}}
                    </div>
                {{/if}}
                <div id="naissance" style="display:none;">
                    {{mb_include module=maternite template="CNaissance_view" object=$naissance show_edit=false}}
                </div>
            {{/if}}

            {{mb_include module=forms template=inc_form_tabs_content form_tabs=$form_tabs object=$sejour readonly=true}}
        </td>
    </tr>
</table>
