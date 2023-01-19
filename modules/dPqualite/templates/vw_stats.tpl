{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  var graphs = {{$graphs|@json}};
  var options = {{$options|@json}};
  var categories = {{$list_categories_json|@json}};
  var titles = {};

  function refreshCount() {
    $("selected-categories-count").update(oEvenementField.getValues().length);
  }

  function selectAll() {
    $('categories').select('input').each(function (e) {
      $V(e, true, true)
    });
    getTokensFromInputs();
    refreshEvtCounts();
  }

  function getTokensFromInputs() {
    oEvenementField.setValues($("tokens-list").select("input[type=checkbox]").findAll(function (e) {
      return e.checked
    }).pluck("name"));
  }

  function setTokensToInputs() {
    var values = oEvenementField.getValues();
    $("tokens-list").select("input[type=checkbox]").each(function (e) {
      if (values.indexOf(name) != -1 && e.name == name || !values.length) {
        e.checked = true;
      }
    });
    checkCategories();
  }

  function refreshEvtCounts() {
    categories.each(function (c) {
      var counter = $('selected-evts-' + c + '-count');
      if (counter) {
        counter.update($("category-" + c).select("input[type=checkbox]").findAll(function (e) {
          return e.checked
        }).length);
      }
    });
  }

  function toggleTokens(checkbox, element) {
    $(element).select("input[type=checkbox]").each(function (e) {
      e.checked = checkbox.checked;
    });
    getTokensFromInputs();
    refreshEvtCounts();
  }

  function toggleEvt(catId, val, forceTo) {
    oEvenementField.toggle(val, forceTo);
    refreshEvtCounts();
  }

  function checkCategories() {
    categories.each(function (c) {
      var checked = $("category-" + c).select("input[type=checkbox]").findAll(function (e) {
        return e.checked
      }).length;
      if (checked == $("category-" + c).select("input[type=checkbox]").length) {
        $("category-" + c + "-checkbox").checked = true;
      }
    });
  }

  function refreshGraph(size) {
    size = size.split('x');
    var width = size[0], height = size[1];

    $H(graphs).each(function (pair) {
      $("stats-" + pair.key).setStyle({
        width:  width + 'px',
        height: height + 'px'
      });
      drawGraph(pair.key);
    });
  }

  var graph = [];

  function drawGraph(id) {
    titles[id] = titles[id] || $('stats-' + id).innerHTML;
    $('stats-' + id).innerHTML = '';
    graph[id] = Flotr.draw(
      $('stats-' + id),
      graphs[id], Object.extend({
        title:       titles[id],
        bars:        {show: true, barWidth: 0.5, stacked: true, fillOpacity: 0.6},
        mouse:       {track: false},
        yaxis:       {
          min: 0, tickFormatter: function (v) {
            return Math.round(v).toString()
          }
        },
        grid:        {verticalLines: false, backgroundColor: '#fff'},
        legend:      {position: 'nw'},
        HtmlText:    false,
        spreadsheet: {
          show:             true,
          tabGraphLabel:    'Graphique',
          tabDataLabel:     'Données',
          toolbarDownload:  'Fichier CSV',
          toolbarSelectAll: 'Sélectionner tout le tableau',
          csvFileSeparator: ';',
          decimalSeparator: ','
        }
      }, options)
    );
  }

  function showLegendeCriticite() {
    new Url('qualite', 'vw_legende_criticite').requestModal();
  }

  Main.add(function () {
    filterForm = getForm('stats-filter');

    $H(graphs).each(function (pair) {
      drawGraph(pair.key);
    });

    Control.Tabs.create('filters-tabs', true);
    Control.Tabs.create('evenements-tabs', true);

    oEvenementField = new TokenField(filterForm.evenements, {onChange: refreshCount});
    setTokensToInputs();
    checkCategories();
    refreshCount();
    refreshEvtCounts();
  });

  var oEvenementField = null;
  var filterForm = null;
</script>

<form name="stats-filter" method="get">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="tab" value="{{$tab}}" />
  <input type="hidden" name="evenements" value="{{$evenements}}" />

  <div class="me-align-auto">
    <ul class="control_tabs" id="filters-tabs">
      <li><a href="#time">Filtre chronologique</a></li>
      <li><a href="#data">Filtres données</a></li>
      <li>
        <a href="#categories" style="padding-top: 2px; padding-bottom: 1px; padding-right: 1px;">
          Catégories (<span id="selected-categories-count">0</span> sélectionnées)
          <button type="button" class="cancel" onclick="selectAll()">Tout cocher</button>
        </a>
      </li>
    </ul>
  </div>

  <table class="main form">
    <tbody id="time" style="display: none;">
    <tr>
      <td>
        <label for="months_count">Durée</label>
        <select name="months_count">
          <option value="3" {{if $months_count == 3}}selected="selected"{{/if}}>3 mois</option>
          <option value="6" {{if $months_count == 6}}selected="selected"{{/if}}>6 mois</option>
          <option value="12" {{if $months_count == 12}}selected="selected"{{/if}}>12 mois</option>
          <option value="24" {{if $months_count == 24}}selected="selected"{{/if}}>24 mois</option>
        </select>

        <label for="months_relative">Fin de la période il y a</label>
        <select name="months_relative">
          {{foreach from=$list_months item=c}}
            <option value="{{$c}}" {{if $months_relative == $c}}selected="selected"{{/if}}>{{$c}} mois</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    </tbody>
    
    <tbody id="data" style="display: none;">
    <tr>
      <td colspan="20">
        <div class="small-info">
          Les cases à cocher vous permettent de choisir quels graphiques vous souhaitez afficher.
        </div>
      </td>
    </tr>
    {{foreach from=$enums item=enum key=name name=enum}}
      {{if $smarty.foreach.enum.index % 2 == 0 || $smarty.foreach.enum.first}}
        <tr>
      {{/if}}
      <th>
        <label for="filters[{{$name}}]">{{tr}}CFicheEi-{{$name}}{{/tr}}</label>
        <input type="checkbox" value="{{$name}}" name="comparison[{{$name}}]"
               {{if in_array($name,$comparison)}}checked="checked"{{/if}} />
      </th>
      <td>
        {{if $name != 'evenements' && $name != '_criticite'}}
          <select name="filters[{{$name}}]">
            <option value=""> &mdash; Tous</option>
            {{foreach from=$enum item=val key=key}}
              <option value="{{$key}}" {{if $filters.$name == $key}}selected="selected"{{/if}}>{{$val}}</option>
            {{/foreach}}
          </select>
        {{elseif $name == '_criticite'}}
          <button type="button" class="search" onclick="showLegendeCriticite()">Légende</button>
        {{/if}}
      </td>
      {{if $smarty.foreach.enum.index+1 % 2 == 0 || $smarty.foreach.enum.last}}
        </tr>
      {{/if}}
    {{/foreach}}
    </tbody>
  </table>
  
  <table id="categories" class="me-align-auto" style="display: none;">
    <tr>
      <td>
        <ul class="control_tabs_vertical" id="evenements-tabs">
          {{foreach from=$list_categories item=curr_evenement name=categories}}
            <li>
              <input id="category-{{$curr_evenement->ei_categorie_id}}-checkbox" type="checkbox"
                     onclick="toggleTokens(this, 'category-{{$curr_evenement->ei_categorie_id}}'); "
                     style="float: right; margin: 3px; clear: right;" />
              <a href="#category-{{$curr_evenement->ei_categorie_id}}" style="font-size: 1em; padding: 1px 3px; font-weight: normal;">
                {{$curr_evenement->nom}}
                (<span id="selected-evts-{{$curr_evenement->ei_categorie_id}}-count">0</span>)
              </a>
            </li>
          {{/foreach}}
        </ul>
      </td>
      <td style="vertical-align: top;" id="tokens-list">
        {{foreach from=$list_categories item=curr_evenement name=categories}}
          <table class="tbl" id="category-{{$curr_evenement->ei_categorie_id}}" style="display: none;">
            {{foreach from=$curr_evenement->_ref_items item=curr_item name=evt_item}}
              <tr>
                <td class="text">
                  <input type="checkbox" name="{{$curr_item->ei_item_id}}"
                         onclick="toggleEvt('{{$curr_evenement->ei_categorie_id}}', this.name, this.checked); "
                         {{if $curr_item->_checked}}checked="checked"{{/if}}/>
                  <label for="{{$curr_item->ei_item_id}}" id="titleItem{{$curr_item->ei_item_id}}"
                         title="{{$curr_item->nom}}">{{$curr_item->nom}}</label>
                </td>
              </tr>
              {{foreachelse}}
              <tr>
                <td>{{tr}}_CFicheEi-noitemscat{{/tr}}</td>
              </tr>
            {{/foreach}}
          </table>
          {{foreachelse}}
          <div class="empty">{{tr}}CEiItem.none{{/tr}}</div>
        {{/foreach}}
      </td>
    </tr>
  </table>
  
  <div style="text-align: center;">
    <button type="submit" class="search">Filtrer</button>
    
    <select name="size" onchange="refreshGraph($V(this))">
      <option value="" disabled="disabled" selected="selected">Taille des graphiques</option>
      <option value="400x200">Petit</option>
      <option value="600x300">Normal</option>
      <option value="700x500">Grand</option>
      <option value="800x600">Très grand</option>
    </select>
  </div>

  {{foreach from=$graphs item=graph key=id}}
    <div style="text-align: center; clear: both;" id="graphs">
      <div id="stats-{{$id}}" style="width: 600px; height: 300px; margin: auto;">{{tr}}CFicheEi-{{$id}}{{/tr}}</div>
      <button onclick="graph['{{$id}}'].saveImage()" type="button" class="submit">Enregistrer l'image</button>
    </div>
  {{/foreach}}
</form>

