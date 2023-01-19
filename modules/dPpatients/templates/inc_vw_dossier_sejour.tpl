{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  refreshConstantesMedicales = function (context_guid) {
    if (context_guid) {
      var url = new Url("patients", "httpreq_vw_constantes_medicales");
      url.addParam("context_guid", context_guid);
      url.addParam("can_edit", 0);
      url.addParam("can_select_context", 0);
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
    var url = new Url("dPImeds", "httpreq_vw_sejour_results");
    url.addParam("sejour_id", sejour_id);
    url.requestUpdate('result_labo');
  };


  Main.add(function () {
    dossier_sejour_tabs = Control.Tabs.create('dossier_sejour_tab_group', false);
    refreshConstantesHack('{{$object->_id}}');
    {{if $isImedsInstalled}}
    loadResultLabo('{{$object->_id}}');
    {{/if}}
  });
</script>

<ul id="dossier_sejour_tab_group" class="control_tabs">
  <li><a href="#div_sejour">Séjour</a></li>
  <li onmousedown="refreshConstantesHack('{{$object->_id}}')"><a href="#constantes">Constantes</a></li>
  {{if $isImedsInstalled}}
    <li><a href="#result_labo">Labo</a></li>
  {{/if}}
</ul>

<div id="div_sejour" style="display:none;">
  {{mb_include module=planningOp template="CSejour_complete"}}
</div>

<div id="constantes" style="display:none;"></div>

{{if $isImedsInstalled}}
  <div id="result_labo" style="display:none;"></div>
{{/if}}