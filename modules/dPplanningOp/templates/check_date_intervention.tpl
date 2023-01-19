{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="sanitizePlageOp" method="post" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_sanitize_plageop" />

  <table class="tbl" style="text-align: center;">
    <tr>
      <th style="width: 25%;">Nombre d'interventions...</th>
      <th style="width: 25%;">dans une plage...</th>
      <th style="width: 25%;">sans date!</th>
      <th style="width: 25%;">avec une date eronnée!</th>
    </tr>

    <tr>
      <td>{{$counts.total|integer}}</td>
      <td>{{$counts.plaged|integer}}</td>
      <td class="{{$counts.missing|ternary:warning:ok}}"><strong>{{$counts.missing|integer}}</strong></td>
      <td class="{{$counts.wrong|ternary:warning:ok}}"><strong>{{$counts.wrong|integer}}</strong></td>
    </tr>

    <tr>
      <td colspan="4" class="button">
        <button type="submit" class="change">{{tr}}Sanitize-plageop{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>