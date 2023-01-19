{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$pathologies_fields key=patho_field item=patho_name}}
  {{if $dossier->$patho_field}}
    <span class="texticon-pathology">
    {{tr}}{{$patho_name}}{{/tr}}
    <span class="texticon-delete" onclick="DossierMater.removePathologyTag('{{$patho_field}}', '{{$dossier->_id}}');">
      <i class="fas fa-times-circle" style="color:grey"></i>
    </span>
  </span>
  {{/if}}
{{/foreach}}
