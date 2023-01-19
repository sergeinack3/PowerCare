{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $offline}}
  <td class="not-printable">
    <button class="search notext compact" onclick="Modal.open($('dossier-{{$curr_op->sejour_id}}'), {width: '100%'})">Dossier de soins</button>
    <button class="print notext compact" onclick="printOneDossier('{{$curr_op->sejour_id}}')">Imprimer le dossier</button>
  </td>
{{/if}}