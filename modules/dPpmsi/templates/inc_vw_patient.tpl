{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $patient->_id}}
  <div id="vwPatient">
    {{mb_include module=patients template=inc_vw_identite_patient}}
  </div>
  <table class="form">
    <tr>
      <th class="category" colspan="2">Liste des séjours</th>
    </tr>

    {{foreach from=$patient->_ref_sejours item=_sejour}}
      {{if $_sejour->group_id == $g || "dPpatients sharing multi_group"|gconf == "full"}}
        <tr {{if $_sejour->_id == $isSejourPatient}}class="selected{{/if}}">
          <td class="text">
            {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$_sejour}}
            <a href="#{{$_sejour->_guid}}" onclick="loadSejour('{{$_sejour->_id}}'); $(this).up('tr').addUniqueClassName('selected')">
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
                  {{$_sejour->_shortview}}
                  {{if $_sejour->_nb_files_docs}}
                    - ({{$_sejour->_nb_files_docs}} Doc.)
                  {{/if}}
                </span>
            </a>
          </td>
          <td style="text-align: left;" {{if $_sejour->annule}}class="cancelled"{{/if}}>
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
          </td>
        </tr>
        {{foreach from=$_sejour->_ref_operations item=curr_op}}
          <tr>
            <td class="text" style="text-indent: 1em;">
              <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_op->_guid}}')">
                {{tr}}dPplanningOp-COperation of{{/tr}} {{$curr_op->_datetime|date_format:$conf.date}}
                {{if $curr_op->_nb_files_docs}}
                  - ({{$curr_op->_nb_files_docs}} Doc.)
                {{/if}}
              </span>
            </td>
            <td style="text-align: left;" {{if $curr_op->annulee}}class="cancelled"{{/if}}>
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$curr_op->_ref_chir}}
            </td>
          </tr>
        {{/foreach}}
      {{elseif "dPpatients sharing multi_group"|gconf == "limited" && !$_sejour->annule}}
        <tr>
          <td>
            {{$_sejour->_shortview}}
          </td>
          <td style="background-color:#afa">
            {{$_sejour->_ref_group->text|upper}}
          </td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{else}}
  <div class="small-info">Aucun résultat ne correspond à votre recherche</div>
{{/if}}