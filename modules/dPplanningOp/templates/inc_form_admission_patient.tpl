{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=config_sejour value=$conf.dPplanningOp.CSejour}}

<form name="plageSelectorFrm{{$rank}}" method="get">
  <input type="hidden" name="_date" value="" />
  <input type="hidden" name="_salle_id" value="" />
  <input type="hidden" name="_plage_id" value="" />

  {{if $multiple}}
  <div class="dhe_multiple" style="min-height: 95%;">
  {{/if}}

    <table class="form me-no-box-shadow me-small-form">
      {{if $multiple}}
      <tr id="tools_DHE_{{$rank}}" class="tools_DHE {{if $rank == 1}}selected{{/if}}">
        <td class="button" colspan="2">
          <button type="button" class="target" onclick="DHEMultiple.selRank({{$rank}})">Séjour {{$rank}}</button>
          {{if $rank != 1}}
            <button type="button" class="erase notext" onclick="DHEMultiple.removeSlot({{$rank}})"></button>
          {{/if}}
        </td>
      </tr>
      {{/if}}
      <tr>
        <th colspan="2" class="category">
          Admission du patient
        </th>
      </tr>
      <tr>
        <td class="narrow">
          <input type="radio" name="admission" value="veille" />
          <label for="admission_veille">La veille à</label>
        </td>
        <td class="greedyPane">
          <select name="hour_veille">
            {{foreach from=$config_sejour.heure_deb|range:$config_sejour.heure_fin item=hour}}
              <option value="{{$hour}}" {{if 'dPplanningOp CSejour default_hours heure_entree_veille'|gconf == $hour}}selected{{/if}}>{{$hour}}</option>
            {{/foreach}}
          </select>
          h
          <select name="min_veille">
            {{foreach from=0|range:59:$config_sejour.min_intervalle item=min}}
              <option value="{{$min}}">{{$min}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
      <tr>
        <td>
          <input type="radio" name="admission" value="jour" />
          <label for="admission_jour">Le jour même à</label>
        </td>
        <td>
          <select name="hour_jour">
            {{assign var=heure_entree_jour value='dPplanningOp CSejour default_hours heure_entree_jour'|gconf}}
            {{foreach from=$config_sejour.heure_deb|range:$config_sejour.heure_fin item=hour}}
              <option value="{{$hour}}" {{if $heure_entree_jour == $hour}}selected{{/if}}>{{$hour}}</option>
            {{/foreach}}
          </select>
          h
          <select name="min_jour">
            {{foreach from=0|range:59:$config_sejour.min_intervalle item=min}}
              <option value="{{$min}}" {{if 'dPplanningOp CSejour default_hours min_entree_jour'|gconf == $min}}selected{{/if}}>{{$min}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
      {{if $rank == 1}}
      <tr>
        <td colspan="2">
          <input type="radio" name="admission" value="aucune" />
          <label for="admission_aucune">Ne pas modifier</label>
        </td>
      </tr>
      {{/if}}
      <tr>
        <td colspan="2" class="text">
          <div class="small-info">
            Le choix de l'heure de passage est remplacé par les flèches dans le programme ci-dessous.<br />
            Afin de placer une intervention, cliquez sur la flèche correspondante.
          </div>
        </td>
      </tr>
      <tr>
        <td colspan="2" id="prog_plageop_{{$rank}}"></td>
      </tr>
      {{if !$multiple}}
      <tr>
        <td colspan="2" class="button">
          <button class="cancel me-tertiary" type="button" onclick="window._close()">{{tr}}Cancel{{/tr}}</button>
          <button id="didac_button_OK" class="tick" type="button" onclick="setClose('', '', '')">{{tr}}OK{{/tr}}</button>
        </td>
      </tr>
      {{/if}}
    </table>

  {{if $multiple}}
  </div>
  {{/if}}

</form>
