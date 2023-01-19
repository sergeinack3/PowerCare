{{*
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  {{$total}} {{tr}}CMedecin|pl{{/tr}}
</div>

<table class="main tbl">
  <tr>
    <th class="narrow">{{tr}}CMedecin-import_file_version{{/tr}}</th>
    <th class="narrow">{{tr}}Total{{/tr}}</th>
    <th>{{tr}}Percentage{{/tr}}</th>
  </tr>

  {{foreach from=$versions item=_version}}
    <tr>
      <th class="me-text-align-right">{{$_version.import_file_version}}</th>
      <td>{{$_version.total}}</td>
      <td>{{$_version.pct}}%</td>
    </tr>
  {{/foreach}}
</table>