{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Query Digests</h2>

<table class="tbl">
  <tr>
    <th>{{mb_title class=CSQLQueryDigest field=hostname}}</th>
    <th>{{mb_title class=CSQLQueryDigest field=threshold}}</th>
    <th>{{mb_title class=CSQLQueryDigest field=ts_min}}</th>
    <th>{{mb_title class=CSQLQueryDigest field=ts_max}}</th>
  </tr>
  {{foreach from=$digests item=_digest}}
  <tr>
    <td>{{mb_value object=$_digest field=hostname}}</td>
    <td>{{mb_value object=$_digest field=threshold}}</td>
    <td>{{mb_value object=$_digest field=ts_min}}</td>
    <td>{{mb_value object=$_digest field=ts_max}}</td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="4">{{tr}}CSQLQueryDigest.none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>