/**
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * JS function Sender SOAP EAI
 */
SenderSOAP = {  
  dispatch : function(sender_soap_id, message) {
    new Url("webservices", "ajax_dispatch_event")
      .addParam("sender_soap_id", sender_soap_id)
      .addParam("message", message)
      .requestUpdate("CSenderSOAP-utilities_dispatch");
  }
};