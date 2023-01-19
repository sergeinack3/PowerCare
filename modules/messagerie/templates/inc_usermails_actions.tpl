{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<input type="checkbox" value="" onclick="UserMessage.toggleSelect(this);"/>

<button type="button" title="{{tr}}CUserMessage-title-create{{/tr}}" onclick="UserEmail.edit();" class="me-primary">
  <i class="msgicon fa fa-envelope"></i>
  {{tr}}New{{/tr}}
</button>

<button type="button" title="{{tr}}Delete{{/tr}}" onclick="UserEmail.action('delete');">
  <i class="msgicon fas fa-trash-alt"></i>
  {{tr}}Delete{{/tr}}
</button>

{{if $type == 'inbox'}}
  <button type="button" title="{{tr}}CUserMail-title-archive{{/tr}}" onclick="UserEmail.action('archive');">
    <i class="msgicon fa fa-archive"></i>
    {{tr}}CUserMail-title-archive{{/tr}}
  </button>
{{/if}}

{{if $type == 'archived'}}
  <button type="button" title="{{tr}}CUserMessageDest-title-to_archive-1{{/tr}}" onclick="UserEmail.action('unarchive');">
    <i class="msgicon fa fa-inbox"></i>
    {{tr}}CUserMail-title-unarchive{{/tr}}
  </button>
{{/if}}

<button id="btn_move_mails" type="button" title="{{tr}}CUserMail-action-move{{/tr}}" onclick="UserEmail.selectParentFolder('{{$account_pop->_id}}');">
  <span class="fa-stack fa" style="width: 12px; height: 12px;">
    <i class="msgicon fa fa-folder fa-stack-1x" style="top: -6px;"></i>
    <i class="fas fa-long-arrow-alt-right fa-stack-1x" style="color: #fff; font-size: 0.65em; top: -6px;"></i>
  </span>
  {{tr}}CUserMail-action-move{{/tr}}
</button>

{{if $type == 'inbox'}}
  <button type="button" title="{{tr}}CUserMail-title-read{{/tr}}" onclick="UserEmail.action('mark_read');">
    <i class="msgicon fa fa-eye"></i>
    {{tr}}CUserMail-title-read{{/tr}}
  </button>

  <button type="button" title="{{tr}}CUserMail-title-unread{{/tr}}" onclick="UserEmail.action('mark_unread');">
    <i class="msgicon fa fa-eye-slash"></i>
    {{tr}}CUserMail-title-unread{{/tr}}
  </button>

  <button type="button" title="{{tr}}CUserMail-title-favour{{/tr}}" onclick="UserEmail.action('favour');">
    <i class="msgicon fa fa-star"></i>
    {{tr}}CUserMail-title-favour{{/tr}}
  </button>
{{/if}}

{{if $type == 'favorites'}}
  <button type="button" title="{{tr}}CUserMail-title-unfavour{{/tr}}" onclick="UserEmail.action('unfavour');">
    <i class="msgicon far fa-star"></i>
    {{tr}}CUserMail-title-unfavour{{/tr}}
  </button>
{{/if}}

{{if $type == 'drafts' && $account_smtp->asynchronous == '1'}}
  <button type="button" title="{{tr}}CUserMail-title-reset_retries{{/tr}}" onclick="UserEmail.action('reset_retries');">
    <i class="msgicon far fa-circle-notch"></i>
    {{tr}}CUserMail-title-reset_retries{{/tr}}
  </button>
{{/if}}
{{if $type != 'drafts'}}
    <button type="button" title="{{tr}}Print{{/tr}}" onclick="UserEmail.print();">
        <i class="msgicon fa fa-print"></i>
        {{tr}}Print{{/tr}}
    </button>
{{/if}}
