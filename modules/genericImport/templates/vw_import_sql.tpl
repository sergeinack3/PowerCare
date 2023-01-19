{{*
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-import', true,
      {
        afterChange: function (container) {
          var url = new Url('genericImport', container.id);
          url.addParam('import_type', '{{$import_type}}')
          url.requestUpdate(container);
        }
      }
    );
  });
</script>

<ul id="tabs-import" class="control_tabs">
  <li><a href="#vw_users_fw">{{tr}}mod-genericImport-tab-vw_users_fw_sql{{/tr}}</a></li>
  <li><a href="#vw_import_fw">{{tr}}mod-genericImport-tab-vw_import_fw_sql{{/tr}}</a></li>
</ul>

<div id="vw_users_fw" style="display: none;"></div>
<div id="vw_import_fw" style="display: none;"></div>
