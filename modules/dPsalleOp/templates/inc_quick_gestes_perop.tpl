{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{unique_id var=uid}}

{{mb_script module=salleOp script=geste_perop ajax=true}}

<script>
  Main.add(function () {
    GestePerop.showCIMs10();

    // Remove context menu
    if ($$('.tooltip-perop') || $$('.tooltip-geste')) {
      $$('.tooltip-perop').invoke('remove');
      $$('.tooltip-geste').invoke('remove');
    }
  });
</script>

<h2>{{$operation}}</h2>
<form name="addAnesthPerop-{{$uid}}" action="?" method="post"
      onsubmit="return onSubmitFormAjax(this, Control.Modal.close)">
  {{mb_key object=$evenement}}
  <input type="hidden" name="m" value="dPsalleOp" />
  <input type="hidden" name="dosql" value="do_geste_perop_multi_aed" />
  <input type="hidden" name="operation_id" value="{{$operation->_id}}" />
  <input type="hidden" name="patient_id" value="{{$operation->_ref_sejour->patient_id}}" />
  <input type="hidden" name="sejour_id"  value="{{$operation->sejour_id}}" />
  <input type="hidden" name="incident" value="0" />
  <input type="hidden" name="datetime" value="{{$filtre->_datetime}}" />
  <input type="hidden" name="_geste_perop_ids" value="" />
  <input type="hidden" name="_antecedent" value="0"/>

  <div>
    {{mb_label object=$filtre field=_datetime}} : {{mb_field object=$filtre field=_datetime form="addAnesthPerop-$uid" register=true onchange="\$V(this.form.datetime, this.value);"}}
      <input name="antecedent" type="checkbox" value="" onchange="GestePerop.incidentAntecedent(this.form);"/>

      {{tr}}CAntecedent-action-Make antecedent|pl{{/tr}}
  </div>

  <div id="do_antecedent" style="display: none;"></div>

  {{if $gestes_user|@count}}
    <div style="text-align: right;">
      <em>{{tr}}CUser{{/tr}} &ndash; </em>
    </div>
    <table class="main tbl">
      <tr>
        <td class="text">
          {{foreach from=$gestes_user key=chapitre item=categories}}
            <table class="tbl">
              <tr>
                <th class="title">{{$chapitre}}</th>
              </tr>
              {{foreach from=$categories key=categorie item=cat_gestes}}
                <tr>
                  <th class="section">{{$categorie}}</th>
                </tr>
                <tr>
                  <td class="text">
                    {{foreach from=$cat_gestes item=_geste}}
                    <div style="display: inline-block; width: 20em; margin-bottom: 3px;">
                      <label>
                        <input type="checkbox" name="gestes_user[{{$_geste->_id}}]" value="{{$_geste->_id}}" class="gestes" />
                        {{$_geste->libelle}}
                      </label>
                    </div>
                    {{/foreach}}
                  </td>
                </tr>
            {{/foreach}}
            </table>
          {{/foreach}}
        </td>
      </tr>
    </table>
  {{/if}}

  {{if $gestes_function|@count}}
    <div style="text-align: right;">
      <em>{{tr}}CFunction{{/tr}} &ndash; </em>
    </div>
    <table class="main tbl">
      <tr>
        <td class="text">
            {{foreach from=$gestes_function key=chapitre item=categories}}
              <table class="tbl">
              <tr>
                <th class="title">{{$chapitre}}</th>
              </tr>
                {{foreach from=$categories key=categorie item=cat_gestes}}
                  <tr>
                    <th class="section">{{$categorie}}</th>
                  </tr>
                  <tr>
                    <td class="text">
                        {{foreach from=$cat_gestes item=_geste}}
                          <div style="display: inline-block; width: 20em; margin-bottom: 3px;">
                            <label>
                              <input type="checkbox" name="gestes_function[{{$_geste->_id}}]" value="{{$_geste->_id}}" class="gestes" />
                                {{$_geste->libelle}}
                            </label>
                          </div>
                        {{/foreach}}
                    </td>
                  </tr>
                {{/foreach}}
              </table>
            {{/foreach}}
        </td>
      </tr>
    </table>
  {{/if}}

  {{if $gestes_group|@count}}
    <div style="text-align: right;">
      <em>{{tr}}CGroups{{/tr}} &ndash; </em>
    </div>
    <table class="main tbl">
      <tr>
        <td class="text">
            {{foreach from=$gestes_group key=chapitre item=categories}}
              <table class="tbl">
              <tr>
                <th class="title">{{$chapitre}}</th>
              </tr>
                {{foreach from=$categories key=categorie item=cat_gestes}}
                  <tr>
                    <th class="section">{{$categorie}}</th>
                  </tr>
                  <tr>
                    <td class="text">
                      {{foreach from=$cat_gestes item=_geste}}
                        <div style="display: inline-block; width: 20em; margin-bottom: 3px;">
                          <label>
                            <input type="checkbox" name="gestes_group[{{$_geste->_id}}]" value="{{$_geste->_id}}" class="gestes" />
                              {{$_geste->libelle}}
                          </label>
                        </div>
                      {{/foreach}}
                    </td>
                  </tr>
                {{/foreach}}
              </table>
            {{/foreach}}
        </td>
      </tr>
    </table>
  {{/if}}

  <table class="form" style="margin-top: 10px; background-color: #FFFFFF;">
    <tr>
      <td class="button">
        <button type="button" class="singleclick" title="{{tr}}CAnesthPerop-action-Save selected Perop gestures without incident-desc{{/tr}}"
                onclick="GestePerop.choosePrecisionsGeste(this.form, 0);">
          <i class="fas fa-check fa-lg" style="color: green;"></i> {{tr}}Validate{{/tr}}
        </button>
        <button type="button" class="singleclick" title="{{tr}}CAnesthPerop-action-Save the selected Perop gestures as an incident-desc{{/tr}}"
                onclick="GestePerop.choosePrecisionsGeste(this.form, 1);">
          <i class="fas fa-exclamation-triangle fa-lg" style="color: red;"></i> {{tr}}CAnesthPerop-incident{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>