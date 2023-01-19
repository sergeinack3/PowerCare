{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=parsed value=$parsed_files|@count}}
{{assign var=unparsed value=$unparsed_files|@count}}
{{assign var=sibling value=$sibling_objects|@count}}
{{assign var=related value=$related_objects|@count}}

<hr />
{{tr}}common-Regular expression{{/tr}} : <code>{{$regex}}</code>
<hr />

<table class="main tbl">
  <tr>
    <th colspan="3">Correctement analysés</th>
    <th rowspan="2">{{tr}}common-Related object|pl{{/tr}}</th>
    <th rowspan="2">Non analysés</th>
    <th rowspan="2" class="section">{{tr}}common-Total{{/tr}}</th>
  </tr>

  <tr>
    <th class="section">{{tr}}common-Sibling object|pl{{/tr}}</th>
    <th class="section">{{tr}}common-No sibling object|pl{{/tr}}</th>
    <th class="section">{{tr}}common-Total{{/tr}}</th>
  </tr>

  <tr>
    <td>{{$sibling}}</td>
    <td>{{$parsed-$sibling}}</td>
    <td>{{$parsed}}</td>

    <td>{{$related}}</td>
    <td>{{$unparsed}}</td>
    <td>{{$count}}</td>
  </tr>
</table>