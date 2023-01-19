{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset>
  <legend>Échantillon</legend>

  <table class="main tbl">
    <tr>
      <th>#</th>
      <th>Identifiant Mediboard</th>
      <th>Identifiant domaine</th>
    </tr>

    {{foreach from=$sample key=_nb item=_line}}
      <tr>
        <td>{{$_nb}}</td>
        <td>{{$_line.mediboard_identifier}}</td>
        <td>{{$_line.domain_identifier}}</td>
      </tr>
      {{foreachelse}}
      <tr>
        <td class="empty" colspan="3">{{tr}}CKerberosLdapIdentifier.none{{/tr}}</td>
      </tr>
    {{/foreach}}

    {{if $sample}}
      <tr>
        <td class="button" colspan="3">
          <form name="import-kerberos_identifiers" method="post" onsubmit="return onSubmitFormAjax(this, {}, 'kerberos-import-report')">
            <input type="hidden" name="m" value="admin" />
            <input type="hidden" name="dosql" value="do_import_kerberos_identifiers" />
            <input type="hidden" name="file_uid" value="{{$uid}}" />

            <button type="submit" class="save">{{tr}}common-action-Import{{/tr}}</button>
          </form>
        </td>
      </tr>
    {{/if}}
  </table>
</fieldset>

<div id="kerberos-import-report"></div>