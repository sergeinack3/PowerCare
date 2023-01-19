{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
refreshFiches = function(sejour_id, tab) {
  var url = new Url("soins", "ajax_vw_fiches");
  url.addParam("sejour_id", sejour_id);
  if (tab) {
    url.addParam('selected_tab', tab);
  }
  url.requestUpdate('tab-native_views-fiches');
};

ExObject.groupTabsCallback["tab-native_views-fiches-tab"] = function(){
  refreshFiches('{{$object->_id}}');
};
</script>