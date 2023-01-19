{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$object->_ref_documents item=_doc}}
  <span class="dhe_sum_item">
    {{$_doc->nom}}
  </span>
{{/foreach}}

{{foreach from=$object->_ref_files item=_file}}
  <span class="dhe_sum_item">
    {{$_file->file_name}}
  </span>
{{/foreach}}