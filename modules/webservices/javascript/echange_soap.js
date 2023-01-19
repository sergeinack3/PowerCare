/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

EchangeSOAP = {
  viewEchange : function (echange_soap_id) {
    new Url("webservices", "ajax_view_echange_soap")
      .addParam("echange_soap_id", echange_soap_id)
      .requestModal(800, 500);
  },

  changePage : function (page) {
    $V(getForm('listFilter').page, page);
  },

  fillSelect : function(select, dest) {
    var url = new Url("webservices", "ajax_filter_web_func");
    url.addParam("service_demande", select);
    url.addParam("type", dest);

    if (dest == 'fonction') {
      url.addParam("web_service_demande", $V(getForm('listFilter').web_service));
    }

    url.requestUpdate(dest, { onComplete: function() {
      if (dest == 'web_service') {
        EchangeSOAP.fillSelect($V(getForm('listFilter').service), 'fonction');
      }
    }});
  }
}