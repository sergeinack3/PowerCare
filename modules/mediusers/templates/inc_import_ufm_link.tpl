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
    ImportAnalyzer.run($('do-import-ufm-link_import_file'), {
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
    url.requestModal();
  };

  Main.add(function() {
    require.config({
      paths: {
        Papa: 'lib/PapaParse/papaparse.min'
      }
    });
  });
</script>

<h2>{{tr}}CUniteFonctionnelle-import-link-medicale|pl{{/tr}}</h2>

<div class="small-info">
  Le fichier d'import doit �tre au format ISO, les champs s�par�s par des <strong>;</strong> et les textes entour�s par <strong>"</strong>

  <br/>
  <br/>

  La premi�re ligne doit �tre la suivante : <strong>"Nom utilisateur";"Nom";"Pr�nom";"UF m�dicale"</strong>
  <br/>
  <br/>
  Description des champs :
  <ul>
    <li>Nom utilisateur : Nom de connexion de l'utilisateur (login)</li>
    <li>Nom : le nom de l'utilisateur</li>
    <li>Pr�nom : le pr�nom de l'utilisateur</li>
    <li><strong>UF m�dicale</strong> : code de l'UF m�dicale</li>
  </ul>

  <br/>

  <strong>Attention :</strong>
  <ul>
    <li>Le champ "UF m�dicale" est obligatoire</li>
    <li>Si le champ "Nom utilisateur" n'est pas remplit alors les champs "Nom" et "Pr�nom" doivent �tre remplis</li>
    <li>Si plusieurs utilisateurs ont les m�mes noms et pr�noms la ligne ne sera pas import�e et un message d'erreur indiquera le probl�me</li>
    <li>Le nom, pr�nom et login de l'utilisateur ne sont pas sensibles � la casse</li>
  </ul>
  <br/>
</div>

<iframe name="import-ufm-link-frame" id="import-ufm-link-frame" style="display: none;"></iframe>

<form name="do-import-ufm-link" method="post" action="?" enctype="multipart/form-data" target="import-ufm-link-frame">
  <input type="hidden" name="m" value="mediusers"/>
  <input type="hidden" name="dosql" value="do_import_ufm_link"/>

  <label for="import_file" title="Fichier � importer" style="margin-left: 10px;">{{tr}}File{{/tr}}</label>
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