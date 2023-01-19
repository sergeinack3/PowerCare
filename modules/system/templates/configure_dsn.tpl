{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=inline value=false}}
{{unique_id var=dsn_uid}}

{{mb_script module=system script=DSN ajax=true}}

{{assign var=dsnConfig value=0}}

{{if $dsn|array_key_exists:$conf.db}}
  {{assign var=dsnConfig value=$conf.db.$dsn}}
{{/if}}

{{if !$inline}}
  <table class="main tbl">
    <tr>
      <th class="category" colspan="2">
        {{tr}}config-db{{/tr}} '{{$dsn}}'
      </th>
    </tr>
{{/if}}

<tr>
  {{if $inline}}
    <th style="width: 20%; font-weight: bold;">
      {{$dsn}}
    </th>
  {{/if}}

  <td class="dsn-uri narrow" id="{{$dsn_uid}}">
    {{mb_include module=system template=inc_view_dsn}}
  </td>
  <td style="vertical-align: top;">
    <div class="dsn-is-configured" style="display: none; vertical-align: top;">
      <button type="button" class="edit" onclick="DSN.edit('{{$dsn}}', '{{$dsn_uid}}');">
        {{tr}}Edit{{/tr}}
      </button>

      <button type="button" class="search" onclick="DSN.test('{{$dsn}}', this.next().down());">
        {{tr}}commmon-action-Test{{/tr}}
      </button>

      <div style="display: inline-block">
        <table class="form"></table>
      </div>
    </div>

    <div class="dsn-is-empty" style="display: none;">
      <button type="button" class="new" onclick="DSN.edit('{{$dsn}}', '{{$dsn_uid}}');">
        {{tr}}Create{{/tr}}
      </button>
    </div>
  </td>
</tr>

{{if !$inline}}
  </table>
{{/if}}