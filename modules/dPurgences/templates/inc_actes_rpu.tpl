{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=object value=$sejour}}
{{assign var=subject value=$sejour}}
{{mb_include module=salleOp template=js_codage_ccam}}

<script>
    loadCodagesCCAM = function(sejour_id, date, from, to) {
      var url = new Url('soins', 'ajax_codages_ccam_sejour');
      url.addParam('sejour_id', sejour_id);
      if (date) url.addParam('date', date);
      if (from) url.addParam('from', from);
      if (to) url.addParam('to', to);
      url.requestUpdate('ccam', {onComplete: function() {
        var url = new Url('ccam', 'updateActsCounter');
        url.addParam('subject_guid', '{{$object->_guid}}');
        url.addParam('type', 'ccam');
        url.requestUpdate('count_ccam_{{$object->_guid}}', {insertion: function(element, content) {
          element.innerHTML = content;
        }});
      }});
    };

  showActesNGAP = function(sejour_id) {
    var url = new Url("cabinet", "httpreq_vw_actes_ngap");
    url.addParam("object_id"   , sejour_id);
    url.addParam("object_class", "CSejour");
    url.addParam('page', '0');
    url.requestUpdate('listActesNGAP');
  };

  loadTarifsSejour = function (sejour_id) {
    var url = new Url("soins", "ajax_tarifs_sejour");
    url.addParam("sejour_id", sejour_id);
    url.requestUpdate("tarif");
  };

  showFraisDivers = function(sejour_id) {
    var url = new Url("urgences", "ajax_show_frais_divers");
    url.addParam("sejour_id", sejour_id);
    url.requestUpdate('fraisdivers');
  };

  reloadDiagnostic = function (sejour_id) {
    var url = new Url("dPsalleOp", "httpreq_diagnostic_principal");
    url.addParam("sejour_id", sejour_id);
    url.requestUpdate("cim");
  };

  Main.add(function () {
    var tab_actes = Control.Tabs.create('tab-actes', false, {
    foldable: true,
    unfolded: true
  });

    showActesNGAP('{{$sejour->_id}}');
    loadCodagesCCAM('{{$sejour->_id}}');
    loadTarifsSejour('{{$sejour->_id}}');
    showFraisDivers('{{$sejour->_id}}');
    reloadDiagnostic('{{$sejour->_id}}');
  });
</script>

<ul id="tab-actes" class="control_tabs">
  <li id="tarif" class="keep_content" style="float: right;"></li>
  <li>
    <a href="#listActesCCAM"{{if $sejour->_ref_actes_ccam|@count == 0}} class="empty"{{/if}}>
      Actes CCAM
      <small id="count_ccam_{{$sejour->_guid}}">({{$sejour->_ref_actes_ccam|@count}})</small>
    </a>
  </li>
  <li>
    <a href="#listActesNGAP"{{if $sejour->_ref_actes_ngap|@count == 0}} class="empty"{{/if}}>
      Actes NGAP
      <small id="count_ngap_{{$sejour->_guid}}">({{$sejour->_ref_actes_ngap|@count}})</small>
    </a>
  </li>
  {{if "dPccam frais_divers use_frais_divers_CSejour"|gconf}}
    <li onmouseup="showFraisDivers('{{$sejour->_id}}')"><a href="#fraisdivers">Frais divers</a></li>
  {{/if}}
  <li><a href="#diagnostics">Diagnostics</a></li>
</ul>

<div id="listActesCCAM" style="display: none;">
  <div id="ccam"></div>
</div>

<div id="listActesNGAP" data-object_id="{{$sejour->_id}}" data-object_class="{{$sejour->_class}}" style="display: none;"></div>

{{if "dPccam frais_divers use_frais_divers_CSejour"|gconf}}
  <div id="fraisdivers" style="display:none"></div>
{{/if}}

<div id="diagnostics" style="display: none;">
  <div id="cim"></div>
</div>
