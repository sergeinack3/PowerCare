{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=messagerie script=UserEmail}}

<script>
  Main.add(function () {
    var formAccount = getForm("accountFrm");
    {{if $account_id}}
      UserEmail.refreshAccount($V(formAccount.account_id));
    {{/if}}
  });
</script>


<form onsubmit="return checkForm(this.form)" method="get" name="externalFrm">
  <input type="hidden" name="m" value="{{$m}}"/>
  <input type="hidden" name="tab" value="{{$tab}}"/>
  <label>Messageries disponibles :
  <select name="user_id" onchange="this.form.submit()">
    <option value="">{{tr}}Select{{/tr}} {{tr}}CMediusers{{/tr}}</option>
    {{foreach from=$users item=_user}}
      <option value="{{$_user->_id}}" {{if $user->_id == $_user->_id}}selected="selected" {{/if}}>{{$_user}}</option>
    {{/foreach}}
  </select>
  </label>
</form>

{{if $user->_id}}
<form name="accountFrm" method="get">
  {{tr}}Account{{/tr}} :
      {{foreach from=$mails key=k item=_mailbox}}
      <label>
        <input type="radio" name="account_id" onclick="UserEmail.refreshAccount($V(this))" value="{{$_mailbox->_id}}" {{if $_mailbox->_id == $account_id}}checked="checked"{{/if}}/>
        {{$_mailbox->libelle}}
      </label>
      {{/foreach}}
</form>
{{/if}}

<button type="button" onclick="UserEmail.manageAccounts();" style="float: right;">
  <i class="msgicon fas fa-cog"></i>
  Gestion des comptes
</button>


<div id="account_mail">
  {{if !$account_id}}
    <div class="small-info">{{tr}}messagerie-msg-pls_select_an_account{{/tr}}</div>
  {{/if}}
</div>