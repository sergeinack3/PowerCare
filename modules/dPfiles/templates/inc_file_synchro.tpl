{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "appFineClient"|module_active && $docItem->_ref_appFine_idex && $docItem->_ref_appFine_idex->_id}}
  <div class="file_synchro">
    <img src="./modules/appFineClient/images/icon.png" title="{{tr var1="AppFine"}}CDocumentItem-msg-File synchronized with %s{{/tr}}" />
  </div>
{{/if}}

{{if "dmp"|module_active && $docItem->_status_dmp}}
  <div class="file_synchro {{if $docItem->_status_dmp == 4}}unpublished{{/if}}" title="{{tr}}CFileTraceability.status.dmp.{{$docItem->_status_dmp}}{{/tr}}">
    <i style="position: absolute; top:4px; right: 1px;" class="fas {{$docItem->_fa_dmp}}"></i>
    <img src="./modules/dmp/images/icon.png"/>
  </div>
{{/if}}

{{if "sisra"|module_active && $docItem->_count_sisra_documents}}
  <div class="file_synchro">
    <img src="./modules/sisra/images/icon.png" title="{{tr var1="SISRA"}}CDocumentItem-msg-File synchronized with %s{{/tr}}"/>
  </div>
{{/if}}
