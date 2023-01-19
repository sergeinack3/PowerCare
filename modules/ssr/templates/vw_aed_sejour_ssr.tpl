{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients   script=pat_selector       ajax=true}}
{{mb_script module=cim10      script=CIM                ajax=true}}
{{mb_script module=planningOp script=protocole_selector ajax=true}}
{{mb_script module=system     script=alert              ajax=true}}

{{if "dPmedicament"|module_active}}
  {{mb_script module=medicament script=medicament_selector ajax=true}}
  {{mb_script module=medicament script=equivalent_selector ajax=true}}
{{/if}}

{{mb_script module=ssr         script=cotation_rhs    ajax=true}}
{{mb_script module=compteRendu script=document        ajax=true}}
{{mb_script module=compteRendu script=modele_selector ajax=true}}
{{mb_script module=files       script=file            ajax=true}}
{{mb_script module=ssr         script=groupe_patient  ajax=true}}

{{if $view_form_ssr}}
  {{mb_include module=ssr template=inc_form_sejour_ssr}}
{{else}}
  <table class="form">
    <tr>
      <th class="title modify text" colspan="8">
        {{mb_include module=system template=inc_object_notes      object=$sejour}}
        {{mb_include module=system template=inc_object_idsante400 object=$sejour}}
        {{mb_include module=system template=inc_object_history    object=$sejour}}

        <a class="action" style="float: right;" title="Modifier uniquement le sejour" href="?m=planningOp&tab=vw_edit_sejour&sejour_id={{$sejour->_id}}">
          {{me_img src="edit.png" icon="edit" class="me-primary" alt="modifier"}}
        </a>
        {{tr}}CSejour-title-modify{{/tr}} {{$sejour}}
        {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
      </th>
    </tr>
  </table>
{{/if}}

{{if !$sejour->_id || !$can->read}}
  {{mb_return}}
{{/if}}

<script>
  updateBilanId = function(bilan_id){
    var formNames = [
      "Edit-CBilanSSR",
      "Planification-CBilanSSR",
      "Create-CBilanSSR"
    ];

    formNames.each(function(formName){
      var form = getForm(formName);
      if (form) {
        $V(form.bilan_id, bilan_id);
      }
    });
  };
  loadDocuments = function(sejour_id) {
    var url = new Url("hospi", "httpreq_documents_sejour");
    url.addParam("sejour_id" , sejour_id);
    url.requestUpdate("docs-ssr");
  };
  var constantesMedicalesDrawn = false;
  refreshConstantesMedicales = function (force) {
    if (!constantesMedicalesDrawn || force) {
      var url = new Url("patients", "httpreq_vw_constantes_medicales");
      url.addParam("patient_id", {{$sejour->_ref_patient->_id}});
      url.addParam("context_guid", "{{$sejour->_guid}}");
      url.addParam("selection[]", ["poids", "taille"]);
      if (window.oGraphs) {
        url.addParam('hidden_graphs', JSON.stringify(window.oGraphs.getHiddenGraphs()));
      }
      url.requestUpdate("constantes");
      constantesMedicalesDrawn = true;
    }
  };
  refreshFicheAutonomie = function(sejour_id) {
    if ($('autonomie').innerHTML == "") {
      var url = new Url("ssr", "vw_fiche_autonomie");
      url.addParam("sejour_id" , sejour_id);
      url.requestUpdate("autonomie");
    }
  };
  refreshHebergement = function(sejour_id) {
    if ($('hebergement').innerHTML == "") {
      var url = new Url("ssr", "vw_form_hebergement");
      url.addParam("sejour_id" , sejour_id);
      url.requestUpdate("hebergement");
    }
  };
  refreshBilanSSR = function(sejour_id) {
    if ($('sejours_ssr')) {
      refreshSejoursSSR(sejour_id);
    }
    else {
      var url = new Url("{{$m}}", "ajax_form_bilan_ssr");
      url.addParam("sejour_id", sejour_id);
      url.requestUpdate("bilan", {
        onComplete: function(){
          refreshSejoursSSR(sejour_id);
        }
      });
    }
  };
  refreshSejoursSSR = function(sejour_id){
    var url = new Url("{{$m}}", "ajax_vw_sejours_patient");
    url.addParam("sejour_id", sejour_id);
    url.requestUpdate("sejours_ssr");
  };

  Main.add(function() {
    var tabs = Control.Tabs.create('tab-sejour-ssr', true);
    (tabs.activeLink.onmousedown || Prototype.emptyFunction)();
  });
</script>

<ul id="tab-sejour-ssr" class="control_tabs me-small">
  {{if $m == "ssr" && !$conf.ssr.recusation.sejour_readonly}}
    <li>
      <a href="#hebergement" onmousedown="refreshHebergement('{{$sejour->_id}}');">{{tr}}ssr-hebergement{{/tr}}</a>
    </li>
  {{/if}}
  {{if $m == "ssr"}}
    <li>
      <a href="#autonomie" onmousedown="refreshFicheAutonomie('{{$sejour->_id}}');">{{tr}}CFicheAutonomie{{/tr}}</a>
    </li>
  {{/if}}
  {{if $can_view_dossier_medical}}
    {{if !$sejour->annule}}
    {{if $m == "ssr"}}
      <li>
        <a href="#constantes" onmousedown="refreshConstantesMedicales();">
          {{tr}}CPatient.surveillance{{/tr}}
        </a>
      </li>
    {{/if}}

    <li>
      <a href="#bilan" onmousedown="refreshBilanSSR('{{$sejour->_id}}');">
        {{if $m == "psy"}}
          {{tr}}CPrescription et Bilan PSY{{/tr}}
        {{else}}
          {{tr}}CPrescription{{/tr}} &amp; {{tr}}CBilanSSR{{/tr}}
        {{/if}}
      </a>
    </li>

    <li>
      <a {{if $bilan->_id && !$bilan->planification}} class="empty" {{/if}}
        href="#planification" onmousedown="Planification.current_m = '{{$current_m}}';Planification.refresh('{{$sejour->_id}}')">
        {{tr}}CBilanSSR-planification{{/tr}}
      </a>
    </li>
    {{if $m =="ssr"}}
      <li>
        <a {{if $bilan->_id && !$bilan->planification}} class="empty" {{/if}}
          href="#cotation-rhs" onmousedown="CotationRHS.refresh('{{$sejour->_id}}')">
          {{tr}}CConsultation-cotation{{/tr}}
        </a>
      </li>
    {{/if}}
    {{/if}}
  {{/if}}
  {{if $m == "ssr"}}
    <li>
      <a href="#docs-ssr" onmousedown="loadDocuments('{{$sejour->_id}}')">
        {{tr}}CMbObject-back-documents{{/tr}}
      </a>
    </li>
  {{/if}}
</ul>

<div id="hebergement" style="display: none;"></div>

<div id="autonomie" style="display: none;"></div>

<div id="bilan" style="display: none;"></div>

<div id="planification" style="display: none;">
  {{mb_include module=ssr template=inc_planification}}
</div>

<div id="cotation-rhs" style="display: none;">
  <div id="cotation-rhs-{{$sejour->_id}}"></div>
</div>

<div id="constantes" style="display: none;"></div>

<div id="docs-ssr" style="display: none;"></div>
