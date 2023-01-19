{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="astreintes" script="plage"}}

<script>

  tableau_periode = {{$tableau_periode|@json}};
  nombreelem = tableau_periode.length;
  largeur_nom = 135;

  loadPlanning = function(form) {
    var url = new Url("astreintes", "ajax_planning");
    url.addFormData(form);
    url.requestUpdate("planning");
    return false;
  };

  display_plage = function (plage_id, debut, fin) {
    var width = parseInt($("schedule").getWidth()) - largeur_nom;
    var plage = $("plage" + plage_id);
    var width_calc = (fin * (width/nombreelem + 0.4).floor());
    var margin_calc = 0;

    if((debut*width/nombreelem).ceil() < 0) {
      margin_calc = -(debut*width/nombreelem).ceil();
    }
    plage.setStyle({
      left: (debut*width/nombreelem).ceil()+'px',
      width: width_calc - 2 +'px'
    });

    plage.down(".content").setStyle({
      marginLeft: Math.max(2, margin_calc)+'px'
    });
  };

  movesnap = function(x, y, drag) {
    var table = $("schedule");
    var columns = table.down("tr").next().select("td");
    var left, found = false;
    var widthsave = columns[0].getWidth();
    var leftOffsets = [];
    var tableLeft = table.cumulativeOffset().left + largeur_nom;

    columns.each(function(col){
      leftOffsets.push(col.cumulativeOffset().left) + largeur_nom;
    });

    if(x > 0) {
      leftOffsets.each(function(offset){
        if (found) return;

        left = offset - tableLeft;
        if (left >= x) {
          found = true;
          return;
        }
      });
      if (left < x) {
        left = left + widthsave - 5;
      }
    }
    else {
      leftOffsets.each(function(offset){
      if (found) return;

        left = offset - parseInt(table.getWidth()-largeur_nom) + widthsave - tableLeft;
        if (left >= x) {
          found = true;
          return;
        }
      });
    }

    drag.element.down().setStyle({
      marginLeft: Math.abs(Math.min(left, 2))+"px"
    });

    return [left, 0];
  };

  DragDropPlage = function(draggable){
    var element = draggable.element;
    var decalage = parseInt(element.style.left);
    var widthtotal = parseInt($("schedule").getWidth()) - largeur_nom;
    var taille = (widthtotal / nombreelem).round();
    var new_left = (decalage / taille).round();
    var widthplage = (parseInt(element.style.width) / taille).round() - 1;
    var datedeb = tableau_periode[0];
    var date_debut = Date.fromDATE(datedeb);

    date_debut.addDays(new_left);

    var date_fin = date_debut;
    date_debut = date_debut.toDATE();

    date_fin.addDays(widthplage);
    date_fin = date_fin.toDATE();
    var plage_id = element.id.substring(5);

    var url = new Url("astreintes", "do_plageastreinte_aed");
    url.addParam("plage_id", plage_id);
    url.addParam("date_debut", date_debut);
    url.addParam("dosql","do_plageastreinte_aed");
    url.addParam("date_fin", date_fin);
    url.requestUpdate("systemMsg", {
      method: "post",
      // Si l'enregistrement de la plage échoue, il faut replacer la plage à sa place antérieure
      onComplete: function(){
        if ($("systemMsg").select(".error").length > 0) {
          oldDrag.drag.element.style.left = parseInt(oldDrag.left)+"px";
        } else {
          loadPlanning(getForm("searchplanning"));
          PlageAstreinte.loadUser("{{$filter->user_id}}", '');
        }
      }
    });
  };


  savePosition = function(drag){
    window.oldDrag = {
    left: drag.element.style.left,
    drag: drag
    };
  };

  toggleYear = function (form) {
    if($V(form.user_id) == '') {
      form.choix[2].disabled = "disabled";
      $V(form.choix, "mois");
    }
    else {
      form.choix[2].disabled = "";
    }
  };

  Main.add(function(){
    var form = getForm("searchplanning");
    var choixannee = $('annee');

    loadPlanning(form);

    if($V(form.user_id) == "") {
      choixannee.checked='';
      choixannee.disabled='disabled';
    }
    else {
      choixannee.disabled='';
    }
  });
</script>

<style type="text/css">

#schedule {
  table-layout:    fixed;
  width:           100%;
  border-spacing:  0;
  border-collapse: collapse;
  overflow:        hidden;
  border: 1px solid #ddd;
  position: relative;
}

#schedule td,
#schedule th {
  border: 1px solid #ddd;
}

.ligne {
  height: 50px;
}

.plage {
  height:            40px;
  background-color:  #ccc;
  position:          absolute;
 -moz-border-radius: 3px;
  -webkit-border-radius: 3px;
  border-radius: 3px;
  border:  2px solid #aaa;
  z-index: 0;
  overflow: hidden;
  padding: 2px 0;
  margin-top: 1px;
}

.plage .content {
  margin: 2px;
}

.insertion {
  position: relative;

}

.nom {
  margin-top: -1px;
  z-index: 1;
  position :relative;
  background-color: #fff;
  height: 50px;
  line-height: 2em;
  text-align: left;
}
</style>
<button class="new" type="button" onclick="PlageAstreinte.modal('','')">
          {{tr}}CPlageAstreinte-title-create{{/tr}}
        </button>
<table class="main">
  <tr>
    <th>{{tr}}CPlageAstreinte.filter{{/tr}}</th>
  </tr>
  <tr>
  <tr>
    <td>
      <form name="searchplanning" method="get" onsubmit="return loadPlanning(this)">
        <input type="hidden" name="m" value="{{$m}}"/>
        <input type="hidden" name="date_debut" value="{{$filter->date_debut}}"/>
        <table class="form">
          <tr>
            <th>{{mb_label object=$filter field="user_id"}}</th>
            <td>
               <select name="user_id" onchange="toggleYear(this.form); getForm('searchplanning').onsubmit();">
                 <option value="">{{tr}}CMediusers.all{{/tr}}</option>
                 <option value="-1">{{tr}}CMediusers.allprofSante{{/tr}}</option>
                 <option value="-2">{{tr}}CMediusers.allNonprofSante{{/tr}}</option>
                 {{mb_include module=mediusers template=inc_options_mediuser list=$mediusers}}
               </select>
             </td>
           </tr>
           <tr>
             <th style="width: 50%;">{{tr}}CPlageAstreinte-choix-periode{{/tr}}</th>
             <td style="width: 50%;">
               <label>
                 <input onclick="getForm('searchplanning').onsubmit();" type="radio" name="choix"
                   {{if $choix=="semaine"}}checked{{/if}} value="semaine" /> {{tr}}week{{/tr}}
               </label>
               <label>
                 <input onclick="getForm('searchplanning').onsubmit();" type="radio" name="choix"
                   {{if $choix=="mois"}}checked{{/if}} value="mois" /> {{tr}}month{{/tr}}
               </label>
               <label>
                 <input onclick="getForm('searchplanning').onsubmit();" id="annee" type="radio" name="choix"
                   {{if $choix=="annee"}}checked{{/if}} value="annee" /> {{tr}}year{{/tr}}
               </label>
             </td>
           </tr>
        </table>
      </form>
    </td>
    <td style="width:200px;">
      {{mb_include template=inc_legend_planning_astreinte}}
    </td>
  </tr>
  <tr>
    <td id="planning" colspan="2">
    </td>
  </tr>
</table>
