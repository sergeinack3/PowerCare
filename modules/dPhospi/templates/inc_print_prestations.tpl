{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=patient}}

<script>
  Main.add(function () {
    window.print();
  });
</script>

{{mb_include module=soins template=inc_patient_banner object=$sejour patient=$sejour->_ref_patient}}

<button type="button" class="print not-printable" style="float: right;" onclick="window.print();">{{tr}}Print{{/tr}}</button>

<div style="margin-top: 2em;">
  <table class="tbl">
    <tr>
      <th>{{tr}}common-Date{{/tr}}</th>
      <th>{{tr}}CItemLiaison-souhait{{/tr}}</th>
      <th>{{tr}}CItemLiaison-realise{{/tr}}</th>
      <th style="width: 1%;">{{tr}}CItemLiaison-quantite{{/tr}}</th>
    </tr>

    {{foreach from=$dates key=liaison_id item=_intervalle}}
      {{assign var=liaison value=$liaisons.$liaison_id}}
      {{assign var=prestation value=$liaison->_ref_prestation}}
      {{assign var=item_souhait value=$liaison->_ref_item}}
      {{assign var=item_realise value=$liaison->_ref_item_realise}}
      {{assign var=sous_item value=$liaison->_ref_sous_item}}
      <tr>
        {{if $item_souhait->_id || (!$only_souhait && $item_realise->_id)}}
          <td>
            {{mb_include module=system template=inc_interval_date from=$_intervalle.debut to=$_intervalle.fin}}
          </td>
          <td>
            {{if $item_souhait->_id}}
              <h2>
                <strong>
                  {{if $sous_item->_id}}
                    {{$sous_item}}
                  {{else}}
                    {{$item_souhait}}
                  {{/if}}
                </strong>
                <br />
                {{mb_include module=system template=inc_interval_date from=$_intervalle.debut to=$_intervalle.fin}} ({{$prestation}})
              </h2>
            {{/if}}
          </td>
          <td>
            {{if !$only_souhait && $item_realise->_id}}
              <h2>
                <strong>
                  {{if $item_realise}}
                    {{if $sous_item->_id}}
                      {{$sous_item}}
                    {{else}}
                      {{$item_realise}}
                    {{/if}}
                  {{/if}}
                </strong>
              </h2>
            {{/if}}
          </td>
          <td style="text-align: right;">
            {{$_intervalle.qte}}
          </td>
        {{/if}}
      </tr>
    {{/foreach}}
  </table>
</div>