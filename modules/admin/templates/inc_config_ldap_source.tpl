{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_ternary var=_protocol test=$_source->secured value='ldaps' other='ldap'}}

<tr>
  <td style="text-align: center;">
    <button type="button" class="edit notext compact" onclick="LDAPSource.edit('{{$_source->_id}}');">
      {{tr}}CSourceLDAP-action-Edit{{/tr}}
    </button>

    <button type="button" class="search notext compact" onclick="LDAPSource.test('{{$_source->_id}}');">
      {{tr}}CSourceLDAP-action-Test{{/tr}}
    </button>
  </td>

  <td style="text-align: center;">
    {{if $_source->priority}}
      <div class="rank">{{mb_value object=$_source field=priority}}</div>
    {{/if}}
  </td>

  <td style="white-space: nowrap;">
    {{if $_source->host}}
      {{assign var=_althosts value='/\s*,\s*/'|preg_split:$_source->host}}

      <code>
        <span class="dsn dsn-dbtype" title="{{tr}}CSourceLDAP-secured-desc{{/tr}}">{{$_protocol}}</span>://
        <span class="dsn dsn-dbuser" title="{{tr}}CSourceLDAP-user-desc{{/tr}}">{{$_source->user}}</span>

        {{if $_source->bind_rdn_suffix}}
          [ <span class="dsn" title="{{tr}}CSourceLDAP-bind_rdn_suffix-desc{{/tr}}">{{$_source->bind_rdn_suffix}}</span> ]
        {{/if}}

        @
        <span class="dsn dsn-dbhost" title="{{tr}}CSourceLDAP-host-desc{{/tr}}">

              {{if $_althosts|@count > 1}}
                [
                {{foreach from=$_althosts item=_althost name=althosts}}
                  <span class="dsn-host">{{$_althost}}</span>
                  {{if !$smarty.foreach.althosts.last}},{{/if}}
                {{/foreach}}
                ]
              {{else}}
                {{$_source->host}}
              {{/if}}
            </span> :
        <span class="dsn dsn-dbport" title="{{tr}}CSourceLDAP-port-desc{{/tr}}">{{$_source->port}}</span> /
        <span class="dsn dsn-dbname" title="{{tr}}CSourceLDAP-rootdn-desc{{/tr}}">{{$_source->rootdn}}</span>

        {{if !$_source->password}}
          <span class="dsn dsn-dbpass">[{{tr}}config-dsn-no_password{{/tr}}]</span>
        {{/if}}
      </code>
    {{/if}}
  </td>

    <td style="text-align: center;">
      {{if $_source->cascade}}
          <i class="fa fa-arrow-down fa-lg" style="color: #0a4973"></i>
      {{else}}
          <i class="fa fa-times fa-lg" style="color: #8c000c"></i>
      {{/if}}
    </td>

  {{foreach name=groups from=$groups item=_group}}
    <td style="text-align: center;">
      {{mb_include module=system template=inc_vw_bool_icon size="lg" value=$_source->isAvailable($_group->_id)}}
    </td>
  {{/foreach}}
</tr>
