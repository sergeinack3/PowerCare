{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  callbackEditSMimeKey = function() {
    var systemMsg = window.parent.$("systemMsg").update('{{$messages|smarty:nodefaults}}');
    systemMsg.show();
    window.parent.loadSMimeKey('{{$source_id}}');
  };
</script>