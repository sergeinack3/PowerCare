{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=messagerie script=Messagerie}}
{{mb_script module=messagerie script=UserEmail}}
{{assign var=selected_account value='internal'}}

{{if 'Ox\Core\CAppUI::isCabinet'|static_call:null && 'oxCabinet'|module_active}}
  {{mb_include module=oxCabinet template=inc_vw_event_alerts_counter}}
{{/if}}

<script type="text/javascript">
  selectInternalMessagerie = function(user_id) {
    var url = new Url('messagerie', 'vw_list_internalMessages');
    url.addParam('user_id', user_id);
    url.requestUpdate('account');
  };

  newMessage = function(account_id, contact_support_ox, context, mail_subject) {
    var url = new Url('messagerie', 'ajax_edit_usermail');
    url.addParam('account_id', account_id);
    url.addParam('contact_support_ox', contact_support_ox);
    url.addParam('context', context);
    url.addParam('mail_subject', mail_subject);
    url.modal({width: -40, height: -40});
    url.modalObject.observe('afterClose', function() {
      UserEmail.refreshList()
    });
  };

  saveChooseEmailAccount = function (email_account) {
    var form = getForm("editChooseEmailAccount");
    $V(form.elements["pref[chooseEmailAccount]"], email_account);
    return onSubmitFormAjax(form);
  };

  Main.add(function() {
    {{if !$contact_support_ox && (($mssante_account && $app->user_prefs.chooseEmailAccount == "mssante") || ($apicrypt_account && $app->user_prefs.chooseEmailAccount == "apicrypt"))}}
      {{if $mssante_account}}
        {{assign var=selected_account value=$mssante_account->_guid}}
        Account.select('{{$mssante_account->_id}}');
      {{elseif $apicrypt_account}}
        {{assign var=selected_account value=$apicrypt_account->_guid}}
        UserEmail.refreshAccount('{{$apicrypt_account->_id}}');
      {{/if}}
    {{elseif $contact_support_ox || ($pop_accounts|@count != 0 && $app->user_prefs.chooseEmailAccount == "perso")}}
      {{assign var=pop_account value=$pop_accounts|@first}}
      {{assign var=selected_account value=$pop_account->_guid}}
      UserEmail.refreshAccount('{{$pop_account->_id}}');
      {{if $contact_support_ox}}
        newMessage('{{$pop_account->_id}}', '{{$contact_support_ox}}', '{{$context}}', '{{$mail_subject}}');
      {{/if}}
    {{elseif $user->_id == $selected_user->_id && 'messagerie access allow_internal_mail'|gconf && !$contact_support_ox}}
      selectInternalMessagerie('{{$selected_user->_id}}');
    {{/if}}

    $$('input[name="selected_account"][value="{{$selected_account}}"]')[0].checked = true;
  });
</script>

<!-- Formulaire de sauvegarde du choix du compte de messagerie par défaut -->
<form name="editChooseEmailAccount" method="post">
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_preference_aed" />
  <input type="hidden" name="user_id" value="{{$app->user_id}}" />
  <input type="hidden" name="pref[chooseEmailAccount]" value="" />
</form>

