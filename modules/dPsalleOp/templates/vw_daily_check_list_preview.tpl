{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main layout">
  <tr>
    {{foreach from=$daily_check_lists item=check_list}}
      <td>
        <h2>{{$check_list->_ref_list_type->title}}</h2>
        {{if $check_list->_ref_list_type->description}}
          <p>{{$check_list->_ref_list_type->description}}</p>
        {{/if}}

        {{mb_include module=salleOp template=inc_edit_check_list
            check_list=$check_list
            check_item_categories=$check_list->_ref_list_type->_ref_categories
            personnel=$listValidateurs
            preview=true
        }}
      </td>
    {{foreachelse}}
      <td class="empty">
        {{tr}}CDailyCheckList.none{{/tr}}
      </td>
    {{/foreach}}
  </tr>
</table>
