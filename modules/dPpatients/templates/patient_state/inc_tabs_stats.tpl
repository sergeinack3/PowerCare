{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        Control.Tabs.create(
            "tabs-stats-identity-management",
            true,
            {
                afterChange: function (container) {
                    switch (container.id) {
                        case "stats_patient_state":
                            PatientState.statsFilter();
                            break;
                        {{if "ameli"|module_active && "ameli INSi active"|gconf}}
                        case "stats_insi":
                            INSi.statsFilter();
                            break;
                        {{/if}}
                    }
                }
            }
        );
    });
</script>

<ul id="tabs-stats-identity-management" class="control_tabs">
  <li><a href="#stats_patient_state">{{tr}}CPatientState-tab-Stats patient state{{/tr}}</a></li>
  <li><a href="#stats_insi">{{tr}}CINSiLog-tab-Stats insi teleservice{{/tr}}</a></li>
</ul>

<div id="stats_patient_state" style="display: none;"></div>
<div id="stats_insi" style="display: none;"></div>
