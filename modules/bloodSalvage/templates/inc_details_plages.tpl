{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Plages -->
{{foreach from=$salle->_ref_plages item=_plage}}
  <hr />
  <form name="anesth{{$_plage->_id}}" method="post" class="{{$_plage->_spec}}">
    <input type="hidden" name="m" value="bloc" />
    <input type="hidden" name="otherm" value="{{$m}}" />
    <input type="hidden" name="dosql" value="do_plagesop_aed" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="_repeat" value="1" />
    <input type="hidden" name="plageop_id" value="{{$_plage->_id}}" />
    <input type="hidden" name="chir_id" value="{{$_plage->chir_id}}" />
    <input type="hidden" name="spec_id" value="{{$_plage->spec_id}}" />

    <table class="form">
      <tr>
        <th class="category{{if $vueReduite}} text{{/if}}" colspan="2">
          <a href="?m=bloc&tab=vw_edit_interventions&plageop_id={{$_plage->_id}}" title="{{tr}}COperation-back-plage-op.edit_shift{{/tr}}">
            {{tr}}CMedecin.titre.chir{{/tr}} : {{tr}}CMedecin.titre.dr{{/tr}} {{$_plage->_ref_chir->_view}}
            {{if $vueReduite}}
              <br />
            {{else}}
              -
            {{/if}}
            {{$_plage->debut|date_format:$conf.time}} à
            {{$_plage->fin|date_format:$conf.time}}
          </a>
        </th>
      </tr>

      <tr>
        {{if $vueReduite}}
          <th class="category" colspan="2">
            {{if $_plage->anesth_id}}
                {{tr}}CMedecin.titre.anesth{{/tr}} : {{tr}}CMedecin.titre.dr{{/tr}} {{$_plage->_ref_anesth->_view}}
            {{else}}
              -
            {{/if}}
          </th>
        {{else}}
          <th><label for="anesth_id" title="{{tr}}CPlageOp-anesth_id-desc{{/tr}}">{{tr}}CBloodSalvage.anesthesist{{/tr}}</label></th>
          <td>
            <select name="anesth_id" onchange="submit()">
              <option value="">&mdash; {{tr}}Choose{{/tr}} {{tr}}un{{/tr}} {{tr}}CBloodSalvage.anesthesist{{/tr}}<</option>
              {{foreach from=$listAnesths item=curr_anesth}}
                <option
                  value="{{$curr_anesth->user_id}}" {{if $_plage->anesth_id == $curr_anesth->user_id}} selected="selected" {{/if}}>
                  {{$curr_anesth->_view}}
                </option>
              {{/foreach}}
            </select>
          </td>
        {{/if}}
      </tr>
    </table>
  </form>
  <table class="tbl">
    {{if $_plage->_ref_operations}}
      {{mb_include module=bloodSalvage template=inc_liste_operations urgence=0 operations=$_plage->_ref_operations}}
    {{/if}}

    {{if $_plage->_unordered_operations}}
      <tr>
        <th colspan="10">{{tr}}COperation-back-plageop.not_placed{{/tr}}</th>
      </tr>
      {{mb_include module=bloodSalvage template=inc_liste_operations urgence=0 operations=$_plage->_unordered_operations}}
    {{/if}}
  </table>
{{/foreach}}

<!-- Déplacées -->
{{if $salle->_ref_deplacees|@count}}
  <hr />
  <table class="form">
    <tr>
      <th class="category" colspan="2">
        {{tr}}COperation-back-plageop.moved{{/tr}}
      </th>
    </tr>
  </table>
  <table class="tbl">
    {{mb_include module=bloodSalvage template=inc_liste_operations urgence=1 operations=$salle->_ref_deplacees}}
  </table>
{{/if}}

<!-- Urgences -->
{{if $salle->_ref_urgences|@count}}
  <hr />
  <table class="form">
    <tr>
      <th class="category" colspan="2">
        {{tr}}CSejour.type.urg{{/tr}}
      </th>
    </tr>
  </table>
  <table class="tbl">
    {{mb_include module=bloodSalvage template=inc_liste_operations urgence=1 operations=$salle->_ref_urgences}}
  </table>
{{/if}}
