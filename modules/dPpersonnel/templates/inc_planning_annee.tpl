{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  changemode = function(type, date, user_id, type_view) {
    var form = getForm("searchplanning-"+type_view);
    $V(form.choix, type);
    var champs = date.split('-');
    $V(form.date_debut_da,champs[2] + "/" + champs[1] + "/" + champs[0]);
    $V(form.date_debut, date);
    $V(form.user_id, user_id);
    loadPlanning(form);
  };

  changeannee = function(sens, type_view) {
    var form = getForm("searchplanning-"+type_view);

    var date_courante = Date.fromDATE(form.elements.date_debut.value);
    var today = Date.fromDATE(new Date().toDATE());
    switch (sens) {
      case "p":
        date_courante.addYears(-1);
        break;
      default:
      case "n":
        date_courante.addYears(1);
        break;
      case "t":
        date_courante= today;
    }
    $V(form.date_debut, date_courante.toDATE());
    loadPlanning(form);
  }
</script>

<table class="main">
  <tr>
    <td colspan="2" class="button">
      <div class="me-margin-4">
        <button class="left" onclick="changeannee('p', '{{$type_view}}')" style="min-width:120px;text-align:left;">
          {{tr}}Previous year{{/tr}}
        </button>
        <button onclick="changeannee('t', '{{$type_view}}')">
          {{tr}}This year{{/tr}}
        </button>
        <button class="right rtl" onclick="changeannee('n', '{{$type_view}}')" style="min-width:120px;text-align:right;">
          {{tr}}Next year{{/tr}}
        </button>
      </div>
    </td>
  </tr>
</table>

<table class="main me-w100">
  {{assign var="k" value=1}}
  {{foreach from=1|range:12 item=j}}

  {{assign var="start" value=$tab_start.$k}}
  {{assign var="k" value=$k+1}}
  {{assign var="duree" value=$tab_start.$k}}
  {{assign var="k" value=$k+1}}
  {{if $j%6==1}}
  <tr>
  {{/if}}
    <td>
      <table class="tbl cal">
        <tr>
          {{assign var=day value="01"}}
          {{assign var=month value=$j|pad:2:"0"}}
          {{assign var=year value=$debut_periode|date_format:"%Y"}}
          {{assign var=date value="$year-$month-$day"}}
          <th colspan="7" class="title">
            <a href="#" onclick="changemode('mois','{{$date}}',{{$filter->user_id}}, '{{$type_view}}')">
              {{$date|date_format:"%B %Y"}}
            </a>
          </th>
        </tr>
        <tr>
          {{foreach from=1|range:7 item=_j}}
          {{assign var=date_model value="2010-02-$_j"}}
          <th>{{$date_model|date_format:"%A"|upper|substr:0:1}}</th>
          {{/foreach}}
         </tr>
         {{if $duree+$start > 36}}
           {{assign var=longueur value=42}}
         {{elseif $duree+$start < 30}}
           {{assign var=longueur value=28}}
         {{else}}
           {{assign var=longueur value=35}}
         {{/if}}
         {{foreach from=1|range:$longueur item=i}}
           {{if $i%7 == 1}}
         <tr>
         {{/if}}
         {{if $i>=$start && $i<=$duree+$start-1}}
           {{assign var=tday value=$i-$start+1}}
           {{assign var=day value=$tday|pad:2:"0"}}
           {{assign var=month value=$j|pad:2:"0"}}
           {{assign var=date value="$year-$month-$day"}}
           {{assign var=open value=0}}
           {{foreach from=$plages item=_plage}}
             {{assign var=date_debut value=$_plage->$field_debut|iso_date}}
             {{assign var=date_fin value=$_plage->$field_fin|iso_date}}
             {{if $date>=$date_debut && $date<=$date_fin}}
               {{assign var=open value=1}}
               <td class="occuped {{if $date == $date_debut}}start{{/if}} {{if $date == $date_fin}}end{{/if}}" title="{{$_plage}}">
             {{/if}}
           {{/foreach}}
           {{if !$open}}
           <td
             {{assign var=weekend value=$date|date_format:"%A"|upper|substr:0:1}}
             {{if in_array($date, $bank_holidays)}}
               style="background: #fc0;"
             {{elseif $weekend == "S" || $weekend == "D"}}
               style="background: #ddf;"
             {{/if}}>
           {{/if}}
           {{assign var=jour value=$i-$start+1}}
             <a href="#Week-{{$date}}" onclick="changemode('semaine','{{$date}}',{{$filter->user_id}}, '{{$type_view}}')">
               {{$jour}}
             </a>
           </td>
        {{else}}
          <td class="empty"></td>
        {{/if}}
        {{if $i%7==0}}
        </tr>
        {{/if}}
        {{/foreach}}
      </table>
    </td>
    {{if $j%6==0}}
    </tr>
    {{/if}}
  {{/foreach}}
</table>