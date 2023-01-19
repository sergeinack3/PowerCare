{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$user || !$user->_id}}
  {{mb_return}}
{{/if}}

{{mb_script module=admin script=kerberos_ldap ajax=true}}

<table class="main tbl">
  <tr>
    <th class="title" colspan="2">{{tr}}CKerberosLdapIdentifier|pl{{/tr}}</th>
  </tr>

  <tr>
    <th class="narrow">
      <button type="button" class="new notext compact" onclick="KerberosLDAP.edit(null, '{{$user->_id}}', {onClose: KerberosLDAP.showList.curry('{{$user->_id}}') });">
        {{tr}}CKerberosLdapIdentifier-action-Create{{/tr}}
      </button>
    </th>

    <th>{{mb_label class=CKerberosLdapIdentifier field=username}}</th>
  </tr>

  {{foreach from=$user->loadRefKerberosLdapIdentifiers() item=_identifier}}
    <tr>
      <td>
        <button type="button" class="edit notext compact" onclick="KerberosLDAP.edit('{{$_identifier->_id}}', '{{$user->_id}}');">
          {{tr}}common-action-Edit{{/tr}}
        </button>
      </td>

      <td>{{mb_value object=$_identifier field=username}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="2">{{tr}}CKerberosLdapIdentifier.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>