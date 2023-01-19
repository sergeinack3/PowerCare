{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create("tabs-actions", true, {});
  });
</script>

<table>
  <tr>
    <td style="vertical-align: top;">
      <ul id="tabs-actions" class="control_tabs_vertical small">
        <li><a href="#prenom_sexe-maintenance">Gestion des paires prénom - sexe</a></li>
        <li><a href="#civilite_sexe-maintenance">{{tr}}mod-system-repair-civilite{{/tr}}</a></li>
        <li><a href="#pseudonymisation">{{tr}}system-pseudonymise{{/tr}}</a></li>
      </ul>
    </td>
    <td style="vertical-align: top; width: 100%;">
      <div id="prenom_sexe-maintenance" style="display: none;">
        {{mb_include template=inc_maintenance_prenom_sexe}}
      </div>
      <div id="civilite_sexe-maintenance" style="display: none">
        {{mb_include module=system template=inc_maintenance_civilite}}
      </div>
      <div id="pseudonymisation" style="display: none">
        {{mb_include module=system template="pseudonymise/vw_pseudonymise"}}
      </div>
    </td>
  </tr>
</table>