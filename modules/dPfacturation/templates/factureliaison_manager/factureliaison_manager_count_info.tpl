{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=count_info value=false}}
{{mb_default var=nb_element value=false}}
{{if $count_info}}
  <div class="big-info">
    {{tr var1=$nb_element}}CFactureLiaison.Manager count info{{/tr}}
  </div>
{{/if}}