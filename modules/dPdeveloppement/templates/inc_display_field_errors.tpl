{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <td colspan="10">
      {{mb_include module=system template=inc_pagination total=$total current=$start step=$step change_page="ReferencesCheck.changePageField" change_page_arg=$progression->_id}}
    </td>
  </tr>

  <tr>
    <th>{{tr}}CIntegrityError-Missing guid{{/tr}}</th>
    <th class="narrow">{{tr}}CIntegrityError-Count used{{/tr}}</th>
  </tr>

  {{foreach from=$errors item=_error}}
    <tr>
      <td>{{$progression->class}}-{{$_error->missing_id}}</td>
      <td align="right">{{$_error->_count_obj|number_format:0:',':' '}}</td>
    </tr>
  {{/foreach}}
</table>