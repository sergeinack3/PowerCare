{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=programme_patient ajax=true}}
<script>
  Main.add(function () {
    Control.Tabs.create('programme_tabs', true);
  });
</script>

<ul id="programme_tabs" class="control_tabs small">
  <li>
    <a href="#programmes">{{tr}}CProgrammeClinique{{/tr}}</a>
  </li>
  <li {{if !"dPpatients CPatient show_rules_alert"|gconf}}style="display: none;"{{/if}}>
    <a href="#regles">{{tr}}CRegleAlertePatient{{/tr}}</a>
  </li>
</ul>

<div id="programmes" style="display: none;">
  {{mb_include module=patients template=inc_vw_programmes}}
</div>
<div id="regles" style="display: none;">
  {{mb_include module=patients template=vw_regles_alerte_evt}}
</div>