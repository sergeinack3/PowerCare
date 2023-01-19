{{*
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-no-align me-no-box-shadow">
  <tr>
    <th>{{tr}}Result{{/tr}}</th>
  </tr>
  {{foreach from=$treecda.validate item=_error}}
    {{if $_error !== "1"}}
    <tr>
      <td>
        {{$_error}}
      </td>
    </tr>
    {{/if}}
   {{foreachelse}}
    <tr>
      <td>
        {{tr}}Document valide{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>