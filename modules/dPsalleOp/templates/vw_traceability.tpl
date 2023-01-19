{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=salleOp script=traceability}}


<script>
  Main.add(function () {
    Control.Tabs.create('traceability_tabs', true, {
        afterChange: function (container) {
          switch (container.id) {
            case "checklist":
              Traceability.loadChecklists();
              break;
            case "radiologie":
              Traceability.loadRadiologie()
          }
        }
      }
    );
  });
</script>

<ul class="control_tabs" id="traceability_tabs">
  <li>
    <a href="#checklist">{{tr}}mod-dPsalleOp-tab-vw_traceability-checklist{{/tr}}</a>
  </li>
  <li>
    <a href="#radiologie">{{tr}}mod-dPsalleOp-tab-vw_traceability-radiologie{{/tr}}</a>
  </li>
</ul>

<div id="checklist" style="display: none;"></div>
<div id="radiologie" style="display: none;"></div>
