{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=closeModal value=0}}

<script type="text/javascript">
  callbackModalMessagerie = function(messagerie, object_id) {
    var systemMsg = window.parent.$("systemMsg").update('{{$messages|smarty:nodefaults}}');
    systemMsg.show();
    systemMsg.addClassName('systemmsg-in');
    if (systemMsg.down('div.info'){{if !$closeModal}} && window.parent.$('closeModal') && window.parent.$V(window.parent.$('closeModal')) != 0{{/if}} && !systemMsg.down('div.error')) {
      window.parent.Control.Modal.close();
    }
    else {
      if (object_id && messagerie) {
        if (messagerie == 'internal') {
          var form = getForm('edit_usermessage');
          window.parent.$V(form.usermessage_id, object_id);
          window.parent.Control.Modal.close();
        }
      }
    }
  }
</script>