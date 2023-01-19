/**
 * @package Mediboard\Smp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

var Action = {  
  module: "smp",
		
  doExport: function (sAction, type) {
    var url = new Url(this.module, "ajax_export_"+type);
    url.addParam("action", sAction);
    url.requestUpdate("export-"+type);
  },
		
  repair: function (sAction) {
    var url = new Url(this.module, "ajax_repair_sejour");
    url.addParam("action", sAction);
    url.requestUpdate("repair");
  },
  
  doDelete: function (sAction) {
    var url = new Url(this.module, "ajax_delete_mvt");
    url.addParam("action", sAction);
    url.requestUpdate("delete-mvt");
  }
}