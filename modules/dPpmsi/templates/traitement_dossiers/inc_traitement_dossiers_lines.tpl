{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="pmsi" script="traitementDossiers" ajax=true}}

<script>
  sortBy = function(order_col, order_way) {
    var form = getForm("selType");
    $V(form.order_col, order_col);
    $V(form.order_way, order_way);
    traitementDossiers.reloadListDossiers(form);
  }

  Main.add(function() {
    Calendar.regField(getForm("changeDateCompletion").date, null, {noView: true});
  });
</script>

{{if $period}}
  <div class="small-info">
    Vue partielle limitée au <strong>{{$period}}</strong>. Veuillez changer le filtre pour afficher toute la journée.
  </div>
{{/if}}

<table class="tbl" id="lines_dossiers">
  <tr>
    <th class="title" colspan="10">
      <a href="#1" style="display: inline" onclick="$V(getForm('selType').date, '{{$hier}}'); traitementDossiers.reloadAllTraitementDossiers(getForm('selType'))">&lt;&lt;&lt;</a>
      {{$date|date_format:$conf.longdate}}
      <form name="changeDateCompletion" action="?" method="get">
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="$V(getForm('selType').date, this.value); traitementDossiers.reloadAllTraitementDossiers(getForm('selType'))" />
      </form>
      <a href="#1" style="display: inline" onclick="$V(getForm('selType').date, '{{$demain}}'); traitementDossiers.reloadAllTraitementDossiers(getForm('selType'))">&gt;&gt;&gt;</a>
      <br />

      <em style="float: left; font-weight: normal;">
        {{$sejours|@count}} sortie(s) ce jour
      </em>

      <select style="float: right" name="filterFunction" style="width: 16em;" onchange="$V(getForm('selType').filterFunction, this.value); traitementDossiers.reloadListDossiers(getForm('selType'));">
        <option value=""> &mdash; Toutes les fonctions</option>
        {{mb_include module=mediusers template=inc_options_function list=$functions selected=$filterFunction}}
      </select>
    </th>
  </tr>

  <tr>
    <th class="narrow">
      {{mb_colonne class="CSejour" field="sortie_reelle" order_col=$order_col order_way=$order_way function=sortBy}}
    </th>
    <th class="text">
      {{mb_colonne class="CSejour" field="patient_id" order_col=$order_col order_way=$order_way function=sortBy}}
      <input type="text" size="3" onkeyup="traitementDossiers.filter(this, 'lines_dossiers')" id="filter-patient-name" />
    </th>
    <th>
      {{mb_colonne class="CSejour" field="praticien_id" order_col=$order_col order_way=$order_way function=sortBy}}
    </th>
    <th>{{tr}}CSejour{{/tr}}</th>
    <th>{{tr}}CTraitementDossier-traitement{{/tr}} / {{tr}}CTraitementDossier-validate{{/tr}}</th>
      {{if "atih CGroupage use_fg"|gconf}}
        <th>
            {{tr}}GHS{{/tr}} <br/>
          ({{$sejour_groupes}}/{{$sejours|@count}}) <br/>
          Somme : {{$total}}&euro;
        </th>
      {{/if}}
  </tr>

  {{foreach from=$sejours key=_key item=_sejour}}
      {{mb_include module=pmsi template="traitement_dossiers/inc_traitement_dossier_line"}}
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}None{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>