<div id="accounts" style="text-align: center;" class="me-margin-top-8 me-margin-bottom-8">
  <span style="float: left;">
    <form onsubmit="return checkForm(this.form)" method="get" name="selectUser">
      <input type="hidden" name="m" value="{{$m}}"/>
      <input type="hidden" name="tab" value="{{$tab}}"/>
      <label>{{tr}}common-Available user|pl{{/tr}} :
      <select name="selected_user" onchange="this.form.submit()">
        <option value="">{{tr}}CMediusers-action-Select Mediboard User|pl{{/tr}}</option>
        {{foreach from=$users_list item=_user}}
          <option value="{{$_user->_id}}" {{if $selected_user->_id == $_user->_id}}selected="selected" {{/if}}>{{$_user}}</option>
        {{/foreach}}
      </select>
      </label>
    </form>
  </span>

  {{if 'messagerie access allow_external_mail'|gconf}}
    <span style="float: right;">
      <button type="button" onclick="Messagerie.manageAccounts();">
        <i class="msgicon fas fa-cog"></i>
        {{tr}}common-action-Account management{{/tr}}
      </button>
    </span>
  {{/if}}

  <span>
    {{if $user->_id == $selected_user->_id && 'messagerie access allow_internal_mail'|gconf}}
      <span class="circled">
        <input type="radio" name="selected_account" onclick="saveChooseEmailAccount('interne'); selectInternalMessagerie('{{$selected_user->_id}}');"
               value="internal" {{if $app->user_prefs.chooseEmailAccount == "interne"}}checked{{/if}}/>
        <i class="fa fa-users msgicon"></i>
        {{tr}}module-messagerie-long{{/tr}}
        <span class="msg-counter" id="internal-counter"{{if !$internal_mails_unread}} style="display: none;"{{/if}}>{{$internal_mails_unread}}</span>
      </span>
    {{/if}}

    {{if 'messagerie access allow_external_mail'|gconf}}
      {{foreach from=$pop_accounts item=_account name=external_mails}}
        <span class="circled">
          <label>
            <input type="radio" name="selected_account" onclick="saveChooseEmailAccount('perso'); UserEmail.refreshAccount('{{$_account->_id}}');"
                   value="{{$_account->_guid}}" {{if $app->user_prefs.chooseEmailAccount == "perso" && $smarty.foreach.external_mails.first}}checked{{/if}}/>
            <i class="fa fa-envelope msgicon"></i>
            {{$_account->libelle}}
            <span class="msg-counter" id="{{$_account->_guid}}-counter"{{if !$_account->_unread_messages}} style="display: none;"{{/if}}>{{$_account->_unread_messages}}</span>
          </label>
        </span>
      {{/foreach}}
    {{/if}}

    {{if $apicrypt_account}}
      <span class="circled">
        <label>
          <input type="radio" name="selected_account" onclick="saveChooseEmailAccount('apicrypt'); UserEmail.refreshAccount('{{$apicrypt_account->_id}}');"
                 value="{{$apicrypt_account->_guid}}" {{if $app->user_prefs.chooseEmailAccount == "apicrypt"}}checked{{/if}}/>
          <img title="Apicrypt" style="width: 16px; height: 16px;" src="modules/apicrypt/images/icon.png">
          {{$apicrypt_account->libelle}}
          <span class="msg-counter" id="{{$apicrypt_account->_guid}}-counter"{{if !$apicrypt_account->_unread_messages}} style="display: none;"{{/if}}>{{$apicrypt_account->_unread_messages}}</span>
        </label>
      </span>
    {{/if}}

    {{if $mssante_account}}
      {{mb_script module=mssante script=Folder ajax=1}}
      {{mb_script module=mssante script=Message ajax=1}}
      {{mb_script module=mssante script=Account ajax=1}}
      <span class="circled">
        <label>
          <input type="radio" name="selected_account" onclick="saveChooseEmailAccount('mssante'); Account.select('{{$mssante_account->_id}}');"
                 value="{{$mssante_account->_guid}}" {{if $app->user_prefs.chooseEmailAccount == "mssante"}}checked{{/if}}/>
          <img title="MSSante" src="modules/mssante/images/icon_min.png">
          MSSanté
          <span class="msg-counter" id="{{$mssante_account->_guid}}-counter"{{if !$mssante_account->_unread_messages}} style="display: none;"{{/if}}>{{$mssante_account->_unread_messages}}</span>
        </label>
      </span>
    {{/if}}

    {{if "medimail"|module_active}}
      {{mb_script module=medimail script=Medimail ajax=1}}
      {{if $medimail_account && $medimail_account->_id}}
        <span class="circled">
          <label>
            <input type="radio" name="selected_account" onclick="Medimail.selectAccount('{{$medimail_account->_id}}');"/>
            <img src="modules/medimail/images/icon_min.png" title="Medimail">
            {{if $medimail_account->libelle}}{{$medimail_account->libelle}}{{else}}{{tr}}CMedimailAccount.type.nominative{{/tr}}{{/if}}
            <span class="msg-counter" id="{{$medimail_account->_guid}}-counter"{{if !$medimail_account->_unread_messages}} style="display: none;"{{/if}}>{{$medimail_account->_unread_messages}}</span>
          </label>
        </span>
      {{/if}}

      {{if $medimail_account_application && $medimail_account_application->_id}}
        <span class="circled">
          <label>
            <input type="radio" name="selected_account" onclick="Medimail.selectAccount('{{$medimail_account_application->_id}}');"/>
            <img src="modules/medimail/images/icon_min.png" title="Medimail">
            {{if $medimail_account_application->libelle}}{{$medimail_account_application->libelle}}{{else}}{{tr}}CMedimailAccount.type.applicative{{/tr}}{{/if}}
            <span class="msg-counter" id="{{$medimail_account_application->_guid}}-counter"{{if !$medimail_account_application->_unread_messages}} style="display: none;"{{/if}}>{{$medimail_account_application->_unread_messages}}</span>
          </label>
        </span>
      {{/if}}

      {{if $medimail_account_organisational && $medimail_account_organisational->_id}}
        <span class="circled">
          <label>
            <input type="radio" name="selected_account" onclick="Medimail.selectAccount('{{$medimail_account_organisational->_id}}');"/>
            <img src="modules/medimail/images/icon_min.png" title="Medimail">
            {{if $medimail_account_organisational->libelle}}{{$medimail_account_organisational->libelle}}{{else}}{{tr}}CMedimailAccount.type.organizational{{/tr}}{{/if}}
            <span class="msg-counter" id="{{$medimail_account_organisational->_guid}}-counter"{{if !$medimail_account_organisational->_unread_messages}} style="display: none;"{{/if}}>{{$medimail_account_organisational->_unread_messages}}</span>
          </label>
        </span>
      {{/if}}
    {{/if}}

    {{if $mondial_sante_account && $mondial_sante_account->_id}}
      {{mb_script module=mondialSante script=MondialSante ajax=1}}
      <span class="circled">
        <label>
          <input type="radio" name="selected_account" onclick="MondialSante.selectAccount('{{$mondial_sante_account->_id}}');" value="{{$mondial_sante_account->_guid}}"/>
          <i class="msgicon icon-i-laboratory fa-lg"></i>
          {{tr}}CMondialSanteAccount-title-account{{/tr}}
          <span class="msg-counter" id="{{$mondial_sante_account->_guid}}-counter"{{if !$mondial_sante_account->_unread_messages}} style="display: none;"{{/if}}>{{$mondial_sante_account->_unread_messages}}</span>
        </label>
      </span>
    {{/if}}
  </span>
</div>

<div id="account" style="position: relative;">

</div>
