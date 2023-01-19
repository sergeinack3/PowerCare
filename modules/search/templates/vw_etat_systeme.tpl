{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="search" script="Search"}}

<table class="main tbl" id="cartographie_systeme">
  <tr>
    <td id="table-stats">
      <table class="main tbl">
        <tr>
          <th class="category" colspan="2"> {{tr}}mod-search-state-general{{/tr}} </th>
        </tr>
        <tr>
          <td class="text">{{tr}}mod-search-state-connected{{/tr}}</td>
          <td class="text">
            {{if $infos_index.connexion === "1"}}
              <img title="{{tr}}mod-search-state-is-connected{{/tr}}" src="images/icons/note_green.png">
            {{else}}
              <img title="{{tr}}mod-search-state-is-connected{{/tr}}" src="images/icons/note_red.png">
            {{/if}}
          </td>
        </tr>
        <!-- Cluster -->
        <tr>
          <td class="text"><label>{{tr}}mod-search-state-cluster{{/tr}}</label></td>
          <td class="text">
            {{if $infos_index.status !== ""}}
              <img title="{{$infos_index.status}}" src="images/icons/note_{{$infos_index.status}}.png">
            {{/if}}
          </td>
        </tr>
        <!-- Tika && Tesseract -->
        <tr>
          <th class="section" colspan="2"> {{tr}}mod-search-state-tika{{/tr}}</th>
        </tr>
        <tr>
          <td class="text">{{tr}}mod-search-state-connected{{/tr}}</td>
          <td class="text">
            {{if $infos_tika}}
              <img title="{{tr}}mod-search-state-is-connected{{/tr}}" src="images/icons/note_green.png">
            {{else}}
              <img title="{{tr}}mod-search-state-is-not-connected{{/tr}}" src="images/icons/note_red.png">
            {{/if}}
          </td>
        </tr>
        <tr>
          <td class="text">{{tr}}mod-search-state-tesseract{{/tr}}</td>
          <td class="text">
            {{if $infos_ocr}}
              <img title="{{tr}}mod-search-state-is-connected{{/tr}}" src="images/icons/note_green.png">
            {{else}}
              <img title="{{tr}}mod-search-state-is-not-connected{{/tr}}" src="images/icons/note_red.png">
            {{/if}}
          </td>
        </tr>
        <tr>
          <th class="section" colspan="2">{{tr}}mod-search-state-table-tmp{{/tr}} </th>
        </tr>
        <tr>
          <td class="text" style="width:50%">{{tr}}mod-search-state-doc-wait-nbr{{/tr}}</td>
          <td class="text">{{$infos_index.tampon.nbr_doc_attente|integer}}</td>
        </tr>
        <tr>
          <td class="text">{{tr}}mod-search-state-doc-wait-older{{/tr}}</td>
          <td class="text">{{$infos_index.tampon.oldest_datetime|date_format:$conf.datetime}}</td>
        </tr>
        <tr>
          <td class="text">{{tr}}mod-search-state-doc-wait-error{{/tr}}</td>
          <td class="text">{{$infos_index.tampon.nbr_doc_erreur|integer}}</td>
        </tr>
        <tr>
          <th class="section" colspan="2">{{tr}}mod-search-state-stat{{/tr}}</th>
        </tr>
        <tr>
          <td class="text">{{tr}}mod-search-state-index-nbr{{/tr}}</td>
          <td>{{$infos_index.stats.cluster.nbIndex|integer}}</td>
        </tr>
        <tr>
          <td class="text">{{tr}}mod-search-state-index-doc-nbr{{/tr}}</td>
          <td>{{$infos_index.stats.cluster.nbDocsTotal|integer}}</td>
        </tr>
        <!-- Taille totale des index -->
        <tr>
          <td class="text">{{tr}}mod-search-state-index-size{{/tr}}</td>
          <td class="text">{{$infos_index.stats.cluster.size|decasi}}</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <!-- Etat de l'index -->
      {{foreach from=$infos_index.index item=index key=k}}
        <table class="tbl">
          <tr>
            <th class="category" colspan="2">{{tr}}mod-search-state-index{{/tr}} {{$k}}</th>
          </tr>
          <tr>
            <td class="text">{{tr}}mod-search-state-index-nbr-doc{{/tr}}</td>
            <td class="text" style="width:50%">{{$index.nbDocs_indexed|integer}}</td>
          </tr>
          <tr>
            <td class="text">{{tr}}mod-search-state-index-doc-wait{{/tr}}</td>
            <td class="text">{{$index.nbDocs_to_index|integer}}</td>
          </tr>
          <tr>
            <th class="section" colspan="2">{{tr}}mod-search-state-index-state{{/tr}}</th>
          </tr>
          <tr>
            <td class="text">{{tr}}mod-search-state-index-search-nbr{{/tr}}</td>
            <td class="text">{{$index.search_nbr|integer}}</td>
          </tr>
          <tr>
            <td class="text">{{tr}}mod-search-state-index-search-avg{{/tr}}</td>
            <td class="text">{{$index.search_avg}}</td>
          </tr>
        </table>
      {{/foreach}}
    </td>
  </tr>
</table>
