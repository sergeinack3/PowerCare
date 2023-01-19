{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  tableau_periode={{$tableau_periode|@json}};
  nombreelem = tableau_periode.length;
  changedate = function (sens) {
  var choix = {{$choix|@json}};
  var form = getForm("searchplanning");

  var date_courante = Date.fromDATE(form.elements.date_debut.value);
  var today = Date.fromDATE( (new Date()).toDATE() );

  if (choix=="semaine") {
    if(sens=='p') {
      date_courante.addDays(-7);
    }
    else if(sens=='n'){
      date_courante.addDays(7);
    }
    else if(sens=='t'){
      date_courante = today;
    }
  }
  else {
    if(sens == "p") {
      date_courante.setMonth(date_courante.getMonth() - 1);
    }
    else if(sens=='n'){
      date_courante.setMonth(date_courante.getMonth() + 1);
    }
    else if(sens=='t'){
      date_courante = today;
    }
  }
  form.elements.date_debut.value = date_courante.toDATE();
  loadPlanning(form);
}
</script>
<table class="main">
  <tr>
    <!-- Navigation par semaine ou mois-->
    <td colspan="2" style="text-align: center;">
      <button class="left" onclick="changedate('p')" style="min-width:100px;text-align:left;">
        {{if $choix=="semaine"}}{{tr}}Previous week{{/tr}}{{else}}{{tr}}Previous month{{/tr}}{{/if}}
      </button>
      <a class="center" onclick="changedate('t')" href="#1" style="text-align:center;">
        {{if $choix=="semaine"}}{{tr}}This week{{/tr}}{{else}}{{tr}}This month{{/tr}}{{/if}}
      </a>
      <button class="right rtl" onclick="changedate('n')" style="min-width:100px;text-align:right;">
        {{if $choix=="semaine"}}{{tr}}Next week{{/tr}}{{else}}{{tr}}Next month{{/tr}}{{/if}}
      </button>
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
     <!-- Zone d'insertion des plages d'astreinte-->
     {{assign var="indice" value="-1"}}
     {{assign var="count" value="-1"}}
     {{foreach from=$plagesastreinte item=_plage1}}
       {{if $indice != $_plage1->user_id}}
       {{assign var="userid" value=$_plage1->user_id}}
       {{assign var="indice" value=$userid}}
       {{assign var="count" value=$count+1}}
       <tr class="ligne">
         <th>
           <div class="nom">
             {{assign var=mediuser value=$_plage1->_ref_user}}
             {{mb_include module=mediusers template=inc_vw_mediuser object=$mediuser nodebug=true}}
             {{if $mediuser->_user_astreinte}}
              <div class="info phone">{{mb_value object=$mediuser field=_user_astreinte}}</div>
             {{else}}
              <div class="warning">{{tr}}CPlageAstreinte.noPhoneNumber{{/tr}}</div>
             {{/if}}
           </div>
         </th>
       <td>
         <div class="insertion">
         {{foreach from=$plagesastreinte item=_plage2}}
           {{if $_plage2->user_id == $indice}}
             <div id = "plage{{$_plage2->_id}}" class = "plage"
                  style="{{if $_plage2->_ref_user->user_type != "1"}}background:#ffaeae;{{else}}background:#aed0ff;{{/if}}">
               <div class="content">
                 <span onmouseover="ObjectTooltip.createEx(this, '{{$_plage2->_guid}}')">
                   {{if $_plage2->libelle}}<strong>{{$_plage2->libelle}}</strong>{{else}}<em>Pas de libelle</em>{{/if}}
                 </span>
                  <br/>
                 {{assign var=mediuser value=$_plage1->_ref_user}}
                 {{mb_include module=mediusers template=inc_vw_mediuser object=$mediuser nodebug=true}}
                 <script type="text/javascript">
                   Main.add(function(){
                     display_plage({{$_plage2->_id}},{{$_plage2->_deb}},{{$_plage2->_fin}});
                     new Draggable('plage{{$_plage2->_id}}', {constraint:"horizontal", snap: movesnap, onStart: savePosition, onEnd: DragDropPlage});
                     Event.observe(window, "resize", function(){
                       display_plage({{$_plage2->_id}},{{$_plage2->_deb}},{{$_plage2->_fin}});
                     });
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
        <td colspan="{{math equation="x+1" x=$tableau_periode|@count}}" class="empty" style="text-align: center;font-size: 2em;">
          {{tr}}CPlageAstreinte.none{{/tr}}
        </td>
      </tr>
      {{/foreach}}
      </table>
    </td>
  </tr>
</table>