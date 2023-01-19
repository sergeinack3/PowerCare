{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    {{mb_include module=system template=configure_handler class_handler=CAffectationHandler}}

    <tr>
      <td colspan="2" class="button">
        <button class="save">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>