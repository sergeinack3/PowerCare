{{*
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  refreshListImages = function() {
    document.location.reload();
  }
</script>

<h2>
  <button class="edit notext" onclick="DrawingCategory.editModal('{{$object->_id}}', null, null, refreshList)"></button>
  {{$object}}
  <button class="upload notext" onclick="File.upload('{{$object->_class}}', '{{$object->_id}}')"></button>
</h2>

{{foreach from=$object->_ref_files item=_file}}
  <div style="display: inline-block; text-align: center;">
    {{thumbnail document=$_file profile=medium style="max-width: 150px; max-height: 150px;" class=thumbnail alt="`$_file->file_name`"
      onmouseover="ObjectTooltip.createEx(this, '`$_file->_guid`')"}}
    <br />
    <small>{{$_file->file_name}}</small>
  </div>
{{/foreach}}