{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=unique_id value=''|uniqid}}

<script type="text/javascript">
refreshConstantesMedicales{{$unique_id}} = function(context_guid) {
  var url = new Url("patients", "httpreq_vw_constantes_medicales");
  url.addParam("context_guid", context_guid);

  {{assign var=rel_patient value=$object->loadRelPatient()}}
  url.addParam("patient_id", '{{$rel_patient->_id}}');

  url.addParam("readonly", '0');
  //url.addParam("selected_context_guid", context_guid);
  url.addParam("paginate", window.paginate || 0);
  url.addParam("view", 'simple');
  if (window.oGraphs) {
    url.addParam('hidden_graphs', JSON.stringify(window.oGraphs.getHiddenGraphs()));
  }
  url.addParam('unique_id', '{{$unique_id}}');
  url.addParam('iframe', 1);
  url.requestIframe("tab-native_views-constantes", function() {
    loadConstantesMedicales = window.parent.refreshConstantesMedicales{{$unique_id}};
  });
};

ExObject.groupTabsCallback["tab-native_views-constantes-tab"] = function() {
  refreshConstantesMedicales{{$unique_id}}('{{$object->_guid}}');
};
</script>