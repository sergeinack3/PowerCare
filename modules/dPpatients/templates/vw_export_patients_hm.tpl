{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPpatients script=export_patients_hm ajax=true}}

{{mb_default var=count value=500}}
{{mb_default var=start value=0}}
{{mb_default var=continue value=0}}

<h2>{{tr}}CPatient-hm-export{{/tr}} : {{$group}}</h2>

<div class="small-info">
  <ul>
    <li>
      <strong>L'export sera effectué pour l'établissement courant.</strong>
    </li>
    <li>
      Si vous voulez exporter les patients de tous les établissement il faut lancer le script pour chaque établissement.
      Un fichier sera généré pour chaque établissement.
    </li>
    <li>
      <strong>{{tr}}CPatient-hm-export-count{{/tr}}</strong> : Ce champ permet de choisir combien de patients vont être parcourus à
      chaque passe. Il est déconseillé de mettre cette valeur à plus de <strong>1000</strong> pour éviter de ralentir le serveur.
    </li>
    <li>
      <strong>{{tr}}CPatient-hm-export-start{{/tr}}</strong> : Ce champ permet de choisir à partir de quel patient commencer.
    </li>
    <li>
      <strong>{{tr}}CPatient-hm-export-continue{{/tr}}</strong> : Si cette case est cochée alors l'export se fera de manière
      automatisé lors du clique sur le bouton {{tr}}Export{{/tr}}.
    </li>
    <li>
      <strong>Une fois la totalité des patients exporté vous pouvez télécharger le fichier via le bouton
        {{tr}}dPpatients-export-hm-download{{/tr}}</strong>
    </li>
    <li>
      <strong>
        Tous les patients sont parcourus mais seuls les patients avec un IPP sur l'établissement actuel sont exportés.
      </strong>
    </li>
  </ul>
</div>

{{if !$finess}}
  <div class="error">
    {{tr}}CPatient-hm-export-finess.none{{/tr}}
  </div>
{{/if}}

{{if $file_exists}}
  <div class="small-warning">
    Un fichier d'export existe déjà pour cet établissement. Si vous continuez l'export les nouvelles lignes seront ajoutées à la fin
    du fichier. <br /> Vous pouvez vider le fichier d'export en utilisant le bouton suivant :
    <br />
    <span id="export-hm-remove-file">
      <button class="trash" type="button"
              onclick="ExportPatientsHm.removeFile();">{{tr}}dPpatients-export-hm-file-delete{{/tr}}</button>
    </span>
  </div>
{{/if}}

<form name="do-export-patients-hm" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-export-patients')">
  <input type="hidden" name="m" value="dPpatients" />
  <input type="hidden" name="dosql" value="do_export_patients_hm" />
  <input type="hidden" name="max" value="{{$total_patients}}" />

  <table class="main form">
    <tr>
      <th><label for="count">{{tr}}CPatient-hm-export-count{{/tr}}</label></th>
      <td>
        <input type="number" name="count" value="{{$count}}" size="5" />
      </td>
    </tr>

    <tr>
      <th><label for="start">{{tr}}CPatient-hm-export-start{{/tr}}</label></th>
      <td>
        <input type="number" name="start" value="{{$start}}" size="5" /> Total : {{$total_patients}}
      </td>
    </tr>

    <tr>
      <th><label for="continue">{{tr}}CPatient-hm-export-continue{{/tr}}</label></th>
      <td>
        <input type="checkbox" name="continue" value="1" {{if $continue}}checked{{/if}}/>
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="submit" class="change">
          {{tr}}Export{{/tr}}
        </button>
        <a id="export-hm-download" class="button download" href="?m=dPpatients&raw=ajax_download_hm_file" target="_blank">
          {{tr}}dPpatients-export-hm-download{{/tr}}
        </a>
      </td>
    </tr>
  </table>
</form>

<div id="result-export-patients"></div>
