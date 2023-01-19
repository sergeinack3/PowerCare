{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td>
      <a class="button new" onclick="Bloc.updateSelectedSSPI(); Bloc.editSSPI(0);">{{tr}}CSSPI-title-create{{/tr}}</a>
      <table class="tbl">
        <tr>
          <th>{{mb_title class=CSSPI field=libelle}}</th>
          <th>{{tr}}CBlocOperatoire|pl{{/tr}}</th>
        </tr>

        <tbody id="list_sspis">
        {{mb_include module=bloc template=inc_list_sspis}}
        </tbody>
      </table>
    </td>
  </tr>
</table>