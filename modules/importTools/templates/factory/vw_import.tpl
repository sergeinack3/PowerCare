{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=importTools script=DatabaseExplorer}}

<script>
  Main.add(function () {
    Control.Tabs.create("{{$m}}-import-tabs", true);
  });
</script>

<style>
  progress.import_progress {
    height: 12px;
    color: black;
    display: inline-block;
    text-shadow: none;
  }

  progress.import_progress:after {
    display: block;
    content: attr(value) "/" attr(max);
    text-align: center;
  }
</style>

{{assign var=medi_user value=$app->_ref_user}}
{{assign var=cab value=$medi_user->loadRefFunction()}}
{{if !$cab || $cab->type == "administratif"}}
  <div class="small-error">
    Vous êtes connecté avec l'utilisateur <strong>{{$medi_user}}</strong>
    {{if $cab}}
      d'un cabinet administratif <strong>({{$cab}})</strong> !
    {{else}}
      relié à aucun cabinet !
    {{/if}}
    <br/>
    Vous devriez changer d'utilisateur avant de commencer l'import.
  </div>
{{else}}
  <div class="small-info">
    Vous êtes connecté avec l'utilisateur <strong>{{$medi_user}}</strong> relié au cabinet <strong>{{$cab}}</strong>.
  </div>
{{/if}}


