{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "medimail"|module_active && count($docItem->_is_send_mssante) > 0}}
    {{if $docItem->_is_send_mssante.MSSantePatient == true}}
        <div class="file_synchro" title="{{tr var1="MS Santé Patient"}}CDocumentItem-msg-File send with %s{{/tr}}">
          <img src="./modules/medimail/images/mssante_patient.png" alt="MS Santé Patient Logo" />
        </div>
    {{/if}}

    {{if $docItem->_is_send_mssante.MSSantePro == true}}
        <div class="file_synchro" title="{{tr var1="MS Santé Professionnel"}}CDocumentItem-msg-File send with %s{{/tr}}">
          <img src="./modules/medimail/images/mssante_pro.png" alt="MS Santé Pro Logo" />
        </div>
    {{/if}}
{{/if}}
