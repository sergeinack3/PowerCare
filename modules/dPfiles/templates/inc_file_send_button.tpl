{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "dPfiles CDocumentSender system_sender"|gconf}}

{{if $_doc_item->_send_problem}}
<button class="send-problem {{$notext}}" type="button"
  onclick="alert('{{tr escape=JSAttribute}}CDocumentSender-alert_problem{{/tr}}'
    + '\n\t- ' + '{{$_doc_item->_send_problem|smarty:nodefaults|JSAttribute}}' );">
  {{tr}}Send{{/tr}}
  {{tr}}Impossible{{/tr}}
</button>

{{else}}
<script type="text/javascript">
submitSendAjax = function(button, confirm_auto, onComplete) {
  if (confirm_auto) {
    if (!confirm('{{tr escape=JSAttribute}}CDocumentSender-confirm_auto{{/tr}}')) {
      return;
    };
  }
  $V(button.form._send, true);

  return onSubmitFormAjax(button.form, {
    onComplete : onComplete
  } );
}
</script>

<input type="hidden" name="_send" value="" />
{{if $_doc_item->etat_envoi == "oui"}}
  <button class="send-cancel me-tertiary {{$notext}}" type="button" onclick="submitSendAjax(this, false, function () { {{$onComplete}} } )">
    {{tr}}Unsend{{/tr}}
  </button>
{{elseif $_doc_item->etat_envoi == "obsolete"}}
  <button class="send-again me-tertiary {{$notext}}" type="button" onclick="submitSendAjax(this, false, function () { {{$onComplete}} } )">
    {{tr}}Resend{{/tr}}
  </button>
{{else}}
  {{if $_doc_item->_ref_category->send_auto}}
  <button class="send-auto me-tertiary {{$notext}}" type="button" onclick="submitSendAjax(this, true, function () { {{$onComplete}} } )">
    {{tr}}Send{{/tr}}
  </button>
  {{else}}
  <button class="send me-tertiary {{$notext}}" type="button" onclick="submitSendAjax(this, false, function () { {{$onComplete}} } )">
     {{tr}}Send{{/tr}}
  </button>
  {{/if}}
{{/if}}

{{/if}}

{{/if}}