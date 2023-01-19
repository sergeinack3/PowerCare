{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

.sejour-type-default > td::before {
  background-color: {{if "dPhospi colors default"|gconf|substr:0:1 !== "#"}}#{{/if}}{{"dPhospi colors default"|gconf}} !important;
}

.sejour-type-ambu > td::before {
  background-color: {{if "dPhospi colors ambu"|gconf|substr:0:1 !== "#"}}#{{/if}}{{"dPhospi colors ambu"|gconf}} !important;
}

.sejour-type-comp > td::before {
  background-color: {{if "dPhospi colors comp"|gconf|substr:0:1 !== "#"}}#{{/if}}{{"dPhospi colors comp"|gconf}} !important;
}

.sejour-type-exte > td::before {
  background-color: {{if "dPhospi colors exte"|gconf|substr:0:1 !== "#"}}#{{/if}}{{"dPhospi colors exte"|gconf}} !important;
}

.sejour-type-consult > td::before {
  background-color: {{if "dPhospi colors consult"|gconf|substr:0:1 !== "#"}}#{{/if}}{{"dPhospi colors consult"|gconf}} !important;
}
