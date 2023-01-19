{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  largeur_nom = 135;

  loadPlanning = function(form) {
    var url = new Url("personnel", "ajax_planning");
    url.addFormData(form);
    url.requestUpdate("planning-"+$V(form.type_view));
    return false;
  };

  display_plage = function (plage_id, debut, fin, type_view) {
    var plage_view = $('planning-'+type_view);
    var width = parseInt(plage_view.down("#schedule").getWidth()) - largeur_nom;
    var plage = plage_view.down("#plage" + plage_id);
    var width_calc = (fin * (width/nombreelem + 0.4).floor());
    var margin_calc = 0;

    if ((debut*width/nombreelem).ceil() < 0) {
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

  toggleYear = function (form) {
    if($V(form.user_id) == '') {
      form.choix[2].disabled = "disabled";
      $V(form.choix, "mois");
    }
    else {
      form.choix[2].disabled = "";
    }
  };

  Main.add(function() {
    var form = getForm("searchplanning-{{$type_view}}");
    var choixannee = $('annee');

    loadPlanning(form);

    if ($V(form.user_id) == "") {
      choixannee.checked='';
      choixannee.disabled='disabled';
    }
    else {
      choixannee.disabled='';
    }
  });
</script>

<table class="main me-w100">
  <tr>
    <td colspan="2">
      <form name="searchplanning-{{$type_view}}" method="get" onsubmit="return loadPlanning(this)">
        <input type="hidden" name="m" value="{{$m}}"/>
        <input type="hidden" name="date_debut" value="{{$filter->date_debut}}"/>
        <input type="hidden" name="type_view" value="{{$type_view}}"/>
        <table class="form me-no-align me-margin-top-8">
        {{if $affiche_nom==1}}
          <tr>
            <th>{{mb_label object=$filter field="user_id"}}</th>
            <td>
               <select name="user_id" onchange="toggleYear(this.form);this.form.onsubmit();">
                 <option value="">{{tr}}CMediusers.all{{/tr}}</option>
                 {{mb_include module=mediusers template=inc_options_mediuser list=$mediusers selected=$filter->user_id}}
               </select>
             </td>
           </tr>
         {{/if}}

          <tr>
            <th style="width: 50%;">{{tr}}CPlageConge-choix-periode{{/tr}}</th>
            <td style="width: 50%;">
              <label>
                <input onclick="this.form.onsubmit();" type="radio" name="choix"
                  {{if $choix=="semaine"}}checked{{/if}} value="semaine" /> {{tr}}week{{/tr}}
              </label>
              <label>
                <input onclick="this.form.onsubmit();" type="radio" name="choix"
                  {{if $choix=="mois"}}checked{{/if}} value="mois" /> {{tr}}month{{/tr}}
              </label>
              <label>
                <input onclick="this.form.onsubmit();" id="annee" type="radio" name="choix"
                  {{if $choix=="annee"}}checked{{/if}} value="annee" /> {{tr}}year{{/tr}}
              </label>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
  <tr>
    <td id="planning-{{$type_view}}" colspan="2"></td>
  </tr>
</table>
