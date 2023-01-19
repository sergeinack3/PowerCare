{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfigBloquage" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  {{assign var="class" value="CSejour"}}
  <table class="form">
    <tr>
      <th class="title" colspan="2">Blocage des objets</th>
    </tr>

    {{mb_include module=system template=inc_config_bool class=COperation var=locked}}
    {{mb_include module=system template=inc_config_bool class=CSejour    var=locked}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>