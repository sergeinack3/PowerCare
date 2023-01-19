{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="3">
      {{tr}}{{$class}}-title-crazy-found{{/tr}}
      <br />
      {{if $class == "CAccessLog" || $class == "CAccessLogArchive"}}
        {{mb_label class=$class field=_average_duration}} &gt; {{$ratio}}s
      {{else}}
        {{mb_label class=$class field=duration}} &gt; {{$ratio}}s
      {{/if}}
    </th>
  </tr>
  <tr>
    <th>{{mb_label class=$class field=_module}}</th>
    <th>{{mb_label class=$class field=_action}}</th>
    <th>{{tr}}Total{{/tr}}</th>
  </tr>

  {{foreach from=$logs item=_log}}
    <tr>
      <td><strong>{{$_log._module}}</strong></td>
      <td>{{$_log._action}}</td>
      <td>{{$_log.total}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="3">{{tr}}{{$class}}.none{{/tr}}</td>
    </tr>
  {{/foreach}}

  <tr>
    <td colspan="3" class="button">
      <button class="trash" type="button" onclick="purgeCrazyLogs('{{$class}}');" {{if !count($logs)}}disabled="true"{{/if}}>
        {{tr}}Purge{{/tr}}
      </button>
    </td>
  </tr>
</table>

{{if $purged_count !== null}}
  <div class="small-success">
    {{tr}}{{$class}}-title-crazy-purged{{/tr}} : {{$purged_count}}
  </div>
{{/if}}