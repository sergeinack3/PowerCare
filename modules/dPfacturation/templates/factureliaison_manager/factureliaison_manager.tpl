{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=tools ajax=true}}
{{mb_script module=facturation script=facture ajax=true}}
{{mb_script module=patients  script=pat_selector}}
{{mb_script module=cabinet  script=edit_consultation}}

{{mb_include template="factureliaison_manager/factureliaison_manager_filter"}}

<style>
</style>
<div id="factureliaison_lists" class="me-padding-0">
  {{mb_include module=facturation template="factureliaison_manager/factureliaison_manager_lists"}}
</div>
