{{*
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  sendMail = function() {
    var url = new Url("reservation", "ajax_send_mail");
    url.addParam("operation_id", "{{$operation_id}}");
    url.requestUpdate("systemMsg");
  }
</script>
<button type="button" class="mail me-tertiary" onclick="sendMail();">Envoyer l'email</button>
