{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th>{{tr}}common-Minimum-court{{/tr}}</th>
    <td class="text">
      {{mb_include module=importTools template=inc_display_value value=$metrics.min col_info=$columns.$column tooltip=true}}
    </td>
  </tr>

  <tr>
    <th>{{tr}}common-Maximum-court{{/tr}}</th>
    <td class="text">
      {{mb_include module=importTools template=inc_display_value value=$metrics.max col_info=$columns.$column tooltip=true}}
    </td>
  </tr>

  <tr>
    <th>NULL</th>
    <td {{if !$metrics.null}}class="empty"{{/if}}>{{$metrics.null|number_format:'0':'.':' '}}</td>
  </tr>
</table>