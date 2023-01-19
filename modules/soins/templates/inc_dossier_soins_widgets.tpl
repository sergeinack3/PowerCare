{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    showScores = function () {
        var url = new Url('soins', 'ajax_vw_fiches');
        url.addParam('sejour_id', '{{$sejour->_id}}');
        url.addParam('digest', '1');

        window.urlScoresDigest = url;
        url.requestModal('90%', '95%', {
            onClose: function () {
                refreshScoresDigest('{{$sejour->_id}}');
            }
        });
    };

    refreshScoresDigest = function (sejour_id) {
        var url = new Url('soins', 'ajax_refresh_scores');
        url.addParam('sejour_id', sejour_id);
        url.requestUpdate('form-scores-digest');
    };
</script>

<table class="main layout">
    <tr>
        <td style="width: 33%;">
            <fieldset class="me-fieldset-tran me-fieldset-widget">
                <legend>
                    <button class="search notext compact me-tertiary" type="button"
                            onclick="Soins.showModalAllTrans('{{$sejour->_id}}')"></button>
                    {{if "soins Observations manual_alerts"|gconf}}
                        {{mb_include module=system template=inc_icon_alerts object=$sejour tag=observation callback="Soins.compteurAlertesObs.curry(`$sejour->_id`)" show_empty=1 show_span=1 img_ampoule="ampoule_rose"}}
                    {{/if}}
                    Transmissions et observations importantes
                </legend>
                <div id="dossier_suivi_lite" class="me-fieldset-content" style="height: 140px; overflow-y: auto;"></div>
            </fieldset>
        </td>

        {{if "forms"|module_active}}
            <td style="width: 33%;">
                <div id="{{$unique_id_widget_forms}}_modal" style="width: 1200px; height: 700px; display: none;"></div>

                <fieldset class="me-fieldset-widget">
                    <legend>
                        <button class="search notext compact me-tertiary" type="button"
                                onclick="ExObject.loadExObjects('{{$sejour->_class}}', '{{$sejour->_id}}', '{{$unique_id_widget_forms}}_modal', 0);
                                  Modal.open('{{$unique_id_widget_forms}}_modal', {showClose: true})"></button>
                        {{tr}}CExClass|pl{{/tr}}
                        <button id="form-scores-digest" class="fa fa-list-alt compact" onclick="showScores();">
                          {{tr}}CExamIgs-scoreIGS-court{{/tr}} : &ndash; / {{tr}}CChungScore-total-court{{/tr}} : &ndash; /
                          {{tr}}CExamGir-score_gir-court{{/tr}}
                        </button>
                    </legend>
                    <div id="{{$unique_id_widget_forms}}" class="me-fieldset-content"
                         style="height: 140px; overflow-y: auto;"></div>
                </fieldset>
            </td>
        {{/if}}

        <td style="width: 33%;">
            <fieldset class="me-fieldset-widget">
                <legend>
                    <button class="search notext compact me-tertiary" type="button"
                            onclick="Soins.openSurveillanceTab();"></button>
                    Surveillance
                </legend>
                <div id="constantes-medicales-widget" class="me-fieldset-content" style="height: 140px;"></div>
            </fieldset>
        </td>
    </tr>
</table>

<div id="dossier_traitement"></div>
