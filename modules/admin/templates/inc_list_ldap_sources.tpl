{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=colspan value=3}}
{{assign var=nb_groups value=$groups|@count}}
{{assign var=colspan value=$colspan+$nb_groups}}

<table class="main tbl">
  <tr>
    <th class="narrow" style="text-align: center;">
      <button type="button" class="new notext" onclick="LDAPSource.edit();">
        {{tr}}CSourceLDAP-action-Add{{/tr}}
      </button>
    </th>

    <th class="narrow">#</th>
    <th class="narrow"></th>
    <th class="narrow">{{mb_label class=CSourceLDAP field=cascade}}</th>

    {{foreach name=groups from=$groups item=_group}}
      <th>
        {{$_group}}
      </th>
    {{/foreach}}
  </tr>

  <tbody>
  {{foreach name=sources from=$sources item=_source}}
    {{mb_include module=admin template=inc_config_ldap_source source=$_source groups=$groups}}
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="{{$colspan}}">{{tr}}CSourceLDAP.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  </tbody>
</table>
