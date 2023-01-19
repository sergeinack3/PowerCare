{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=modal value=false}}

{{if $modal}}
  <div id="cim10_details">
{{/if}}

{{if $code}}
  {{mb_include module=cim10 template="cim/$version/inc_details"}}
{{else}}
  <div class="empty">
    {{tr}}CCIM10-code.none{{/tr}}
  </div>
{{/if}}

{{if $modal}}
  </div>
{{/if}}