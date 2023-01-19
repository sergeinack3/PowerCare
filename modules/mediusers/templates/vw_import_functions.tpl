{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=import_analyzer ajax=1}}

<script>
  checkInputs = function (form) {
    if ($V(form.import_file)) {
      $('import-button').enable();
    }
    else {
      $('import-button').disable();
    }
  };

  checkImportFile = function(form) {
    ImportAnalyzer.init();
    ImportAnalyzer.fields = {{$import_specs|@json}};
    ImportAnalyzer.run($('do-import-functions_import_file'), {
      callback: function() {
        var errorMsg = false;
        ImportAnalyzer.messages.each(function(msg) {
          if (msg.type == 'error') {
            errorMsg = true;
            throw $break;
          }
        });

        if (Object.keys(ImportAnalyzer.unique_messages).length > 0) {
          $H(ImportAnalyzer.unique_messages).each(function(msg) {
            if (msg.value.type == 'error') {
              errorMsg = true;
              throw $break;
            }
          });
        }

        if (errorMsg || Object.keys(ImportAnalyzer.field_errors).length > 0) {
          ImportAnalyzer.options.outputLogs = true;
          ImportAnalyzer.options.outputData = true;
          ImportAnalyzer.output();
        }
        else {
          form.submit();
        }
      }.bind(form)
    });
  };

  afterImport = function(msg_ok, msg_err) {
    Control.Modal.close();
    var url = new Url('mediusers', 'ajax_show_import_log');
    url.addParam('log_ok[]', msg_ok);
    url.addParam('log_err[]', msg_err);
    url.requestModal(null, null, {onClose: function() {
        var url = new Url('mediusers', 'vw_idx_functions');
        url.requestUpdate('functions');
      }});
  };

  Main.add(function() {
    require.config({
      paths: {
        Papa: 'lib/PapaParse/papaparse.min'
      }
    });
  });
</script>

<h2>{{tr}}CFunctions-import|pl{{/tr}}</h2>

<div class="small-info">
  Le fichier d'import doit être au format ISO, les champs séparés par des <strong>;</strong> et les textes entourés par <strong>"</strong>

  <br/>
  <br/>

  La première ligne doit être la suivante : <br/>
  <strong>
    "intitule";"sous-titre";"type";"couleur";"initales";"adresse";"cp";"ville";"tel";"fax";"mail";"siret";"quotas";"actif";
    "compta_partage";"consult_partage";"adm_auto";"facturable";"creation_sejours";"ufs"
  </strong>

  <br/>
  <br/>
  Description des champs :
  <ul>
    <li><strong>intitule</strong> : {{tr}}CFunctions-text{{/tr}}</li>
    <li>sous-titre : {{tr}}CFunctions-soustitre{{/tr}}</li>
    <li><strong>type</strong> : {{tr}}CFunctions-type{{/tr}}</li>
    <li>couleur : {{tr}}CFunctions-color{{/tr}}</li>
    <li>initales : {{tr}}CFunctions-initials{{/tr}}</li>
    <li>adresse : {{tr}}CFunctions-adresse{{/tr}}</li>
    <li>cp : {{tr}}CFunctions-cp{{/tr}}</li>
    <li>ville : {{tr}}CFunctions-ville{{/tr}}</li>
    <li>tel : {{tr}}CFunctions-tel{{/tr}}</li>
    <li>fax : {{tr}}CFunctions-fax{{/tr}}</li>
    <li>mail : {{tr}}CFunctions-email{{/tr}}</li>
    <li>siret : {{tr}}CFunctions-siret{{/tr}}</li>
    <li>quotas : {{tr}}CFunctions-quotas{{/tr}}</li>
    <li>actif : {{tr}}CFunctions-actif{{/tr}}</li>
    <li>compta_partage : {{tr}}CFunctions-compta_partagee{{/tr}}</li>
    <li>consult_partage : {{tr}}CFunctions-consults_events_partagees{{/tr}}</li>
    <li>adm_auto : {{tr}}CFunctions-admission_auto{{/tr}}</li>
    <li>facturable : {{tr}}CFunctions-facturable{{/tr}}</li>
    <li>creation_sejours : {{tr}}CFunctions-create_sejour_consult{{/tr}}</li>
    <li>ufs : Codes des unités fonctionnelles médicales séparés par | (les UF ne sont pas importées si non retrouvées via leur code)</li>
    <li>ufs_secondaires : Codes des unités fonctionnelles médicales secondaires séparés par | (les UF ne sont pas importées si non retrouvées via leur code)</li>
  </ul>

  <br/><br/>

  <strong>Attention :</strong>
  <ul>
    <li>Aucune unité fonctionnelle ne sera créée, elles doivent déjà exister</li>
  </ul>
  <br/>
</div>

<iframe name="import-functions-frame" id="import-functions-frame" style="display: none;"></iframe>

<form name="do-import-functions" method="post" action="?" enctype="multipart/form-data" target="import-functions-frame">
  <input type="hidden" name="m" value="mediusers"/>
  <input type="hidden" name="dosql" value="do_import_functions"/>

  <label for="import_file" title="Fichier à importer" style="margin-left: 10px;">{{tr}}File{{/tr}}</label>
  : <input type="file" name="import_file" size="0" onchange="checkInputs(this.form);"/>
  <button id="import-button" type="button" class="import" onclick="checkImportFile(this.form);">
    {{tr}}Import{{/tr}}
  </button>
</form>

<div id="import-results-loading" class="small-info" style="display: none;">
  {{tr}}common-file-analysis-in-progress{{/tr}}
</div>

<div id="import_results_header"></div>
<div id="import_results_data"></div>
