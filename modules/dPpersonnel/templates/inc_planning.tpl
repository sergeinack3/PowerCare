{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  tableau_periode={{$tableau_periode|@json}};
  nombreelem = tableau_periode.length * 24;

  changedate = function (sens, type_view) {
    var choix = {{$choix|@json}};
    var form = getForm("searchplanning-"+type_view);

    var date_courante = Date.fromDATE($V(form.date_debut));
    var today = Date.fromDATE( (new Date()).toDATE() );

    if (choix == "semaine") {
      switch (sens) {
        case "p":
          date_courante.addDays(-7);
          break;
        default:
        case 'n':
          date_courante.addDays(7);
          break;
        case "t":
          date_courante = today;
      }
    }
    else {
      switch (sens) {
        case "p":
          date_courante.setMonth(date_courante.getMonth() - 1);
          break;
        default:
        case "n":
          date_courante.setMonth(date_courante.getMonth() + 1);
          break;
        case "t":
          date_courante = today;
      }
    }
    $V(form.date_debut, date_courante.toDATE());
    loadPlanning(form);
  }
</script>

<table class="main me-w100">
  <tr>
    <!-- Navigation par semaine ou mois-->
    <td colspan="2" style="text-align: center;">
      <div class="me-margin-4">
        <button class="left" onclick="changedate('p', '{{$type_view}}')" style="min-width:120px;text-align:left;">
          {{if $choix=="semaine"}}{{tr}}Previous week{{/tr}}{{else}}{{tr}}Previous month{{/tr}}{{/if}}
        </button>
        <button class="center" onclick="changedate('t', '{{$type_view}}')" style="text-align:center;">
          {{if $choix=="semaine"}}{{tr}}This week{{/tr}}{{else}}{{tr}}This month{{/tr}}{{/if}}
        </button>
        <button class="right rtl" onclick="changedate('n', '{{$type_view}}')" style="min-width:120px;text-align:right;">
          {{if $choix=="semaine"}}{{tr}}Next week{{/tr}}{{else}}{{tr}}Next month{{/tr}}{{/if}}
        </button>
      </div>
    </td>
  </tr>
  <tr>
    <!-- Affichage : semaine du tant au tant -->
    <th colspan="{{$tableau_periode|@count}}" style="text-align:center; font-size:14pt">
      {{if $choix=="semaine"}}
        {{$choix}} du {{$tableau_periode.0|date_format:"%d %B %Y"}} au
        {{$tableau_periode.6|date_format:"%d %B %Y"}}
      {{else}}
         {{$tableau_periode.0|date_format:"%B %Y"}}
      {{/if}}
    </th>
  </tr>
  <tr>
    <td>
    <!-- Affichage du planning -->
   <table id="schedule">
     <tr style="height: 2em;">
       <td style="width: 12em;"></td>
       {{foreach from=$tableau_periode item=_periode}}
         {{assign var=day value=$_periode|date_format:"%A"|upper|substr:0:1}}
         <th
           {{if in_array($_periode, $bank_holidays)}}
           style="background: #fc0;"
           {{elseif $day == "S" || $day == "D"}}
             style="background: #ddf;"
           {{/if}}>
           <big>{{$day}}</big>
           <br/>{{$_periode|date_format:"%d"}}
         </th>
       {{/foreach}}
     </tr>
     <!-- Zone d'insertion des plages de conge-->
     {{assign var="indice" value="-1"}}
     {{assign var="count" value="-1"}}
     {{foreach from=$plages item=_plage1}}
       {{if $indice != $_plage1->$orderby}}
         {{assign var="indice" value=$_plage1->$orderby}}
         {{assign var="count" value=$count+1}}
         <tr class="ligne">
           <th>
             <div class="nom">
               {{assign var=mediuser value=$_plage1->_ref_user}}
               {{mb_include module=mediusers template=inc_vw_mediuser object=$mediuser nodebug=true}}
             </div>
           </th>
           <td>
             <div class="insertion">
             {{foreach from=$plages item=_plage2}}
               {{if $_plage2->$orderby == $indice}}
                 <div id="plage{{$_plage2->_id}}" class="plage_conge">
                   <div class="content">
                     {{if $_plage2->_duree >= 24}}
                       {{math equation=round(x/24) x=$_plage2->_duree}} {{tr}}days{{/tr}}
                     {{else}}
                       {{$_plage2->_duree}} {{tr}}hours{{/tr}}
                     {{/if}}
                     <br/>
                     <span onmouseover="ObjectTooltip.createEx(this, '{{$_plage2->_guid}}')">
                       {{$_plage2->libelle}}
                     </span>
                     <script>
                       Main.add(function() {
                         display_plage({{$_plage2->_id}}, {{$_plage2->_deb}}, {{$_plage2->_fin}}, '{{$type_view}}');
                       });
                     </script>
                   </div>
                 </div>
               {{/if}}
             {{/foreach}}
             </div>
           </td>
        {{foreach from=$tableau_periode item=_periode name=td_list}}
          {{if !$smarty.foreach.td_list.first}}
            {{assign var=day value=$_periode|date_format:"%A"|upper|substr:0:1}}
            <td {{if $day == "S" || $day == "D"}}style="background: #ddf;"{{/if}}></td>
          {{/if}}
        {{/foreach}}
        </tr>
      {{/if}}
      {{foreachelse}}
      <tr>
        <td colspan="{{math equation="x+1" x=$tableau_periode|@count}}" class="empty">
          {{tr}}CPlageConge.none{{/tr}}
        </td>
      </tr>
      {{/foreach}}
      </table>
    </td>
  </tr>
</table>