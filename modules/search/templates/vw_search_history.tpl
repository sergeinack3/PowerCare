{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=search script=SearchHistory ajax=true}}


<script>
   Main.add(function () {
     var form = getForm("esFilterHistory");
     SearchHistory.updateListHistory(form);
   });
</script>

<table class="main me-align-auto">
  <tr>
    <td id="list_favoris">
      <fieldset>
        <legend>{{tr}}mod-search-filtre{{/tr}}</legend>
        <form method="get" name="esFilterHistory" action="?m=search" class="watched prepared"
              onsubmit="return SearchHistory.updateListHistory(this);">
          <table class="main">
            <tr>
              <td>
                <span>{{tr}}mod-search-contexte{{/tr}} : </span>
                {{foreach from=$contextes item=_contexte}}
                  <span class="circled">
                      <label for="{{$_contexte}}"> {{$_contexte}}</label>
                      <input type="checkbox" name="contextes[]" id="{{$_contexte}}" value="{{$_contexte}}"
                             onclick="this.form.onsubmit();">
                   </span>
                {{/foreach}}
              </td>
            </tr>
          </table>
        </form>
      </fieldset>
      <div id="list_thesaurus_entry"></div>
    </td>
  </tr>
  <tr>
    <td>
      <button type="button" class="trash" onclick="SearchHistory.deletehistory()" style="margin:5px;">
        {{tr}}mod-search-history-delete{{/tr}}
      </button>
    </td>
  </tr>
</table>

<div id="search_history_results" class="me-no-align"></div>