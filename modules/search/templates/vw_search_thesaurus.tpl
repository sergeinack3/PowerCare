{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!--Vue principale de la gestion des favoris.-->

{{mb_script module=search script=Search}}
{{mb_script module=search script=Thesaurus ajax=true}}
<script>
  Main.add(function () {
    var form = getForm("esFilterFavoris");
    Thesaurus.updateListThesaurus(form);
  });
</script>

<table class="main me-align-auto">
  <tr>
    <td id="list_favoris">
      <fieldset>
        <legend>{{tr}}mod-search-filtre{{/tr}}</legend>
        <form method="get" name="esFilterFavoris" action="?m=search" class="watched prepared"
              onsubmit="return Thesaurus.filterListThesaurus(this);">
          <table>
            <tr>
              <td>
                <span>{{tr}}mod-search-contexte{{/tr}} : </span>
                {{foreach from=$contextes item=_contexte}}
                  <span class="circled">
                      <label for="{{$_contexte}}"> {{$_contexte}}</label>
                      <input type="checkbox" name="contextes[]" id="{{$_contexte}}" value="{{$_contexte}}"
                             onclick="this.form.onsubmit();" onchange="$V(this.form.start_thesaurus, '0')">
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
</table>


