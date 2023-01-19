/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

SOAP = {
  connexion: function (exchange_source_name) {
    new Url("webservices", "ajaxConnexionSOAP")
      .addParam("exchange_source_name", exchange_source_name)
      .requestModal(600);
  },

  getFunctions: function (exchange_source_name, form) {
    new Url("webservices", "ajaxGetFunctionsSOAP")
      .addParam("form_name", form.getAttribute("name"))
      .addParam("exchange_source_name", exchange_source_name)
      .requestModal(600);
  },
};
