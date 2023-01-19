{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  refreshListMA = function(page) {
    var url = new Url("admin", "ajax_list_medical_access");
    url.addParam("guid", "{{$guid}}");
    url.addParam("page", page);
    url.requestUpdate('result_log_access_{{$guid}}');
  };

  Main.add(function() {
    refreshListMA({{$page}});
  });
</script>


<div id="result_log_access_{{$guid}}">

</div>
