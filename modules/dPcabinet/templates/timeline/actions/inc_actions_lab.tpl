{{*
 * @package Mediboard\Consultations
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "mondialSante"|module_active}}
    <div class="button_size">
        <button type="button" class="search"
                onclick="MondialSante.showMessagesForPatient($V(getForm('filtreTdb').praticien_id), '{{$patient->_id}}');">
            {{tr}}CMondialSanteMessage-action-show_for_patient{{/tr}}
        </button>
    </div>
{{/if}}
