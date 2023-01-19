{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=facture ajax=1}}

<button type="button" class="edit" onclick="Facture.editEvt('{{$object->_guid}}');">{{tr}}CConsultation-cotation{{/tr}}</button>