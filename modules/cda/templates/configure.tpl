{{*
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    <tr>
      <th class="title" colspan="2">Configuration</th>
    </tr>
    <tr>
      <td>
        {{mb_include module=system template=inc_config_str var=path_ghostscript}}
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <button class="save">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>