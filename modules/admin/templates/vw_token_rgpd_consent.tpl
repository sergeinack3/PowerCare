{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $consent->isAccepted()}}
  <div class="small-info">
    Vous venez d'autoriser le traitement de vos données personnelles.
  </div>
{{elseif $consent->isRefused()}}
  <div class="small-info">
    Vous venez de refuser le traitement de vos données personnelles.
  </div>
{{/if}}