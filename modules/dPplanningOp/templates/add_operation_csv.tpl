{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Ajout d'interventions dans Mediboard par CSV</h2>

<div class="small-info">
  Veuillez indiquez les champs suivants dans un fichier CSV (<strong>au format ISO</strong>) dont les champs sont séparés par
  <strong>;</strong> et les textes par <strong>"</strong> :
  <ul>
    <li>NDA *</li>
    <li>ADELI *</li>
    <li>DATE/HEURE DEBUT *</li>
    <li>DATE/HEURE FIN *</li>
    <li>LIBELLE *</li>
    <li>SALLE </li>
    <li>COTE (droit/gauche/bilateral/total/inconnu)</li>
  </ul>
  <em>* : champs obligatoires</em>
</div>

<form method="post" action="?m={{$m}}&{{$actionType}}={{$action}}&dialog=1" name="import" enctype="multipart/form-data">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
  
  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <input type="file" name="import" />
  
  <button class="submit">{{tr}}Save{{/tr}}</button>
</form>

{{if $results.count_nda_nt > 0 || $results.count_erreur > 0}}
  <div class="small-error">
    {{$results.count_nda_nt}} séjours n'ont pu être retrouvés par le NDA <br />
    {{$results.count_erreur}} interventions n'ont pu être importées
  </div>
{{/if}}

{{if $results.count_ok > 0}}
  <div class="small-success">
    {{$results.count_ok}} interventions importées avec succès
  </div>
{{/if}}
