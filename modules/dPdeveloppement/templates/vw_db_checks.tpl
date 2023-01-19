{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-db-checks', true, {
      afterChange: function (container) {
        var script;
        switch (container.id) {
          case 'tables':
            script = 'mnt_table_classes';
            break;
          case 'indexes':
            script = 'vw_indexes';
            break;
          case 'tables-integrity':
            script = 'vw_tables_integrity';
            break;
          default:
              script = '';
        }

        var url = new Url('dPdeveloppement', script);
        url.requestUpdate(container);
      }
    });
  });
</script>

<ul id="tabs-db-checks" class="control_tabs">
  <li><a href="#tables">{{tr}}mod-dPdeveloppement-tab-mnt_table_classes{{/tr}}</a></li>
  <li><a href="#indexes">{{tr}}mod-dPdeveloppement-tab-vw_indexes{{/tr}}</a></li>
  <li><a href="#tables-integrity">{{tr}}mod-dPdeveloppement-tab-vw_tables_integrity{{/tr}}</a></li>
</ul>

<div id="tables" style="display: none;"></div>
<div id="indexes" style="display: none;"></div>
<div id="tables-integrity" style="display: none;"></div>
