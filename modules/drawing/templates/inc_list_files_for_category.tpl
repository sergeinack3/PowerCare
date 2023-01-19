{{*
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- use the "selectThisElement" function -->

<ul class="cfile_to_select_list">
  {{foreach from=$category->_ref_files item=_file}}
    <li>
      <a href="#"
         onclick="selectThisElement(this);"
         data-file_id="{{$_file->_id}}"
         data-file_path="{{$_file->_file_path}}"
         data-file_type="{{$_file->file_type}}"
         onmouseover="ObjectTooltip.createEx(this, '{{$_file->_guid}}');">
        {{thumbnail document=$_file profile=medium alt="" default_size=1}}
      </a>
    </li>
  {{foreachelse}}
    <li>{{tr}}CDrawingCategory-msg-This folder does not have an image that can be used for drawing{{/tr}}</li>
  {{/foreach}}
</ul>