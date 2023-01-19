{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    <tr>
      <td>
        {{mb_include module=system template=inc_config_bool var=tunnel_pass}}
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button class="save">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>