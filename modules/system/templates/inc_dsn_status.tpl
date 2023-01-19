{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $ds}}
  {{if $ds->metadata|@count}}
    <tr>
      <th>{{tr}}config-{{$section}}-metadata-elected{{/tr}}</th>
      <td>{{$ds->metadata.elected}}</td>
    </tr>
    <tr>
      <th colspan="2" class="section">{{tr}}config-{{$section}}-metadata-statuses{{/tr}}</th>
    </tr>
  
    {{foreach from=$ds->metadata.statuses key=_host item=_status}}
      <tr>
        <th>
          {{$_host}}
        </th>
        <td>
          <ul>
            {{foreach from=$_status key=_key item=_value}}
              <li><strong>{{$_key}}</strong>: {{$_value|smarty:nodefaults|var_export:true}}</li>
            {{/foreach}}
          </ul>
        </td>
      </tr>
    {{/foreach}}
  {{else}}
    <tr>
      <td colspan="2" class="empty">
        {{tr}}config-{{$section}}-no_metadata{{/tr}}
      </td>
    </tr>
  {{/if}}
{{else}}
  <tr>
    <td colspan="2">
      {{foreach from=$hosts item=_host}}
        {{mb_include module=system template=inc_test_dsn_failure dsn=$dsn host=$_host}}
      {{/foreach}}
    </td>
  </tr>
{{/if}}