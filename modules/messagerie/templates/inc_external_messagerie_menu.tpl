{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=accounts value=$messagerie.accounts.external}}
{{assign var=counters value=$messagerie.counters.external}}

{{foreach from=$accounts key=index item=account}}
  <li class="messagerie-menu-element" onclick="Messagerie.openModal('{{$index}}');">
    <span class="msg-counter" id="messagerie-external-{{$index}}-counter"{{if !$counters.$index}} style="display: none;"{{/if}}>
    {{$counters.$index}}
    </span>
    {{if $account->_class == 'CSourcePOP'}}
      {{if $account->name|strpos:'apicrypt' === false}}
        <i class="fa fa-envelope msgicon"></i>
      {{else}}
        <img title="Apicrypt" style="width: 16px; height: 16px;" src="modules/apicrypt/images/icon.png">
      {{/if}}
      {{$account->libelle}}
    {{elseif $account->_class == 'CMSSanteUserAccount'}}
      <img title="MSSante" src="modules/mssante/images/icon_min.png">
      MSSanté
    {{elseif $account->_class == 'CMedimailAccount'}}
      <img src="modules/medimail/images/icon_min.png" title="Medimail">
      {{if $account->libelle}}{{$account->libelle}}{{else}}Medimail{{/if}}
    {{/if}}
  </li>
{{/foreach}}

{{if 'messagerie access allow_external_mail'|gconf}}
  <li class="messagerie-menu-element" onclick="Messagerie.manageAccounts();">
    <i class="msgicon fas fa-cog"></i>
    {{tr}}common-action-Account management{{/tr}}
  </li>
{{/if}}

