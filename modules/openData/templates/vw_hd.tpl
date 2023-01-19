{{*
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


{{if $ds}}
  <script>
    Main.add(function () {
      Control.Tabs.create('tabs-hd', true);
    });
  </script>

  <ul class="control_tabs" id="tabs-hd">
    {{if $table_exists}}
      <li><a href="#tab-vw-hd-data">{{tr}}mod-openData-vw-hd-data{{/tr}}</a></li>
    {{/if}}
    <li><a href="#tab-import-hd">{{tr}}mod-openData-import-hd{{/tr}}</a></li>
    <li><a href="#tab-import-hd-finess">{{tr}}mod-openData-import-hd-finess{{/tr}}</a></li>
  </ul>

  {{if $table_exists}}
    <div id="tab-vw-hd-data" style="display: none;">
      {{mb_include module=openData template=vw_hd_data}}
    </div>
  {{/if}}
  <div id="tab-import-hd" style="display: none;">
    {{mb_include module=openData template=vw_import_hd}}
  </div>
  <div id="tab-import-hd-finess" style="display: none;">
    {{mb_include module=openData template=vw_import_hd_finess}}
  </div>
{{else}}
  <div class="small-error">
    {{tr}}mod-openData-hospiDiage-ds.none{{/tr}}
  </div>
{{/if}}
