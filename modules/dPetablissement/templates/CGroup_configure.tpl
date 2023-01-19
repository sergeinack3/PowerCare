{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfigGroup" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_configure module=$m}}
  <table class="form">
    <tr>
      <th class="category" colspan="10">{{tr}}config-{{$m}}{{/tr}}</th>
    </tr>

    {{mb_include module=system template=inc_config_str  var=tag_group}}
    {{mb_include module=system template=inc_config_bool var=dossiers_medicaux_shared}}
      
    <tr>
      <td class="button" colspan="10">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>