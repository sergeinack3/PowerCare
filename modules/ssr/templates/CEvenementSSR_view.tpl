{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{assign var=evenement_ssr       value=$object}}
{{assign var=evenement_ssr_id    value=$evenement_ssr->_id}}
{{assign var=unique_id           value=""|uniqid}}
{{assign var=prestas_ssr         value=$evenement_ssr->_refs_prestas_ssr}}
{{assign var=counter_prestas_ssr value=$evenement_ssr->_counter_prestas_ssr}}

{{mb_include module=system template=CMbObject_view}}

{{if $evenement_ssr->_ref_transmissions|@count}}
  <table class="tbl tooltip">
    <tr>
      <td>
        <strong>{{tr}}CEvenementSSR-back-transmissions{{/tr}}:</strong>
        <ul>
          {{if $evenement_ssr->_ref_evenements_seance|@count}}
            {{foreach from=$evenement_ssr->_ref_evenements_seance item=_seance}}
              <li>
                {{$_seance->_ref_sejour->_ref_patient}}
                <ul>
                  {{foreach from=$_seance->_ref_transmissions item=_transmission}}
                    <li>{{$_transmission->_ref_user->_view}} - {{$_transmission->date|date_format:$conf.datetime}}:<br /> {{$_transmission->text}}</li>
                  {{/foreach}}
                </ul>
              </li>
            {{/foreach}}
          {{else}}
            {{foreach from=$evenement_ssr->_ref_transmissions item=_transmission}}
              <li>{{$_transmission->_ref_user->_view}} - {{$_transmission->date|date_format:$conf.datetime}}:<br /> {{$_transmission->text}}</li>
            {{/foreach}}
          {{/if}}
        </ul>
      </td>
    </tr>
  </table>
{{/if}}

<table class="tbl tooltip">
  {{if $evenement_ssr->sejour_id}}
    {{if $evenement_ssr->_ref_actes_cdarr|@count || $evenement_ssr->_ref_actes_csarr|@count}}
      <!-- Actes CdARRs -->
      {{if $evenement_ssr->_ref_actes_cdarr|@count}}
      <tr>
        <td class="text">
          <strong>{{tr}}CEvenementSSR-back-actes_cdarr{{/tr}}</strong> :
          {{foreach from=$evenement_ssr->_ref_actes_cdarr item=_acte}}
            {{$_acte}}
          {{/foreach}}
        </td>
      </tr>
      {{/if}}
      <!-- Actes CdARRs -->
      {{if $evenement_ssr->_ref_actes_csarr|@count}}
        <tr>
          <td class="text">
            <strong>{{tr}}CEvenementSSR-back-actes_csarr{{/tr}}</strong> :
            {{foreach from=$evenement_ssr->_ref_actes_csarr item=_acte}}
              {{$_acte}}
            {{/foreach}}
          </td>
        </tr>
      {{/if}}
      {{elseif "ssr general use_acte_presta"|gconf == 'csarr'
        && !$evenement_ssr->_ref_actes_csarr|@count
        && !$evenement_ssr->patient_missing}}
        <tr>
          <td>
            <div class="small-warning">
              {{tr}}CEvenementSSR-warning-no_code_ssr{{/tr}}
            </div>
          </td>
        </tr>
      {{/if}}

    {{if $prestas_ssr|@count}}
      <!-- Prestations SSR -->
      {{if $prestas_ssr|@count}}
        <tr>
          <td class="text">
            <strong>{{tr}}CEvenementSSR-back-prestas_ssr{{/tr}}</strong> :
            {{foreach from=$counter_prestas_ssr key=code_presta item=_presta name=prestas}}
              {{$code_presta}} {{if $counter_prestas_ssr.$code_presta.quantity}}(x {{$counter_prestas_ssr.$code_presta.quantity}}){{/if}}{{if !$smarty.foreach.prestas.last}}, {{/if}}
            {{/foreach}}
          </td>
        </tr>
      {{/if}}
    {{elseif "ssr general use_acte_presta"|gconf == 'presta' && !$prestas_ssr|@count}}
      <tr>
        <td>
          <div class="small-warning">
            {{tr}}CEvenementSSR-warning-no_presta_ssr{{/tr}}
          </div>
        </td>
      </tr>
    {{/if}}
  {{else}}
    <tr>
      <td class="text">
        <strong>{{mb_label object=$evenement_ssr field="seance_collective_id"}}</strong>
        <ul>
        {{foreach from=$evenement_ssr->_ref_evenements_seance item=_evenement}}
          <li>{{$_evenement->_ref_sejour->_ref_patient}}</li>
        {{/foreach}}
        </ul>
      </td>
    </tr>
  {{/if}}
  {{assign var=seance_collective_id value=$evenement_ssr->seance_collective_id}}
  {{if !$evenement_ssr->sejour_id}}
    {{assign var=seance_collective_id value=$evenement_ssr->_id}}
  {{/if}}
  {{if $seance_collective_id}}
    {{mb_script module=ssr script=seance_collective ajax=true}}
    <tr>
      <td class="button">
        <button type="button" class="edit" onclick="Seance.gestionPatients('{{$seance_collective_id}}')">
          {{tr}}CEvenementSSR.gestionPatients{{/tr}}
        </button>
      </td>
    </tr>
  {{/if}}
</table>
