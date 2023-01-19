{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=exchange_source ajax=true}}

{{if $mssante_account}}
  {{mb_script module=mssante script=Account ajax=true}}
{{/if}}

{{if $medimail_account}}
  {{mb_script module=medimail script=Medimail ajax=true}}
{{/if}}

<table class="tbl">
  <tr>
    <th class="title" colspan="5" style="border-right: none;">
      Comptes actifs
    </th>
    <th class="title" style="border-left: none;">
      <button type="button" class="add notext" onclick="Messagerie.addAccount();" style="float: right;">
        {{tr}}mod-messagerie-msg-add_account{{/tr}}
      </button>
    </th>
  </tr>
  <tr>
    <th class="category narrow"></th>
    <th class="category">{{tr}}CExchangeSource-libelle{{/tr}}</th>
    <th class="category">{{tr}}Type{{/tr}}</th>
    <th class="category">{{tr}}Address{{/tr}}</th>
    <th class="category">{{tr}}CExchangeSource-active{{/tr}}</th>
    <th class="category">{{tr}}Actions{{/tr}}</th>
  </tr>
  {{foreach from=$sources_smtp item=_source}}
    <tr>
      <td class="narrow">
        <button type="button" class="edit notext compact"
                onclick="ExchangeSource.editSource('{{$_source->_guid}}', true, '{{$_source->name}}',
                  '{{$_source->_wanted_type}}', null, Messagerie.refreshManageAccounts.curry())">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
      <td>{{mb_value object=$_source field=libelle}}</td>
      <td>{{tr}}CExchangeSource.smtp-desc{{/tr}}</td>
      <td>{{mb_value object=$_source field=user}}</td>
      <td>{{mb_value object=$_source field=active}}</td>
      <td class="button">
        {{mb_include module="`$_source->_ref_module->mod_name`" template="`$_source->_class`_tools_inc"}}
      </td>
    </tr>
  {{/foreach}}
  {{foreach from=$sources_pop item=_source}}
    <tr>
      <td class="narrow">
        <button type="button" class="edit notext compact"
                onclick="ExchangeSource.editSource('{{$_source->_guid}}', true, '{{$_source->name}}',
                  '{{$_source->_wanted_type}}', null, Messagerie.refreshManageAccounts.curry())">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
      <td>{{mb_value object=$_source field=libelle}}</td>
      <td>{{tr}}CExchangeSource.pop-desc{{/tr}}</td>
      <td>{{mb_value object=$_source field=user}}</td>
      <td>{{mb_value object=$_source field=active}}</td>
      <td class="button">
        {{mb_include module="`$_source->_ref_module->mod_name`" template="`$_source->_class`_tools_inc"}}
      </td>
    </tr>
  {{/foreach}}

  {{if $mssante_account}}
    <tr>
      <td class="narrow">
        <button class="edit notext compact" onclick="Account.edit(Messagerie.refreshManageAccounts.curry());">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
      <td></td>
      <td>Compte MSSante</td>
      <td>{{$mssante_account->address}}</td>
      <td>{{tr}}Yes{{/tr}}</td>
      <td class="empty"></td>
    </tr>
  {{/if}}

  {{if $apicrypt_account && $apicrypt_account->_id}}
    <tr>
      <td class="narrow">
        <button type="button" class="edit notext compact"
                onclick="new Url('apicrypt', 'ajax_apicrypt_account').requestModal(500, 600);">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
      <td>{{mb_value object=$apicrypt_account field=libelle}}</td>
      <td>Compte Apicrypt</td>
      <td>{{mb_value object=$apicrypt_account field=user}}</td>
      <td>{{mb_value object=$apicrypt_account field=active}}</td>
      <td></td>
    </tr>
  {{/if}}

  {{if $medimail_account && $medimail_account->_id}}
    <tr>
      <td class="narrow">
        <button type="button" class="edit notext compact" onclick="Medimail.editAccount();">{{tr}}Edit{{/tr}}</button>
        <td>{{mb_value object=$medimail_account field=libelle}}</td>
        <td>Compte Medimail</td>
        <td>{{mb_value object=$medimail_account field=medimail_login}}</td>
        <td>{{tr}}Yes{{/tr}}</td>
        <td></td>
      </td>
    </tr>
  {{/if}}
</table>
