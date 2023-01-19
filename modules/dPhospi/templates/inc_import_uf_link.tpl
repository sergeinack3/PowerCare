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
      $('import-button').enable();
    }
    else {
      $('import-button').disable();
    }
  };

  checkImportFile = function (form) {
    ImportAnalyzer.init();
    ImportAnalyzer.fields = {{$import_specs|@json}};
    ImportAnalyzer.run($('do-import-uf-link_import_file'), {
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

  afterImport = function (msg_ok, msg_err) {
    Control.Modal.close();
    var url = new Url('dPhospi', 'ajax_show_import_log');
    url.addParam('log_ok[]', msg_ok);
    url.addParam('log_err[]', msg_err);
    url.requestModal(null, null, {
      onClose: function () {
        var url = new Url('dPhospi', 'ajax_list_infrastructure');
        url.addParam('type_name', 'UF');
        url.requestUpdate('UF');
      }
    });
  };

  Main.add(function () {
    require.config({
      paths: {
        Papa: 'lib/PapaParse/papaparse.min'
      }
    });
  });
</script>

<h2>{{tr}}CUniteFonctionnelle-import-link|pl{{/tr}}</h2>

<div class="small-info">
  Le fichier d'import doit être au format ISO, les champs séparés par des <strong>;</strong> et les textes entourés par
  <strong>"</strong>

  <br />
  <br />

  La première ligne doit être la suivante : <strong>"Nom du Service";"Nom de la chambre";"Nom du lit";"Code UF Hébergement";"Code UF
    Soins"</strong>
  <br />
  <br />
  Description des champs :
  <ul>
    <li><strong>Nom du Service</strong> : le nom du service</li>
    <li>Nom de la chambre : le nom de la chambre</li>
    <li>Nom du lit : le nom du lit</li>
    <li>Code UF Hébergement : code de l'UF d'hébergement</li>
    <li>Code UF Soins : code de l'UF de soins</li>
  </ul>

  <br /><br />

  <strong>Attention :</strong>
  <ul>
    <li>Le champ "Nom du Service" est obligatoire</li>
    <li>Au moins un des champs "Code UF Hébergement" ou "Code UF Soins" doit être remplit</li>
    <li>Le champ "Nom de la chambre" devient obligatoire si le champ "Nom du lit" est renseigné</li>
  </ul>
  <br />
</div>

<iframe name="import-uf-link-frame" id="import-uf-link-frame" style="display: none;"></iframe>

<form name="do-import-uf-link" method="post" action="?" enctype="multipart/form-data" target="import-uf-link-frame">
  <input type="hidden" name="m" value="dPhospi" />
  <input type="hidden" name="dosql" value="do_import_uf_link" />

  <label for="import_file" title="Fichier à importer" style="margin-left: 10px;">{{tr}}File{{/tr}}</label>
  : <input type="file" name="import_file" size="0" onchange="checkInputs(this.form);" />
  <button id="import-button" type="button" class="import" onclick="checkImportFile(this.form);">
    {{tr}}Import{{/tr}}
  </button>
</form>

<div id="import-results-loading" class="small-info" style="display: none;">
  {{tr}}common-file-analysis-in-progress{{/tr}}
</div>

<div id="import_results_header"></div>
<div id="import_results_data"></div>