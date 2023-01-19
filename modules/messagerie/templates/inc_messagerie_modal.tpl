{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $account && $account->_class == 'CSourcePOP'}}
  {{mb_script module=messagerie script=UserEmail ajax=1}}
{{elseif $account && $account->_class == 'CMSSanteUserAccount'}}
  {{mb_script module=mssante script=Folder ajax=1}}
  {{mb_script module=mssante script=Message ajax=1}}
  {{mb_script module=mssante script=Account ajax=1}}
{{elseif $account && $account->_class == 'CMedimailAccount'}}
  {{mb_script module=medimail script=Medimail ajax=1}}
{{elseif $account && $account->_class == 'CMondialSanteAccount'}}
  {{mb_script module=mondialSante script=MondialSante ajax=1}}
{{/if}}

<script type="text/javascript">
  Main.add(function() {
    {{if $account_guid == 'internal'}}
      var url = new Url('messagerie', 'vw_list_internalMessages');
      url.addParam('user_id', '{{$user->_id}}');
      url.requestUpdate('account');
    {{elseif $account && $account->_class == 'CSourcePOP'}}
      UserEmail.refreshAccount('{{$account->_id}}');
    {{elseif $account && $account->_class == 'CMSSanteUserAccount'}}
      Account.select('{{$account->_id}}');
    {{elseif $account && $account->_class == 'CMedimailAccount'}}
      Medimail.selectAccount('{{$account->_id}}');
    {{elseif $account && $account->_class == 'CMondialSanteAccount'}}
      MondialSante.selectAccount('{{$account->_id}}');
    {{/if}}
  });
</script>

<div id="account" style="position: relative;">

</div>