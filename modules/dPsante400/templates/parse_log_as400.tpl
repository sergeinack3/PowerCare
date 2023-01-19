{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  <strong>{{$entries_count|integer}} duration overheads</strong> were found over
  <strong>{{$lines_count|integer}} lines</strong> parsed in global DB2 SQL overhead log.<br/>
  Duration thresold was set to <strong>{{$params->min_duration}} seconds</strong>.<br/>
  Phase filter was set to '<strong>{{$params->phase|default:All}}'</strong>.<br/>
  For performance issues, only the <strong>{{$rows_count|integer}} most recent rows</strong> were shown.
</div>

<table class="tbl">

  {{foreach from=$table key=_date item=_hours}}
    <tr>
      <th colspan="3">{{$_date}}</th>
    </tr>
    {{foreach from=$_hours key=_hour item=_rows}}
      <tr>
        <th colspan="3" class="section">{{$_hour}}</th>
      </tr>
      {{foreach from=$_rows item=_row}}
        <tr>
          <td>{{$_row.datetime}}</td>
          <td>{{$_row.phase}}</td>
          <td>{{$_row.duration}}</td>
        </tr>
      {{/foreach}}
    {{/foreach}}
  {{/foreach}}
</table>
