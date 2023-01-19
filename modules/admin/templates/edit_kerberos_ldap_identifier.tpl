{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-kerberos-ldap-identifier" method="post" onsubmit="return KerberosLDAP.submit(this);">
  {{mb_key object=$identifier}}
  {{mb_class object=$identifier}}

  {{mb_field object=$identifier field=user_id hidden=true}}
  <input type="hidden" name="del" value="" />

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$identifier}}

    <col style="width: 20%;" />

    <tr>
      <th>{{mb_label object=$identifier field=username}}</th>
      <td>
        {{mb_field object=$identifier field=username}}
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="submit" class="save">{{tr}}common-action-Save{{/tr}}</button>

        {{if $identifier->_id}}
          <button type="button" class="trash" onclick="KerberosLDAP.confirmDeletion(this.form);">
            {{tr}}common-action-Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>