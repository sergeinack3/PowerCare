{{*
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-no-align me-no-box-shadow">
  <tr>
    <th class="title" colspan="2">{{tr}}Result{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}Location{{/tr}}</th>
    <th>{{tr}}Error{{/tr}}</th>
  </tr>
  {{foreach from=$treecda.validateSchematron item=_error}}
    <tr>
      <td>
        {{$_error.location}}
      </td>
      <td>
        {{$_error.error}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="2">
        {{tr}}Document valide{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>