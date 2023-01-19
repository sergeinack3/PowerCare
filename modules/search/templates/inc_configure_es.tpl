{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=search script=Search ajax=true}}

<table class="main tbl" id="tab_config_es">
  <tbody id="first_indexing">
  <tr>
    <td>
      <table class="form">
        <tr>
          <th class="category" colspan="3">{{tr}}CConfigServeur{{/tr}} 1 - Actions sur l'index</th>
        </tr>
        <tr>
          <td>
            <button class="new singleclick" type="submit" onclick="Search.createIndex('generique')">Créer le schéma Nosql</button>
              {{if $exist_index}}
                <span style="color:red;">L'index "{{$index}}" existe déjà.</span>
              {{/if}}
          </td>
        </tr>
        <tr>
          <th class="category" colspan="3"> 2 - Actions sur la table tampon</th>
        </tr>
        <tr>
          <td>
            <button class="new singleclick" type="submit" onclick="Search.createData();">Remplir la table de données</button>
            <span style="color:blue;">Il y a {{$nbr_doc|integer}} documents dans la table.</span>
          </td>
        </tr>
        <tr>
          <th class="category" colspan="3"> 3 - Tests Indexation</th>
        </tr>
        <tr>
          <td id="indexation">

            <form name="search-index-data" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-index-data')">
              <input type="hidden" name="m" value="search" />
              <input type="hidden" name="a" value="ajax_index_data" />
              <input type="hidden" name="continue" value="1">
              <button type="submit" class="save singleclick">Indexer les données</button>
              <label for="continue">{{tr}}common-Automatic{{/tr}}</label><input type="checkbox" checked onclick="$V(this.form.continue, ($V(this)) ? 1 : 0)" />
            </form>
            <br>
            <div id="result-index-data"></div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  </tbody>
</table>
