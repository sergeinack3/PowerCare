{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPfiles script=files ajax=true}}

<script>
  submitStatus = function(file_id) {
    var form = getForm('edit_status_file');
    $V(form.file_id, file_id);
    var date = new Date().toDATETIME();
    $V(form.read_datetime, date);
    form.onsubmit();
  };
</script>

<h2>{{tr}}CFileUserView{{/tr}} - {{$object}}</h2>

<form name="edit_status_file" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete : FilesCategory.reloadModal})">
  <input type="hidden" name="m" value="dPfiles" />
  {{mb_class object=$file_view}}
  <input type="hidden" name="read_datetime" value=""/>
  <input type="hidden" name="user_id" value="{{$user_id}}" />
  <input type="hidden" name="file_id" value="" />
</form>

<table class="tbl">
  {{foreach from=$categories item=_category}}
    <tr>
      <th colspan="2">{{$_category}}</th>
    </tr>
    {{foreach from=$_category->_ref_files item=_file}}
      <tr>
        <td>
          <a onclick="popFile('{{$_file->object_class}}','{{$_file->object_id}}','{{$_file->_class}}','{{$_file->_id}}');"
             onmouseover="ObjectTooltip.createEx(this, '{{$_file->_guid}}');">
            {{$_file}}
          </a>
        </td>
        <td class="narrow">
          <button type="button" onclick="submitStatus('{{$_file->_id}}');" class="tick">{{tr}}CFilesCategory-read{{/tr}}</button>
        </td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>