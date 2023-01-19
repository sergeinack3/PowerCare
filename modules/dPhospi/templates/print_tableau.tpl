{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    window.print();
  });
</script>

<table class="main affectations me-align-auto">
  <tr>
    <th colspan="100">Affectations du {{$date|date_format:$conf.longdate}}</th>
  </tr>
  <tr>
    {{foreach from=$services item=_service}}
      {{if $_service->_ref_chambres|@count}}
        <td style="width: {{math equation=100/x x=$services|@count}}%;">
          <table class="tbl">
            <tr>
              <th class="title" colspan="3">{{$_service->nom}}</th>
            </tr>
            {{foreach from=$_service->_ref_chambres item=_chambre}}
              {{if !$_chambre->annule}}
                {{foreach from=$_chambre->_ref_lits item=_lit}}
                  {{if !$_lit->_ref_affectations|@count}}
                    <tr>
                      <th class="opacity-70" colspan="3">
                        {{if $_lit->nom_complet}}
                          <span style="float: left">{{$_lit->nom_complet}}</span>
                        {{else}}
                          <span style="float: left">{{$_chambre->_shortview}}</span>
                          <span style="float: right">{{$_lit->_shortview}}</span>
                        {{/if}}
                      </th>
                    </tr>
                  {{else}}
                    {{foreach from=$_lit->_ref_affectations item=_aff}}
                      {{assign var="_sejour" value=$_aff->_ref_sejour}}
                      {{assign var="_patient" value=$_sejour->_ref_patient}}
                      {{assign var="_aff_prev" value=$_aff->_ref_prev}}
                      {{assign var="_aff_next" value=$_aff->_ref_next}}
                      <tr>
                        <th colspan="3">
                          <span style="float: left">{{$_chambre->_shortview}}</span>
                          <span style="float: right">{{$_lit->_shortview}}</span>
                        </th>
                      </tr>
                      <tr>
                        <td class="text button" style="width: 1%;">
                          {{if $_sejour->_couvert_c2s}}
                            <div><strong>C2S</strong></div>
                          {{/if}}
                          {{if $_sejour->_couvert_ald}}
                            <div><strong>ALD</strong></div>
                          {{/if}}
                          {{mb_include module=hospi template=inc_vw_icones_sejour sejour=$_sejour curr_affectation=$_aff aff_next=$_aff_next}}
                        </td>
                        <td class="text"
                            {{if $_sejour->confirme}}style="background-image:url(images/icons/ray.gif); background-repeat:repeat;"{{/if}}>
                          {{if !$_sejour->entree_reelle || ($_aff_prev->_id && $_aff_prev->effectue == 0)}}
                          <span class="patient-not-arrived">
            {{elseif $_sejour->septique}}
                            <span class="septique">
            {{else}}
                              <span>
            {{/if}}
                                <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
                <strong>
                  {{if $_sejour->type == "ambu"}}<em>{{/if}}
                    {{$_patient}}
                    {{if $_patient->naissance}}({{$_patient->_age}}){{/if}}
                    {{if $_sejour->type == "ambu"}}</em>{{/if}}
                </strong>
              </span>
            </span>
                        </td>
                        <td style="width: 1%; background:#{{$_sejour->_ref_praticien->_ref_function->color}}">
                          {{$_sejour->_ref_praticien->_shortview}}
                        </td>
                      </tr>
                    {{/foreach}}
                  {{/if}}
                {{/foreach}}
              {{/if}}
            {{/foreach}}
          </table>
        </td>
      {{/if}}
    {{/foreach}}
  </tr>
</table>

