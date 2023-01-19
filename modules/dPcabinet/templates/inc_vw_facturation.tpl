{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !"dPfacturation"|module_active}}
<div class="small-warning">
  {{tr}}CFactureCabinet-msg-Please activate the Billing module to use invoices{{/tr}}
</div>
{{else}}
  {{mb_include module=facturation template=inc_vw_facturation}}
{{/if}}