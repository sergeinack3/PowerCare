{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=dsn_uid}}

{{mb_script module=system script=elastic_dsn ajax=true}}

{{assign var=dsnConfig value=0}}

{{if array_key_exists("elastic", $conf)}}
    {{if $dsn|array_key_exists:$conf.elastic}}
        {{assign var=dsnConfig value=$conf.elastic.$dsn}}
    {{/if}}
{{/if}}


<tr>
  <th style="width: 20%; font-weight: bold;">
      {{$dsn}}
  </th>

  <td class="dsn-uri narrow" id="{{$dsn_uid}}">
      {{mb_include module=system template=inc_view_elastic_dsn}}
  </td>
  <td style="vertical-align: top;">
    <div class="dsn-is-configured" style="display: none; vertical-align: top;">
      <button type="button" class="edit" onclick="ElasticDSN.edit('{{$dsn}}', '{{$dsn_uid}}');">
          {{tr}}Edit{{/tr}}
      </button>

      <button type="button" class="search" onclick="ElasticDSN.test('{{$dsn}}', '{{$_module}}');">
          {{tr}}commmon-action-Test{{/tr}}
      </button>
    </div>

    <div class="dsn-is-empty" style="display: none;">
      <button type="button" class="new" onclick="ElasticDSN.edit('{{$dsn}}', '{{$dsn_uid}}');">
          {{tr}}Create{{/tr}}
      </button>
    </div>
  </td>
</tr>
