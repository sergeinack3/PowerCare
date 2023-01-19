{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Import de prestations.</h2>

<div class="big-info">
  Téléversez un fichier CSV, encodé en <code>ISO-8859-1</code> (Western Europe),
  séparé par des point-virgules (<code>;</code>) et
  délimité par des guillemets doubles (<code>"</code>).
  <br />
  La première ligne du fichier doit contenir les champs suivants (noms identiques) séparés par un point-virgule (;) :
  <ol>
    <li><strong>prestation</strong> : Nom de la prestation (Prestation importée si non retrouvée)</li>
    <li><strong>type</strong> : Type de prestation (ponctuelle ou journaliere)</li>
    <li>type_admission : Type d'admission ({{$type_adm}}). Si vide tous les types.</li>
    <li>M : Prise en charge médecine (1 si oui, 0 si non)</li>
    <li>C : Prise en charge chirurgie (1 si oui, 0 si non)</li>
    <li>O : Prise en charge obstétrique (1 si oui, 0 si non)</li>
    <li>SSR : Prise en charge SSR (1 si oui, 0 si non)</li>
    <li><strong>item</strong> : Nom de l'item</li>
    <li><strong>rang</strong> : Le rang de l'item</li>
    <li>identifiant_externe : Identifiants externes à ajouter à l'item (séparés par |)</li>

    {{mb_include module=system template=inc_import_csv_info_outro}}

    <form method="post" name="import" action="?m=dPhospi&a=ajax_import_prestation_csv" enctype="multipart/form-data"
          onsubmit="return onSubmitFormAjax(this, {useFormAction: true}, 'result-import');">
      <input type="hidden" name="m" value="dPhospi" />
      <input type="hidden" name="a" value="ajax_import_prestation_csv" />

      <table class="main form">
        <tr>
          <th style="width: 50%;">{{tr}}File{{/tr}}</th>
          <td>
            {{mb_include module=system template=inc_inline_upload paste=false extensions=csv multi=false}}
          </td>
        </tr>

        <tr>
          <th><label for="update">{{tr}}mod-dPhospi-import-presta-update{{/tr}}</label></th>
          <td><input type="checkbox" name="update" value="1" /></td>
        </tr>

        <tr>
          <th><label for="dryrun">{{tr}}DryRun{{/tr}}</label></th>
          <td><input type="checkbox" name="dryrun" value="1" checked /></td>
        </tr>

        <tr>
          <td class="button" colspan="2">
            <button class="import" type="submit">{{tr}}Import{{/tr}}</button>
          </td>
        </tr>
      </table>
    </form>

    <div id="result-import"></div>