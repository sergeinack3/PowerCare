{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table>
  <tr>
    {{foreach from=$plages item=_plage}}
      {{assign var=salle value=$_plage->_ref_salle}}
      <td style="width: {{math equation="50 / x" x=$plages|@count}}%;">
        {{mb_include module=bloc template=inc_list_operations operations=$_plage->_ref_operations}}
      </td>
    {{/foreach}}
  </tr>
</table>