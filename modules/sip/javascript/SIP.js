/**
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SIP = {
  module: "sip",

  doExport: function (sAction, type) {
    var url = new Url(this.module, "ajax_export_"+type);
    url.addParam("action", sAction);
    url.requestUpdate("export-"+type);
  },

  findCandidates: function(form) {
    return Url.update(form, "find_candidates");
  },

  patient_identity_consumer: function(form) {
    return Url.update(form, "patient_identity_consumer");
  }
}