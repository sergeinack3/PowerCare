{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=import_analyzer ajax=1}}

<script>
  checkInputs = function (form) {
    if ($V(form.import_file)) {
      $('import_button').enable();
    }
    else {
      $('import_button').disable();
    }
  };

  checkImportFile = function (form) {
    ImportAnalyzer.init();
    ImportAnalyzer.fields = {{$import_specs|@json}};
    ImportAnalyzer.run($('doImportUF_import_file'), {
      callback: function () {
        var errorMsg = false;
        ImportAnalyzer.messages.each(function (msg) {
          if (msg.type == 'error') {
            errorMsg = true;
            throw $break;
          }
        });

        if (Object.keys(ImportAnalyzer.unique_messages).length > 0) {
          $H(ImportAnalyzer.unique_messages).each(function (msg) {
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

  afterImport = function (msg) {
    var url = new Url("dPhospi", "ajax_list_infrastructure");
    url.addParam('type_name', 'UF');
    url.requestUpdate('UF');
    Control.Modal.close();
    $("systemMsg").show().innerHTML = msg;
  };

  Main.add(function () {
    require.config({
      paths: {
        Papa: 'lib/PapaParse/papaparse.min'
      }
    });
  });
</script>

<h2>Import d'unités fonctionnelles</h2>

<div class="small-info">
  Le fichier d'import doit être au format ISO, les champs séparés par des <strong>;</strong> et les textes par <strong>"</strong>.
  <ul>
    <li><strong>code</strong> : Code de l'UF</li>
    <li><strong>libelle</strong> : Nom de l'UF</li>
    <li>type : hebergement, soins ou medicale</li>
    <li>type_sejour : Type de séjour : comp, ambu, exte, seances, ssr, psy, urg ou consult</li>
  </ul>
  <br />
  <em>Les champs en gras sont obligatoires.</em><br />
  <em>La première ligne du fichier est ignorée.</em>
  <br /><br />
</div>

<iframe name="import_uf_frame" id="import_uf_frame" style="display: none;"></iframe>

<form name="doImportUF" method="post" action="?" enctype="multipart/form-data" target="import_uf_frame">
  <input type="hidden" name="m" value="dPhospi" />
  <input type="hidden" name="dosql" value="do_import_uf" />

  <label for="import_file" title="Fichier à importer" style="margin-left: 10px;">
    {{tr}}File{{/tr}}</label> : <input type="file" name="import_file" size="0" onchange="checkInputs(this.form);" />
  <button id="import_button" type="button" class="import" onclick="checkImportFile(this.form);">
    Importer
  </button>
</form>

<div id="import_results_loading" class="small-info" style="display: none;">
  Analyse du fichier en cours
</div>
<div id="import_results_header"></div>
<div id="import_results_data"></div>