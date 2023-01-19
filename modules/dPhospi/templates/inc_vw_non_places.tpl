{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  {{if !$readonly}}
  var container = $('lit_bloque');
  new Draggable(container, {
    ghosting:    "true",
    starteffect: function (element) {
      new Effect.Opacity(element, {duration: 0.2, from: 1.0, to: 0.7});
    },
    revert:      true
  });
  var container2 = $('lit_urgence');
  new Draggable(container2, {
    ghosting:    "true",
    starteffect: function (element) {
      new Effect.Opacity(element, {duration: 0.2, from: 1.0, to: 0.7});
    },
    revert:      true
  });
  {{/if}}
  
  Main.add(function () {
    var time_line_temporelle_non_affectes = $("time_line_temporelle_non_affectes");
    var list_affectations = $("list_non_places");
    
    if (Prototype.Browser.Gecko) {
      time_line_temporelle_non_affectes.setStyle({top: window.top_tempo_na});
    }
    
    if (!Prototype.Browser.IE) {
      list_affectations.on('scroll', function () {
        time_line_temporelle_non_affectes.setClassName('scroll_shadow', list_affectations.scrollTop);
      });
      list_affectations.fire('scroll');
    }
    $("list_non_places").scrollTop = Placement.scrollNonPlaces;

    {{if "dPImeds"|module_active}}
    ImedsResultsWatcher.loadResults();
    {{/if}}
  });
</script>

{{math equation=x+1 x=$nb_ticks assign=colspan}}
{{math equation=x-1 x=$nb_ticks assign=nb_ticks_r}}
{{assign var=systeme_presta value="dPhospi prestations systeme_prestations"|gconf}}

<div style="height: 2em; width: 100%;" class="me-height-auto">
  <div id="time_line_temporelle_non_affectes" style="background: #fff; z-index: 200;" class="me-no-align me-margin-bottom-2 me-no-bg me-small-fields">
    <div style="display: inline-block;">
      <input type="text" style="width: 7em;" onkeyup="filter(this, 'non_places_temporel')" class="search" />
    </div>
    <form name="chgFilter" method="get" onsubmit="return onSubmitFormAjax(this,null, 'list_affectations');">
      <input type="hidden" name="m" value="hospi" />
      <input type="hidden" name="a" value="ajax_vw_non_places" />
      {{mb_field object=$_sejour field="_type_admission" style="width: 16em;" onchange="this.form.onsubmit()"}}

      <select name="triAdm" style="width: 12em;" onchange="this.form.onsubmit()">
        <option value=""> &mdash; {{tr}}Choose{{/tr}}</option>
        <option value="praticien" {{if $triAdm == "praticien"}} selected{{/if}}>Tri par praticien</option>
        <option value="date_entree" {{if $triAdm == "date_entree"}}selected{{/if}}>Tri par heure d'entrée</option>
        <option value="patient" {{if $triAdm == "patient"}} selected{{/if}}>Tri par patient</option>
      </select>
      <select name="filter_function" style="width: 12em;" onchange="this.form.onsubmit()">
        <option value=""> &mdash; Toutes les fonctions</option>
        {{foreach from=$functions_filter item=_function}}
          <option value="{{$_function->_id}}" {{if $_function->_id == $filter_function}}selected{{/if}} class="mediuser"
                  style="border-color: #{{$_function->color}};">{{$_function}}</option>
        {{/foreach}}
      </select>
      {{if $items_prestation|@count}}
        <select name="item_prestation_id" onchange="this.form.onsubmit();" style="width: 13em;">
          <option value="">&mdash; Tous les niveaux de prestation</option>
          {{foreach from=$items_prestation item=_item}}
            <option value="{{$_item->_id}}" {{if $_item->_id == $item_prestation_id}}selected{{/if}}>{{$_item->rank}}
              - {{$_item}}</option>
          {{/foreach}}
        </select>
      {{/if}}
      <label>
        <input type="checkbox" name="duree_uscpo_view" {{if $duree_uscpo}}checked{{/if}}
               onchange="$V(this.form.duree_uscpo, this.checked ? 1 : 0);" />
        <input type="hidden" name="duree_uscpo" value="{{$duree_uscpo}}" onchange="this.form.onsubmit();" />
        Durée uscpo
      </label>
      <label>
        <input type="checkbox" name="isolement_view" {{if $isolement}}checked{{/if}}
               onchange="$V(this.form.isolement, this.checked ? 1 : 0);" />
        <input type="hidden" name="isolement" value="{{$isolement}}" onchange="this.form.onsubmit();" />
        Isolement
      </label>
    </form>
    <div id="lit_bloque" class="clit_bloque draggable" style="display: inline-block;">
      <strong>[BLOQUER UN LIT]</strong>
    </div>
    <div id="lit_urgence" class="clit_bloque draggable"
         style="display: {{if "dPurgences"|module_active}}inline-block{{else}}none{{/if}};">
      <strong>[LIT EN URGENCE]</strong>
    </div>
  </div>
</div>

<div class="small-info" id="alerte_non_places_temporel" style="display: none;">
  {{tr}}CSejour-partial_view{{/tr}}
</div>

<div id="list_non_places" style="height: 90%; overflow-x: auto; overflow-y: scroll;" onscroll="syncBars(this)">
  {{if $sejours_non_affectes|@count}}
    <table class="tbl layout_temporel me-no-align" style="table-layout: fixed; position: relative;" id="non_places_temporel">
      <col style="width: 15%;" />
      {{foreach from=$sejours_non_affectes item=_sejours_by_service key=_service_id}}
        {{if $_service_id != "np"}}
          {{assign var=service value=$services.$_service_id}}
        {{/if}}
        <tr>
          <th class="section {{if $_service_id != "np" && $service->externe}}service_externe{{/if}}" colspan="{{$colspan}}">
            {{if $_service_id == "np"}}
              Non placés
            {{else}}
              {{$service}} - Couloir
            {{/if}}
          </th>
        </tr>
        {{foreach from=$_sejours_by_service item=_object}}
          <tr class="droppable line">
            {{mb_include module=hospi template=inc_line_lit in_corridor=1 _lit=$_object}}
          </tr>
        {{/foreach}}
      {{/foreach}}
    </table>
  {{else}}
    <div class="empty">{{tr}}CSejour.none{{/tr}}</div>
  {{/if}}
</div>
