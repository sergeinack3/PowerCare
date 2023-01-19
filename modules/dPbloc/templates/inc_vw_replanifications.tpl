{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  refreshReplanif = function(date_replanif) {
    var url = new Url("dPbloc", "ajax_vw_operations_replanif");
    url.addParam("date_replanif", date_replanif);
    url.requestUpdate("operations_replanif");
  };
  Main.add(function() {
    refreshReplanif('{{$date_replanif}}');
  });
</script>

<div id="operations_replanif"></div>