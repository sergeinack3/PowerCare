{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_blocked_bed value=true}}

{{if $show_blocked_bed}}
  <tr>
  <td>
    <div id="lit_bloque_topo" class="clit_bloque draggable" style="display: inline-block;">
      <strong>[BLOQUER UN LIT]</strong>
    </div>
    <script>
      Main.add(function () {
        var container = $('lit_bloque_topo');
        new Draggable(container, {
          revert:   true,
          scroll:   window,
          ghosting: true
        });
      });
    </script>
  </td>
</tr>
<tr>
  <td>
    <div id="lit_urgence_topo" data-affectation-guid="lit_urgence" class="clit_bloque draggable"
         style="display: {{if "dPurgences"|module_active}}inline-block{{else}}none{{/if}};">
      <strong>[LIT EN URGENCE]</strong>
      <script>
        Main.add(function () {
          var container = $('lit_urgence_topo');
          new Draggable(container, {
            revert:   true,
            scroll:   window,
            ghosting: true
          });
        });
      </script>
    </div>
  </td>
</tr>
{{/if}}

{{foreach from=$list_patients_notaff item=_patients_notaff key=nom}}
  <tr>
    <th class="title" style="width:150px;">{{$nom}}</th>
  </tr>
  <tr>
    <td class="me-color-black-medium-emphasis">
      <div class="list-patients-non-places">
        {{foreach from=$_patients_notaff item=_affectation}}
          {{if isset($_affectation->affectation_id|smarty:nodefaults)}}
            {{mb_include module=hospi template=inc_vw_patient_affectation}}
          {{else}}
            {{assign var=_sejour  value=$_affectation}}
            {{assign var=_patient value=$_sejour->_ref_patient}}
            <div class="patient draggable" data-affectation-guid="{{$_sejour->_guid}}" data-patient-id="{{$_sejour->patient_id}}"
                 id="{{$_sejour->_guid}}"
                 data-last_operation_id="{{if $_sejour->_ref_last_operation && $_sejour->_ref_last_operation->_id}}{{$_sejour->_ref_last_operation->_id}}{{/if}}">
              <script>
                Main.add(function () {
                  var container = $('{{$_sejour->_guid}}');
                  new Draggable(container, {
                    revert:   true,
                    scroll:   window,
                    ghosting: true
                  });
                });
              </script>
              <form name="{{$_affectation->_guid}}" action="" method="post">
                <input type="hidden" name="dosql" value="do_affectation_aed" />
                <input type="hidden" name="del" value="0" />
                <input type="hidden" name="m" value="hospi" />
                <input type="hidden" name="affectation_id" value="" />
                <input type="hidden" name="sejour_id" value="{{$_sejour->_id}}" />
                <input type="hidden" name="entree" value="{{$_sejour->entree_prevue}}" />
                <input type="hidden" name="sortie" value="{{$_sejour->sortie_prevue}}" />
                <input type="hidden" name="lit_id" value="" />
              </form>
              <span class="{{if !$_sejour->entree_reelle }}patient-not-arrived{{/if}}" onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">{{$_patient}}</span>
              {{mb_include module=patients template=inc_icon_bmr_bhre patient=$_patient}}
              <div class="ssr-sejour-bar"
                   title="arrivée il y a {{$_sejour->_entree_relative}}j et départ prévu dans {{$_sejour->_sortie_relative}}j ">
                <div
                  style="width: {{if $_sejour->_duree}}{{math equation='100*(-entree / (duree))' entree=$_sejour->_entree_relative duree=$_sejour->_duree format='%.2f'}}{{else}}100{{/if}}%;"></div>
              </div>
              <div class="libelle compact">
                {{if "dPhospi prestations systeme_prestations"|gconf == "standard"}}
                  <em style="color: #f00;" title="Chambre {{if $_sejour->chambre_seule}}seule{{else}}double{{/if}}">
                    {{if $_sejour->chambre_seule}}CS{{else}}CD{{/if}}
                    {{if $_sejour->prestation_id}}- {{$_sejour->_ref_prestation->code}}{{/if}}
                  </em>
                {{elseif $_sejour->_liaisons_for_prestation|@count}}
                    {{mb_include module=hospi template=inc_vw_liaisons_prestation liaisons=$_sejour->_liaisons_for_prestation}}
                {{/if}}
                <div style="float:left;{{if !"dPhospi General show_age_patient"|gconf}}display:none;{{/if}}">({{$_patient->_age}})&nbsp;</div>
                {{$_sejour->libelle|lower}}
                {{$_sejour->_type_admission}}
              </div>
            </div>
          {{/if}}
          {{foreachelse}}
          Personne
        {{/foreach}}
      </div>
    </td>
  </tr>
{{/foreach}}
