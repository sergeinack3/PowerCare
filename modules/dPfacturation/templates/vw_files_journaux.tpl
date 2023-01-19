{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$files item=_file}}
  {{assign var=object_class value=$_file->object_class}}
  {{assign var=object_id    value=$_file->object_id}}
  <tr id="tr_{{$_file->_guid}}">
    <td id="td_{{$_file->_guid}}">
      {{mb_include module=files template="inc_widget_line_file"}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty">
      {{tr}}CFile.none{{/tr}}
    </td>
  </tr>
{{/foreach}}