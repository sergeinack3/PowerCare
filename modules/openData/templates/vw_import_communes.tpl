{{*
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=openData script=import_communes}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs_import_commune');
  });
</script>

<ul class="control_tabs" id="tabs_import_commune">
  <li><a href="#tab_import_commune_france">{{tr}}mod-openData-import-commune-france{{/tr}}</a></li>
  {{*<li><a href="#tab_import_commune_suisse">{{tr}}mod-openData-import-commune-suisse{{/tr}}</a></li>*}}
</ul>

<div id="tab_import_commune_france" style="display: none;">
  {{mb_include module=openData template=vw_import_communes_france}}
</div>
{{*<div id="tab_import_commune_suisse" style="display: none;">*}}
  {{*Non développé*}}
{{*</div>*}}