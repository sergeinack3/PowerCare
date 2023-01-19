{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  {{foreach from=$_passage->_ref_files item=_file}}
  <tr>
    <td>
      {{thumblink document=$_file class="button download notext"}}{{/thumblink}}
      <a href="#" class="action" 
         onclick="File.popup('{{$_passage->_class}}','{{$_passage->_id}}','{{$_file->_class}}','{{$_file->_id}}');"
         onmouseover="ObjectTooltip.createEx(this, '{{$_file->_guid}}', 'objectViewHistory')">
        {{$_file->file_name}}
      </a>
      <small>({{$_file->_file_size}})</small>
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty">
      {{tr}}{{$_passage->_class}}{{/tr}} :
      {{tr}}CFile.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>