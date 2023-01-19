{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    UserEmail.refreshList('{{$account->_id}}', '{{$selected_folder}}');
  });
</script>

<div class="me-margin-16">
  <button class="button oneclick me-primary" onclick="UserEmail.getLastMessages('{{$account->_id}}');">
    <i class="msgicon fas fa-sync"></i>
    {{tr}}CUserMail-button-getNewMails{{/tr}}
  </button>
  <button type="button" id="btn_create_folder" title="{{tr}}CUserMailFolder-action-new-desc{{/tr}}" onclick="UserEmail.editFolder('{{$account->_id}}', null);">
    <span class="fa-stack fa" style="display: inline-block; width: 14px; height: 12px;">
      <i class="msgicon fa fa-folder fa-stack-1x" style="top: -5px;"></i>
      <i class="fa fa-plus fa-stack-1x" style="font-size: 0.55em; color: #fff; top: -4px; left: 1px; width: 12px; height: 12px;"></i>
    </span>
    {{tr}}CUserMailFolder-action-new{{/tr}}
  </button>
  <button class="button singleclick" onclick="UserEmail.markallAsRead('{{$account->_id}}')">
    <i class="msgicon fa fa-eye"></i>
    {{tr}}CUserMail-option-allmarkasread{{/tr}}
  </button>
</div>
{{tr}}CUserMail-last-check{{/tr}} : {{$account->last_update|date_format:"%A %d %B %Y %H:%M"}}

<div id="externalMessages" style="position: relative;">
  <section style="position: absolute; width: 280px; left: 0;" id="folders">
    {{mb_include module=messagerie template=inc_mail_folders}}
  </section>

  <section style="position: absolute; height: 80%; width: 75%; left: 24%; top: 20%;">
    <div id="list-messages">

    </div>
  </section>
</div>
