{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $fiche_autonomie->_id || !"forms"|module_active || ("forms"|module_active && !$conf.ssr.CFicheAutonomie.use_ex_form)}}
  <form name="editFicheAutonomie" action="?m={{$m}}" method="post" onsubmit="return checkForm(this);">
    {{mb_class object=$fiche_autonomie}}
    {{mb_key object=$fiche_autonomie}}
    {{mb_field object=$fiche_autonomie field=sejour_id  hidden=1}}
    <table class="form me-small-form">
      <tr>
        <th class="category" colspan="10">{{tr}}CFicheAutonomie-autonomie-perso{{/tr}}</th>
      </tr>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="alimentation" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="alimentation" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="toilette" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="toilette" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="habillage_haut" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="habillage_haut" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="habillage_bas" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="habillage_bas" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="toilettes" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="toilettes" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="utilisation_toilette" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="utilisation_toilette" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="transfert_lit" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="transfert_lit" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="locomotion" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="locomotion" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="locomotion_materiel" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="locomotion_materiel" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="escalier" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="escalier" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th class="category" colspan="10">{{tr}}CFicheAutonomie-soins_cutanes{{/tr}}</th>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="pansement" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="pansement" typeEnum="radio" default=""}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="escarre" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="escarre" typeEnum="radio" default=""}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="soins_cutanes"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="soins_cutanes" form="editFicheAutonomie"}}</td>
        </tr>
      </tbody>
      <tr>
        <th class="category" colspan="10">{{tr}}CFicheAutonomie-capacite_relationnelle{{/tr}}</th>
      </tr>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="comprehension" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="comprehension" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="expression" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="expression" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="memoire" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="memoire" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="resolution_pb" typeEnum="radio"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="resolution_pb" typeEnum="radio"}}</td>
        </tr>
      </tbody>
      <tr>
        <th class="category" colspan="10">{{tr}}CFicheAutonomie-etat_psychique{{/tr}}</th>
      </tr>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="etat_psychique"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="etat_psychique" form="editFicheAutonomie"}}</td>
        </tr>
      </tbody>
      <tr>
        <th class="category" colspan="10">{{tr}}CFicheAutonomie-antecedents{{/tr}} &amp; {{tr}}CFicheAutonomie-traitements{{/tr}}</th>
      </tr>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="antecedents"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="antecedents" form="editFicheAutonomie"}}</td>
        </tr>
      </tbody>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="traitements"}}</th>
          <td>{{mb_field object=$fiche_autonomie field="traitements" form="editFicheAutonomie"}}</td>
        </tr>
      </tbody>
      <tr>
        <th class="category" colspan="10">{{tr}}CFicheAutonomie-devenir_envisage{{/tr}}</th>
      </tr>
      <tbody class="hoverable">
        <tr>
          <th>{{mb_label object=$fiche_autonomie field="devenir_envisage"}}</th>
          <td>
            {{tr}}CFicheAutonomie-devenir_envisage_dom{{/tr}}
            <input type="radio" name="_devenir_envisage" value="1" {{if !$fiche_autonomie->devenir_envisage}}checked="checked"{{/if}} onchange="$V(this.form.devenir_envisage,''); $('devenir').hide();"/>
            {{tr}}Other{{/tr}} <input type="radio" name="_devenir_envisage" value="0" {{if  $fiche_autonomie->devenir_envisage}}checked="checked"{{/if}} onchange="$('devenir').show();"/>

            <div id="devenir" {{if !$fiche_autonomie->devenir_envisage}}style="display: none"{{/if}}>
              {{mb_field object=$fiche_autonomie field="devenir_envisage"}}
            </div>
          </td>
        </tr>
      </tbody>
      <tr>
        <td class="button" colspan="6">
          <button type="button" class="submit" onclick="onSubmitFormAjax(this.form);">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>

{{else}}
  {{unique_id var=unique_id_fich_autonomie}}
  <div id="fiche_auto_{{$unique_id_fich_autonomie}}">
    <script>
      createBilanSSRcallback{{$unique_id_fich_autonomie}} = function(bilan_id, obj) {
        updateBilanId(bilan_id, obj);
        ExObject.loadExObjects("CBilanSSR", bilan_id, "fiche_auto_{{$unique_id_fich_autonomie}}", 0);
      }
    </script>
    {{if !$bilan->_id}}
      <form name="Create-CBilanSSR" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
        <input type="hidden" name="dosql" value="do_bilan_ssr_aed" />
        <input type="hidden" name="callback" value="createBilanSSRcallback{{$unique_id_fich_autonomie}}" />
        {{mb_key object=$bilan}}
        {{mb_class object=$bilan}}
        {{mb_field object=$bilan field=sejour_id hidden=1}}
        <button type="submit" class="new">{{tr}}ssr-acces_fiche_autonomie{{/tr}}</button>
      </form>
    {{else}}
      <script>
        Main.add(function(){
          ExObject.loadExObjects("{{$bilan->_class}}", "{{$bilan->_id}}", "fiche_auto_{{$unique_id_fich_autonomie}}", 0);
        });
      </script>
    {{/if}}
  </div>
{{/if}}