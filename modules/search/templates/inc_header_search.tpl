{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        var list_thesaurus_entry = document.getElementById('list_thesaurus_entry');
        if (list_thesaurus_entry != null) {
            document.getElementById('button_manage_thesaurus').style.display = 'none';
        }
    });
</script>

<table class="main layout" id="search_print">
    <tr>
        <td id="td_container_search">
            <input type="search" id="words" name="words" class="autocomplete"
                   placeholder="{{tr}}mod-search-place-hodler{{/tr}}"
                   style="width:50em; font-size:medium;"
                   onchange="$V(this.form.start, '0'); $V(this.form.words_favoris, this.value)"
                   autofocus/>
            <button type="submit" id="button_search" class="button lookup">{{tr}}mod-search-rechercher{{/tr}}</button>
            <input type="hidden" name="words_favoris"/>
            <button class="favoris notext" type="button"
                    onclick="
                      if($V(this.form.words)){
                      Thesaurus.addeditThesaurusEntry(this.form, null, function(){});
                      }
                      else {
                      Modal.alert('{{tr}}mod-search-aide-ajouter-favoris{{/tr}}');
                      }
                      " title="{{tr}}CSearch-addToThesaurus{{/tr}}"></button>
            <button type="button" class="favoris" id="button_manage_thesaurus"
                    onclick="Search.manageThesaurus('{{$sejour_id}}', '{{$contexte}}')">{{tr}}mod-search-gerer-favoris{{/tr}}
            </button>
            <button type="button" class="download" onclick="Search.download();">{{tr}}Download{{/tr}}</button>
        </td>
    </tr>
    <tr>
        <td>
        <span class="circled">
            <input type="hidden" name="aggregate" value="0" id="aggregate_hidden">
            <input type="checkbox"
                   name="aggregate_view"
                   id="aggregate"
                   onchange="$('aggregate_hidden').value = this.checked ? 1 : 0">
            <label for="aggregate">{{tr}}mod-search-agregation{{/tr}}</label>
        </span>

            <button type="button" class="search" onclick="Search.showAdvancedSearchView()">
                {{tr}}AdvancedSearch-Open advanced search{{/tr}}
            </button>
        </td>
    </tr>
</table>
