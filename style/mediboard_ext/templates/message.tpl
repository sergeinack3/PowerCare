{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=message ajax=$ajax}}
{{mb_default var=messagerie value=null}}
{{mb_default var=update_placeholders value=true}}

<script>
  function hidePasswordRemainingDaysMessage(input) {
    new Url('admin', 'ajax_dismiss_password_remaining_days').requestUpdate('systemMsg', {onComplete: function() { input.hide(); }});
  }
</script>

{{foreach from=$messages item=_message}}
  <div id="{{$_message->_guid}}" class="{{if $_message->urgence == "urgent"}}small-warning{{else}}small-info{{/if}}">
    <form style="float: right;" name="message-{{$_message->_id}}" method="post" onsubmit="return onSubmitFormAjax(this, {
      onComplete: function(guid) {
          $('{{$_message->_guid}}').hide()
        }
      })">
      {{mb_class object=$acquittal}}

      {{mb_field object=$acquittal field=date value=now hidden=true}}
      {{mb_field object=$acquittal field=message_id value=$_message->_id hidden=true}}
      {{mb_field object=$acquittal field=user_id value=$app->user_id hidden=true}}

      <button type="submit" class="button tick">{{tr}}Ive-understood{{/tr}}</button>
    </form>

    <strong>{{mb_value object=$_message field=titre}}</strong>{{mb_value object=$_message field=corps}}
  </div>
{{/foreach}}

{{assign var=remaining_days value='Ox\Core\CAppUI::checkPasswordRemainingDays'|static_call:null}}
{{if $remaining_days}}
  <div class="info" style="border-bottom: 1px solid #ccc; background-color: #f6f6f6; white-space: normal;">
    {{tr var1=$remaining_days}}common-msg-Your password expires in %d day|pl.{{/tr}}

    <a href="#1" class="button" onclick="popChgPwd()" title="">
      <img src="style/mediboard_ext/images/icons/passwd.png" alt="{{tr}}common-action-Change your password{{/tr}}">
      {{tr}}common-action-Change your password{{/tr}}
    </a>

    <a href="#1" style="float: right;" onclick="hidePasswordRemainingDaysMessage(this.up('div'));">
      {{tr}}Close{{/tr}}
    </a>
  </div>
{{/if}}

{{if 'mediusers'|module_active && 'Ox\Mediboard\Mediusers\CMediusers::mustFillProfessionalContext'|static_call:null}}
  <script>
    Main.add(function() {
      new Url('mediusers', 'ajax_edit_professional_context').requestModal(800, 200, {showClose: false});
    });
  </script>
{{/if}}

{{if 'monitorClient'|module_active && 'Ox\Erp\MonitorClient\CMbMonitorClient::canViewChangelog'|static_call:true}}
  <div class="info" style="border-bottom: 1px solid #ccc; background-color: #f6f6f6; white-space: normal;">
    <a href="#1" onclick="MonitorClient.viewChangelog(true);">
      {{tr}}CMbMonitorClient-msg-It is your first connection after an instance update.{{/tr}}
    </a>

    <a href="#1" style="float: right;" onclick="MonitorClient.dismissUpdateInfoMsg(this.up('div'));">{{tr}}Close{{/tr}}</a>
  </div>
{{/if}}
