{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=datasources value=false}}
{{mb_default var=elastic_datasources value=false}}

<script>
  Main.add(function () {
    Control.Tabs.create("tabs-datasources", true);
  });
</script>
<ul id="tabs-datasources" class="control_tabs">
  <li><a href="#sqlDatasources">{{tr}}CSQLDatasource|pl{{/tr}}</a></li>
  <li><a href="#nosqlDatasources">{{tr}}CElasticDatasource|pl{{/tr}}</a></li>
</ul>

<div id="sqlDatasources" style="display: none;">
    {{mb_include module=system template=inc_view_sql_datasources datasources=$datasources}}
</div>

<div id="nosqlDatasources" style="display: none;">
    {{mb_include module=system template=inc_view_elastic_datasources datasources=$elastic_datasources}}
</div>
