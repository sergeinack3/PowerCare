{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination change_page='changePageFileError' total=$total current=$start step=50}}

<table class="main tbl">
  <tr>
    <th>{{mb_title object=$file field='object_class'}}</th>
    <th>{{mb_title object=$file field='object_id'}}</th>
    <th>{{mb_title object=$file field='file_real_filename'}}</th>
    <th>{{mb_title object=$file field='file_name'}}</th>
    <th>{{mb_title object=$file field='file_category_id'}}</th>
    <th>{{mb_title object=$file field='file_date'}}</th>
    <th>{{mb_title object=$file field='doc_size'}}</th>
    <th>{{mb_title object=$file_report field='file_path'}}</th>
  </tr>
  {{foreach from=$error_file_list item=_error_file}}
    <tr>
      <td>{{$_error_file.object_class}}</td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_error_file.object_class}}-{{$_error_file.object_id}}')">
          {{$_error_file.object_id}}
        </span>
      </td>
      <td><code>{{$_error_file.file_real_filename}}</code></td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, 'CFile-{{$_error_file.file_id}}')">
          {{$_error_file.file_name}}
        </span>
      </td>
      <td>
        {{assign var=cat_id value=$_error_file.file_category_id}}
        {{if $cat_id|array_key_exists:$categories}}
          {{$categories.$cat_id}}
        {{/if}}
      </td>
      <td>{{$_error_file.file_date}}</td>
      <td title="{{$_error_file.doc_size}}">{{$_error_file.doc_size|decabinary}}</td>
      <td><code>{{$_error_file.file_path}}</code></td>
    </tr>
  {{/foreach}}
</table>