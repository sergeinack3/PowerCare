{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!--Vue de recherche générique (TDB, Search, PMSI)-->

{{mb_script module="search" script="Search" ajax=true}}
{{mb_script module="search" script="Thesaurus" ajax=true}}

<script>
    Main.add(function () {
        var form = getForm("esSearch");
        Calendar.regField(form._min_date);
        Calendar.regField(form._max_date);
        Search.getAutocomplete(form);

        Search.scrollDownToPagination();

        // Start from thesaurus
        var thesaurus = {{$thesaurus|@json}};
        if (thesaurus) {
            Search.startWithJson(form, thesaurus);
        }

        // Start from history
        var history = {{$history|@json}};
        if (history) {
            Search.startWithJson(form, history);
        }
    });
</script>
<form method="get" name="esSearch" action="?m=search" class="watched prepared"
      onsubmit="return Search.displayResults(this);"
      onchange="onchange=$V(this.form, '0')">
    <input type="hidden" name="start" value="0">
    <input type="hidden" name="stop" value="0">
    <input type="hidden" name="nbResult" value="0">
    <input type="hidden" name="contexte" value="{{$contexte}}">
    <input type="hidden" name="sejour_id" value="{{$sejour_id}}">
    <div>
        <!-- Barre de recherche -->
        {{mb_include module=search template=inc_header_search}}
    </div>
</form>

<div id="list_result_elastic" class="overflow">
    <!-- Résultats de la Recherche -->
</div>
