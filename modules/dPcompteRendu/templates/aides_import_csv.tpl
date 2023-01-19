{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>{{tr var1=$owner}}CAideSaisie-import_for{{/tr}}</h2>

{{if $object_class == "CTransmissionMedicale" && "dPprescription"|module_active}}
  <div class="small-info">
    Téléversez un fichier CSV, encodé en <code>ISO-8859-1</code> (Western Europe), séparé par des virgules (<code>,</code>) et délimité par des guillemets doubles (<code>"</code>).

    <br />
    La première ligne sera ignorée et les suivantes devront comporter les champs suivants :

    <ol>
      <li>CTransmissionMedicale <strong>*</strong></li>
      <li>text <strong>*</strong></li>
      <li>Intitulé de l'aide à la saisie</li>
      <li>Texte de l'aide à la saisie</li>
      <li>data (Donnée) ou action (Action) ou result (Résultat)</li>
      <li>Nom de la catégorie de prescription</li>
      {{assign var=cats value='Ox\Mediboard\Prescription\CCategoryPrescription'|static:chapitres_elt}}
      <li>Chapitre de la catégorie
        (
          {{foreach from=$cats item=_cat name=cats}}
            {{$_cat}} ({{tr}}CCategoryPrescription.chapitre.{{$_cat}}{{/tr}}){{if !$smarty.foreach.cats.last}}, {{/if}}
          {{/foreach}}
        )</li>
    </ol>

    <hr />

    <strong>*</strong> : Recopier le champ tel quel
  </div>
{{/if}}

<form method="post" action="?m={{$m}}&{{$actionType}}={{$action}}&dialog=1&owner_guid={{$owner_guid}}" name="import" enctype="multipart/form-data">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="{{$actionType}}" value="{{$action}}" />

  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <input type="file" name="import" />
  
  <button type="submit" class="submit">{{tr}}Save{{/tr}}</button>
</form>

{{$app->getMsg()|smarty:nodefaults}}