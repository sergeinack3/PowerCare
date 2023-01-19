{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th>{{mb_include module=system template=inc_vw_mbobject object=$_evenement->_ref_sejour->_ref_patient}}</th>
  <th>
    {{if $smarty.foreach.loop_evts.first}}
      <button type="button" class="left notext" title="{{tr}}CActe-spread.all{{/tr}}" onclick="Evt_SSR.addAllTypesPatientActes(this);">
      </button>
    {{/if}}
  </th>
  <th>{{mb_include module=system template=inc_vw_mbobject object=$evenement->_ref_sejour->_ref_patient}}</th>
</tr>

{{foreach from=$actes item=_actes_by_code key=_type_acte}}
  {{if (isset($_actes_by_code|smarty:nodefaults) && $_actes_by_code|@count > 0 && isset($evenement->_ref_actes[$_type_acte]|smarty:nodefaults)) ||
  (isset($evenement->_ref_actes[$_type_acte]|smarty:nodefaults) && $evenement->_ref_actes[$_type_acte]|@count > 0)}}
    <tr>
      <th class="section" colspan="3">
        {{if in_array($_type_acte, array('prestas'))}}
          {{mb_value object=$types_acte.$_type_acte field=type}}
        {{else}}
          {{tr}}{{$types_acte.$_type_acte->_class}}{{/tr}}
        {{/if}}
      </th>
    </tr>
    <tr>
      <td class="halfPane" class="codesToDelete">
        {{foreach from=$_actes_by_code item=_actes key=_code_acte}}
          {{foreach name="actes" from=$_actes item=_acte}}
            <label>
              <button type="button" class="cancel notext"
                      onclick="Evt_SSR.deletePatientActe(
                        '{{$_acte->_id}}',
                        '{{$_type_acte}}',
                        '{{$_acte->_spec->key}}',
                        '{{$_acte->_view|smarty:nodefault|JSAttribute}}'
                        )">
                {{tr}}Delete{{/tr}}
              </button>
              {{$_code_acte}}
            </label>
            <br />
          {{/foreach}}
        {{foreachelse}}
          <span class="empty">{{tr}}CActe.none{{/tr}}</span>
        {{/foreach}}
      </td>
      <td class="narrow" style="vertical-align: middle;">
        <button id="{{$_evenement->_guid}}-{{$_type_acte}}-addAllActesButton" type="button" class="left notext addAllActesButton"
                title="{{tr}}CActe-spread{{/tr}}" onclick="Evt_SSR.addAllPatientActes(this);"
        </button>
      </td>
      <td class="codesToSpread">
        {{assign var=disable_button value=true}}
        {{foreach from=$evenement_codes_by_type[$_type_acte] key=_acte_name item=_actes}}
          {{foreach from=$_actes item=_acte name="actes_by_code"}}
            {{assign var=actes_by_type value=$codes_by_evt_and_type.$_evenement_ssr_id.$_type_acte}}
            {{if array_key_exists($_acte_name, $actes_by_type)}}
              {{assign var=count_codes value=$codes_by_evt_and_type.$_evenement_ssr_id.$_type_acte.$_acte_name|@count}}
            {{else}}
              {{assign var=count_codes value=0}}
            {{/if}}
            <label>
              {{if $smarty.foreach.actes_by_code.index < $count_codes}}
                <i class="fa fa-check" style="padding-left:5px;color:green;"></i>
              {{else}}
                {{assign var=disable_button value=false}}
                <button type="button" class="add notext"
                        onclick="Evt_SSR.addPatientActe(
                          '{{$_acte->_id}}',
                          '{{$_type_acte}}',
                          '{{$_acte->_spec->key}}',
                          '{{$_acte->code}}',
                          '{{$_evenement->_id}}',
                          '{{$evenement->_id}}'
                          )">
                  {{tr}}CActe-spread{{/tr}}
                </button>
              {{/if}}
              {{$_acte->_view}}
            </label>
            <br />
          {{/foreach}}
        {{foreachelse}}
          <span class="empty">{{tr}}CActe-toSpread.none{{/tr}}</span>
        {{/foreach}}
        {{if $disable_button}}
          <script>
            Main.add(function() {
              $("{{$_evenement->_guid}}-{{$_type_acte}}-addAllActesButton").disable();
            });
          </script>
        {{/if}}
      </td>
    </tr>
  {{/if}}
{{/foreach}}
