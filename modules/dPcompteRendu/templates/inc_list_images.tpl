{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$images item=_image}}
  {{mb_include module=files template=CFile_fileviewer file=$_image ondblclick="insertImage(`$_image->_id`)"}}
{{foreachelse}}
  <div class="small-info">
    {{tr}}CCompteRendu-no_picture_fo_context{{/tr}}
  </div>
{{/foreach}}
