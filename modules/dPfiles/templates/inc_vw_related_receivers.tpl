{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=file_category ajax=true}}

<button type="button" class="new" onclick="FilesCategory.editRelatedReceiver(null, '{{$files_category->_id}}')">
  {{tr}}CFilesCategoryToReceiver-new{{/tr}}
</button>

<table class="tbl" id="list_receivers-{{$files_category->_id}}">
  {{mb_include template="inc_vw_related_receivers_list"}}
</table>