{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$curr_service->_ref_chambres item=curr_chambre}}
  {{if $curr_chambre->annule == 0}}
    {{mb_include module=hospi template=inc_affectations_chambres}}
  {{/if}}
{{/foreach}}