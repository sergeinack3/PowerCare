{{*
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  importTables = function () {
    var url = new Url('rpps', 'ajax_create_schema');
    url.requestUpdate('result-config-ds');
  }

  populateTables = function () {
  var url = new Url('rpps', 'ajax_populate_database');
  url.requestUpdate('result-config-ds');
  }
</script>

{{if !$can_load_local}}
  <div class="small-error">
    {{tr}}CExternalMedecinBulkImport-msg-Error-Cannot load local data infile{{/tr}}
  </div>
{{/if}}

<div>
  {{mb_include module=system template=configure_dsn dsn=rpps_import}}
</div>

<div style="padding-top: 10px">
  <button class="tick" type="button" onclick="importTables();">
    {{tr}}CExternalMedecinBulkImport-Action-Create tables{{/tr}}
  </button>

  <br/>
  <br/>

  <button class="tick" type="button" onclick="populateTables();" {{if !$can_load_local}}disabled{{/if}}>
    {{tr}}CExternalMedecinBulkImport-Action-Populate tables{{/tr}}
  </button>
</div>

<div id="result-config-ds" style="padding-top: 10px"></div>
