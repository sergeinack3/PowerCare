{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $sejour->_id}}
  {{mb_include module=pmsi template=inc_vw_dossier_sejour_pmsi object=$sejour}}
{{else}}
  <div class="big-info">
    Vous devez séléctionner un séjour pour accéder au dossier
  </div>
{{/if}}