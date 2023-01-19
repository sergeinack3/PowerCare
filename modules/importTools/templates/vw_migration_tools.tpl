{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create("migration-tools-tabs", true, {
      afterChange: function(container) {
        if (container.id == "migration") {
          var url = new Url('importTools', 'vw_migration');
          url.requestUpdate("migration");
        }
        else {
          if (container.id == 'integrity') {
            var url = new Url('importTools', 'vw_export_integrity');
            url.requestUpdate("integrity");
          }
        }
      }
    });
  });
</script>

<ul class="control_tabs small" id="migration-tools-tabs" style="white-space: nowrap;">
    <li><a href="#migration">{{tr}}importTools-migration-dashboard{{/tr}}</a></li>
    <li><a href="#integrity">{{tr}}importTools-migration-integrity{{/tr}}</a></li>
</ul>

<div id="migration" style="display: none;"></div>
<div id="integrity" style="display: none;"></div>