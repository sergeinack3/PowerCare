{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "dPhospi prestations systeme_prestations"|gconf != $wanted}}
  <div class="small-warning">
    La <a href="?m=hospi&tab=configure">configuration</a> du
    <strong>{{tr}}config-dPhospi-prestations-systeme_prestations{{/tr}}
      {{tr}}config-dPhospi-prestations-systeme_prestations-{{"dPhospi prestations systeme_prestations"|gconf}}{{/tr}}
    </strong>
    n'est pas compatible avec l'usage de ces prestations.
  </div>
{{/if}}
