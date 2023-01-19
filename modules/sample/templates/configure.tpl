{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=sample script=sample_import}}

<script>
  Main.add(function() {
    Control.Tabs.create('sample-configure-tabs');
  });
</script>

<div>
  <ul class="control_tabs" id="sample-configure-tabs">
    <li><a href="#sample-configure-imports">{{tr}}SampleConfigureController-Tab-Import|pl{{/tr}}</a></li>
    <li><a href="#sample-configure-source">{{tr}}SampleConfigureController-Tab-Configure source{{/tr}}</a></li>
  </ul>
</div>

<div class="sample-configure" id="sample-configure-imports" style="display: none;">
  <div class="sample-configure-import">
    <div>
      <button type="button" class="import" onclick="SampleImport.importCategories();">
          {{tr}}SampleCategoryImport-Action-Import{{/tr}}
      </button>
    </div>
    <div id="result-import-categories"></div>
  </div>

  <div class="sample-configure-import">
    <div>
      <button type="button" class="import" onclick="SampleImport.importNationalities();">
          {{tr}}SampleNationalityImport-Action-Import{{/tr}}
      </button>
    </div>
    <div id="result-import-nationalities"></div>
  </div>

  <div class="sample-configure-import">
    <div>
      <button type="button" class="import" {{if !$source_available}}disabled{{else}}onclick="SampleImport.importMovies('{{$base_url}}');"{{/if}}>
          {{tr}}SampleMovieImport-Action-Import{{/tr}}
      </button>
    </div>
    <div id="result-import-movies" style="position: relative;">
      {{if !$source_available}}
          <div class="small-warning">
              <div>{{tr}}SampleMovieImport-Info-Please-create-http-source{{/tr}}</div>
              <div>
                  {{tr}}SampleMovieImport-Info-You-must-create-an-account-on{{/tr}}
                  <a href="https://www.themoviedb.org" target="_blank">https://www.themoviedb.org</a>
              </div>
              <div>{{tr}}SampleMovieImport-Info-You-must-create-an-api-key-on-the-site{{/tr}}</div>
              <div>{{tr}}SampleMovieImport-Info-You-must-set-the-api-key-as-token-in-the-http-source{{/tr}}</div>

          </div>
      {{/if}}
    </div>
  </div>
</div>

<div id="sample-configure-source" style="display: none;">
    {{mb_include module=system template=inc_config_exchange_source source=$source}}
</div>

