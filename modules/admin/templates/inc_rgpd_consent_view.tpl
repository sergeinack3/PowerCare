{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=consent value=false}}

{{if !$consent || !$consent->_id}}
  {{mb_return}}
{{/if}}

<div style="position: relative; width: 70px;" onmouseover="ObjectTooltip.createEx(this, '{{$consent->_guid}}');">
  <strong style="border: solid 1px; border-radius: 3px; padding: 4px;">{{mb_value object=$consent field=tag}}</strong>

  <div class="CRGPDConsent_status_counter CRGPDConsent_status_{{$consent->status}}" style="text-align: center;">
    {{mb_value object=$consent field=status}}
  </div>
</div>