<table class="main layout">
  <tr>
    <td style="width: 300px;">
      <ul class="control_tabs_vertical small" id="{{$m}}-import-tabs" style="white-space: nowrap;">
        {{foreach from=$instances key=_class item=_instance name=instances}}
          <li>
            <a href="#{{$_class}}" style="vertical-align: middle; line-height: 12px;">
              <span style="float: left;">{{$smarty.foreach.instances.iteration}}</span>
              {{tr}}{{$_class}}{{/tr}}

              {{if $_instance->getOrderBy()}}
                {{me_img src="calendar.gif" icon="agenda" class="me-primary" height=12}}
              {{/if}}
              {{if $_instance->getPatientField()}}
                <img src="images/icons/user.png" height="12"/>
              {{/if}}

              <progress class="import_progress" value="{{$_instance->_stats_imported}}" max="{{$_instance->_stats_total}}"></progress>
            </a>
          </li>
        {{/foreach}}
      </ul>
    </td>
    <td>
      {{foreach from=$instances key=_class item=_instance}}
        <script>
          Main.add(function () {
            var form = getForm("import-{{$_class}}");

            form.count.addSpinner({min: 1});
            Calendar.regField(form.date_min);
            Calendar.regField(form.date_max);
          });

          function next{{$_class}}() {

            var form = getForm("import-{{$_class}}");

            if (!$V(form["continue"])) {
              return;
            }

            form.onsubmit();
          }
        </script>
        <div id="{{$_class}}" style="display: none;">
          <h3>{{tr}}{{$_class}}{{/tr}}</h3>
          <table class="main layout">
            <tr>
              <td style="white-space: nowrap; width: 200px;">
                <form name="import-{{$_class}}" method="get" action="?"
                      onsubmit="return onSubmitFormAjax(this, null, 'import-log-{{$_class}}')">
                  <input type="hidden" name="m" value="{{$m}}"/>
                  <input type="hidden" name="a" value="ajax_import"/>
                  <input type="hidden" name="suppressHeaders" value="1"/>
                  <input type="hidden" name="import" value="{{$_instance|get_class}}"/>
                  <input type="hidden" name="nosleep" value="1"/>

                  <table class="main tbl">
                    <tr>
                      <th colspan="2" class="title">Import unitaire</th>
                    </tr>
                    <tr>
                      <th><label for="import_id">ID {{tr}}module-{{$m}}-court{{/tr}}</label></th>
                      <td>
                        <input type="text" name="import_id" value="" size="15"/>
                      </td>
                    </tr>

                    <tr>
                      <th colspan="2" class="title">Import multiple</th>
                    </tr>

                    {{if $_instance->getPatientField()}}
                      <tr>
                        <th><label for="patient_id">ID de patient</label></th>
                        <td>
                          <input type="text" name="patient_id" value="{{$patient_id}}"/>
                        </td>
                      </tr>
                    {{/if}}

                    <tr>
                      <th><label for="count">Nombre à importer</label></th>
                      <td>
                        <input type="text" name="count" value="100" size="3"/>
                      </td>
                    </tr>
                    <tr>
                      <th><label for="last_id">A partir de l'ID </label></th>
                      <td>
                        <input type="text" name="last_id" value="{{$last_id}}"/>
                      </td>
                    </tr>

                    <tr>
                      <td>
                        <label title="Limiter le nombre de résultats"><input type="checkbox" name="limit" {{if $_instance->_default_limit}}checked{{/if}}/> Limiter</label>
                        <br />
                        <label title="Forcer la réimportation"><input type="checkbox" name="reimport" /> Réimporter</label>
                      </td>

                      <td>
                        <label><input type="checkbox" name="continue" value="1" title="Automatique"/> Automatique</label>
                        <br/>
                        <label title="Corriger les dates de fichier"><input type="checkbox" name="correct_file" value="1"/> Corriger fichiers</label>
                      </td>
                    </tr>
                    {{if $_instance->getOrderBy()}}
                      <tr>
                        <th colspan="2" class="title">Options de date</th>
                      </tr>
                      <tr>
                        <td></td>
                        <td>
                          <label>
                            <input type="checkbox" name="order_by" value="1" title="Partir du plus récent"/>
                            Partir du plus récent
                          </label>
                        </td>
                      </tr>
                      <tr>
                        <th><label for="date_min">A partir de</label></th>
                        <td>
                          <input type="hidden" name="date_min" class="date" value="{{$date_min}}"/>
                        </td>
                      </tr>
                      <tr>
                        <th><label for="date_max">Jusqu'à</label></th>
                        <td>
                          <input type="hidden" name="date_max" class="date" value="{{$date_max}}"/>
                        </td>
                      </tr>
                    {{/if}}

                    <tr>
                      <th colspan="2" class="title">Options supplémentaires</th>
                    </tr>

                    <tr>
                      <th>
                        <label for="interval">
                          Interval entre les imports
                        </label>
                      </th>

                      <td>
                        <input type="number" name="interval" size="3" value=""/> seconde(s)
                      </td>
                    </tr>

                    <tr>
                      <td>
                        <label for="handlers">
                          <input type="checkbox" name="handlers" value="1"/> Activer les handlers
                        </label>
                      </td>

                      <td>
                      </td>
                    </tr>

                    <tr>
                      <th colspan="2" class="title">Débogage</th>
                    </tr>
                    <tr>
                      <td>
                        <label for="debug">
                          <input type="checkbox" name="debug" value="1"/> Debug import
                        </label>
                      </td>

                      <td>
                        <label for="query_trace">
                          <input type="checkbox" name="query_trace" value="1"/> Query trace
                        </label>
                      </td>
                    </tr>
                  </table>

                  <div style="text-align: right;">
                    <button type="button" class="search" onclick="DatabaseExplorer.analyze('{{$_class}}');">
                      {{tr}}common-action-Analyze{{/tr}}
                    </button>

                    <button type="button" class="undo" onclick="DatabaseExplorer.reset('{{$_class}}');">
                      {{tr}}common-action-Reset{{/tr}}
                    </button>

                    <button type="submit" class="change">Importer</button>
                  </div>

                  <div style="text-align: center;">
                    <button type="button" class="lookup" onclick="DatabaseExplorer.getImportedMaxID('{{$_class}}');">
                      {{tr}}CExternalDBImport-action-Get max imported ID{{/tr}}
                    </button>
                  </div>
                </form>
              </td>
              <td id="import-log-{{$_class}}" style="vertical-align: top;"></td>
            </tr>
          </table>
        </div>
      {{/foreach}}
    </td>
  </tr>
</table>