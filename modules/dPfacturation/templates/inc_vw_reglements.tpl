{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $facture->_id}}
  {{assign var=object value=$facture}}
{{/if}}

<fieldset class="me-no-align me-no-box-shadow">
  <legend>{{tr}}CFactureCabinet-reglements{{/tr}} ({{tr}}{{$object->_class}}{{/tr}})</legend>
  {{if $object->du_patient}}
    {{mb_include  module=dPfacturation template=inc_vw_du_patient_reglements}}
  {{else}}
    {{mb_include  module=dPfacturation template=inc_vw_du_tiers_reglements}}
  {{/if}}
</fieldset>