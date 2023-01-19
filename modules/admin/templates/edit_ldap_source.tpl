{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-ldap-source" method="post" onsubmit="return LDAPSource.submit(this);">
  {{mb_key object=$source}}
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_ldap_source_aed" />
  <input type="hidden" name="del" value="0" />
  
  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$source colspan=2}}

    <tr>
      <th>{{mb_label object=$source field=name}}</th>
      <td>{{mb_field object=$source field=name}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=host}}</th>
      <td>{{mb_field object=$source field=host}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=port}}</th>
      <td>{{mb_field object=$source field=port}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=user}}</th>
      <td>{{mb_field object=$source field=user}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=password}}</th>
      <td>{{mb_field object=$source field=password}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=secured}}</th>
      <td>{{mb_field object=$source field=secured typeEnum=checkbox}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=priority}}</th>
      <td>{{mb_field object=$source field=priority increment=true form="edit-ldap-source"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=rootdn}}</th>
      <td>{{mb_field object=$source field=rootdn}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=bind_rdn_suffix}}</th>
      <td>{{mb_field object=$source field=bind_rdn_suffix}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=ldap_opt_protocol_version}}</th>
      <td>{{mb_field object=$source field=ldap_opt_protocol_version increment=true form="edit-ldap-source" min=2 max=3}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=ldap_opt_referrals}}</th>
      <td>{{mb_field object=$source field=ldap_opt_referrals}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=cascade}}</th>
      <td>{{mb_field object=$source field=cascade}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=dn_whitelist}}</th>
      <td>{{mb_field object=$source field=dn_whitelist}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=dn_alternatives}}</th>
      <td>{{mb_field object=$source field=dn_alternatives}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$source field=_groups}}</th>
      <td>
          {{mb_field object=$source field=_groups separator='<br />'}}
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $source->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>

          <button type="button" class="trash" onclick="LDAPSource.confirmDeletion(this.form);">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
