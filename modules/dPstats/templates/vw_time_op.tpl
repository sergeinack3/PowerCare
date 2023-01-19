{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=ccam_selector}}

<table class="main">
  <tr>
    <td>
      <form name="bloc" method="get">
        <input type="hidden" name="m" value="stats" />
        <input type="hidden" name="_chir" value="{{$user_id}}" />
        <input type="hidden" name="_class" value="" />
        <table class="form">
          <tr>
            <th colspan="2" class="category">
              <select name="typeVue" onChange="this.form.submit();">
                <option value="0">
                  Moyenne totale des temps opératoires
                </option>
                <option value="1" {{if $typeVue == 1}}selected{{/if}}>
                  Moyenne totale des temps de préparation
                </option>
                <option value="2" {{if $typeVue == 2}}selected{{/if}}>
                  Moyenne totale des temps d'hospitalisation
                </option>
              </select>
            </th>
          </tr>

          <tr>
            <th>
              <label for="nb_sejour_mini" title="Occurence mini">Nombre de séjours mini</label>
            </th>
            <td>
              <select name="nb_sejour_mini">
                {{foreach from="|"|explode:"1|2|3|4|5|10|20|30|40|50|100" item=i}}
                  <option value="{{$i}}" {{if $nb_sejour_mini == $i}}selected{{/if}}>{{$i}}</option>
                {{/foreach}}
              </select>
            </td>
          </tr>

          {{if in_array($typeVue, array(0, 2, 3))}}
            <tr>
              <th>
                <label for="codeCCAM" title="Acte CCAM">Acte CCAM</label>
              </th>
              <td>
                <input type="text" name="codeCCAM" value="{{$codeCCAM|stripslashes}}" />
                <button type="button" class="search" onclick="CCAMSelector.init()">Sélectionner un code</button>

                <script>
                  CCAMSelector.init = function () {
                    this.sForm = "bloc";
                    this.sView = "codeCCAM";
                    this.sChir = "_chir";
                    this.sClass = "_class";
                    this.pop();
                  }
                </script>
              </td>
            </tr>
            <tr>
              <th><label for="prat_id" title="Praticien">Praticien</label></th>
              <td>
                <select name="prat_id">
                  <option value="0">&mdash; Tous les praticiens</option>
                  {{mb_include module=mediusers template=inc_options_mediuser list=$listPrats selected=$prat_id}}
                </select>
              </td>
            </tr>
          {{/if}}
          {{if $typeVue == 2}}
            <tr>
              <th><label for="type" title="Type d'hospitalisation">Type</label></th>
              <td>
                <select name="type">
                  <option value="">
                    &mdash; Tous les types
                  </option>
                  {{foreach from=$listHospis key=key_typeHospi item=curr_typeHospi}}
                    <option value="{{$key_typeHospi}}" {{if $key_typeHospi==$type}}selected{{/if}}>
                      {{tr}}CSejour.type.{{$key_typeHospi}}{{/tr}}
                    </option>
                  {{/foreach}}
                </select>
              </td>
            </tr>
          {{/if}}
          <tr>
            <td colspan="2" class="button">
              <button type="submit" class="search">Afficher</button>
            </td>
          </tr>
        </table>
      </form>
      {{if $typeVue == 0}}
        {{assign var=template value=inc_vw_timeop_op}}
      {{elseif $typeVue == 1}}
        {{assign var=template value=inc_vw_timeop_prepa}}
      {{elseif $typeVue == 2}}
        {{assign var=template value=inc_vw_timehospi}}
      {{else}}
        {{assign var=template value=inc_vw_timeop_reveil}}
      {{/if}}

      {{mb_include module=stats template=$template}}
    </td>
  </tr>
</table>