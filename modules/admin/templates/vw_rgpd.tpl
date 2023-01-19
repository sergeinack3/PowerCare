{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('rgpd-tabs', true);
  });
</script>

<ul id="rgpd-tabs" class="control_tabs">
  <li><a href="#CRGPDConsent-tab">{{tr}}CRGPDConsent|pl{{/tr}}</a></li>
  <li><a href="#rgpd-config-tab">{{tr}}common-action-Configure{{/tr}}</a></li>
  <li><a href="#rgpd-maintenance-tab">Maintenance</a></li>
</ul>

<div id="CRGPDConsent-tab" style="display: none;">
  {{mb_include module=admin template=vw_consents}}
</div>

<div id="rgpd-config-tab" style="display: none;">
  {{mb_include module=admin template=rgpd_configure}}
</div>

<div id="rgpd-maintenance-tab" style="display: none;">
  {{mb_include module=admin template=rgpd_maintenance}}
</div>
