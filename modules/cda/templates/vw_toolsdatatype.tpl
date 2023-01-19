{{*
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cda script=ccda}}
<div id="resultAction" class="me-padding-0">
  <table class="tbl">
    <tr>
      <th colspan="5">
        {{tr}}Action{{/tr}}
      </th>
    </tr>
    <tr>
      <td class="narrow">
        <button type="button" class="add" onclick="Ccda.action('createClass')">
          {{tr}}createClass{{/tr}}
        </button>
      </td>
      <td>
        <div class="small-info">
          Mise à jour des classes dans cda/classes/datatypes/voc/
        </div>
      </td>
    </tr>
    <tr>
      <td class="narrow">
        <button type="button" class="add" onclick="Ccda.action('createTest')">
          {{tr}}createTest{{/tr}}
        </button>
      </td>
      <td>
        <div class="small-info">Mise à jour des fichiers TestClasses.xsd et TestClassesCDA.xsd</div>
      </td>
    </tr>
    <tr>
      <td class="narrow">
        <button type="button" class="cleanup" onclick="Ccda.action('clearXSD')">
          {{tr}}clearXSD{{/tr}}
        </button>
      </td>
      <td>
        <div class="small-info">Nettoyage du fichier datatypes-base.xsd (maxOccurs, minOccurs, etc)</div>
      </td>
    </tr>
    <tr>
      <td class="narrow">
        <button type="button" class="search" onclick="Ccda.action('missClass')">
          {{tr}}missClass{{/tr}}
        </button>
      </td>
      <td>
        <div class="small-info">Vérification des éventuelles classes manquantes dans les différents dossiers</div>
      </td>
    </tr>
    <tr>
      <td class="narrow">
        <button type="button" class="add" onclick="Ccda.action('createClassXSD')">
          {{tr}}createClassXSD{{/tr}}
        </button>
      </td>
      <td>
        <div class="small-info">Création des classes dans le dossier cda/classes/structure/classesGenerate. Ces classes sont déjà
          commitées.
        </div>
      </td>
    </tr>
  </table>
  <br>
  <div id="resultCDA"></div>
  {{if $action == "null"}}
    {{mb_return}}
  {{/if}}
  <table class="tbl">
    <tr>
      <th>
        {{tr}}Result{{/tr}}
      </th>
    </tr>
    {{if $action != "missClass"}}
      <tr>
        <td>
          {{if $result == true}}
            {{tr}}Action done{{/tr}}
          {{else}}
            {{tr}}Action aborted{{/tr}}
          {{/if}}
        </td>
      </tr>
    {{else}}
      {{foreach from=$result item=_class}}
        <tr>
          <td>
            {{$_class}}
          </td>
        </tr>
        {{foreachelse}}
        <tr>
          <td>
            {{tr}}nothingClass{{/tr}}
          </td>
        </tr>
      {{/foreach}}
    {{/if}}
  </table>
</div>