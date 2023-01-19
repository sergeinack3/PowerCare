{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=codage value=$object}}

<table class="tbl tooltip">
  <tr>
    <th class="category">{{tr}}CCodageCCAM-praticien_id{{/tr}}</th>
    <th class="category">{{tr}}CCodageCCAM-activite_anesth{{/tr}}</th>
    <th class="category">{{tr}}CCodageCCAM-date{{/tr}}</th>
    <th class="category">{{tr}}CCodageCCAM-association_mode{{/tr}}</th>
    <th class="category">{{tr}}CCodageCCAM-association_rule{{/tr}}</th>
    <th class="category">Actes cotés</th>
    <th class="category">Actions</th>
  </tr>
  <tr>
    <td>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$codage->_ref_praticien}}
    </td>
    <td>
      {{tr}}CCodageCCAM.activite_anesth.{{$codage->activite_anesth}}{{/tr}}
    </td>
    <td>
      {{$codage->date|date_format:$conf.date}}
    </td>
    <td>
      {{mb_value object=$codage field=association_mode}}
    </td>
    <td>
      {{mb_value object=$codage field=association_rule}}
    </td>
    <td>
      {{if !$codage->_ref_actes_ccam|@count}}
        {{tr}}CActeCCAM.none{{/tr}}
      {{else}}
        <table class="layout">
          {{foreach from=$codage->_ref_actes_ccam item=_acte}}
            {{assign var=_code_ccam value=$_acte->_ref_code_ccam}}
            {{assign var=code_activite value=$_acte->code_activite}}
            {{assign var=_activite value=$_code_ccam->activites[$code_activite]}}
            {{assign var=code_phase value=$_acte->code_phase}}
            {{assign var=_phase value=$_activite->phases[$code_phase]}}

            <tr>
              <td>
                <a href="#" onclick="CodeCCAM.show('{{$_acte->code_acte}}', '{{$codage->codable_class}}');">
                  {{$_acte->code_acte}}{{if $_acte->code_extension && $codage->codable_class != 'CConsultation'}}-{{$_acte->code_extension}}{{/if}}
                </a>
              </td>
              <td>
                <span class="circled ok">
                  {{$_acte->code_activite}}-{{$_acte->code_phase}}
                </span>
              </td>
              <td>
                {{if !$_phase->_modificateurs|@count}}
                  <em style="color: #7d7d7d;">Aucun modif. dispo.</em>
                {{elseif !$_acte->modificateurs}}
                  <strong>Aucun modif. codé</strong>
                {{else}}
                  {{foreach from=$_phase->_modificateurs item=_mod name=modificateurs}}
                    {{if $_mod->_checked}}
                      <span class="circled {{if in_array($_mod->_state, array('not_recommended', 'forbidden'))}}error{{/if}}"
                            title="{{$_mod->libelle}}">
                        {{$_mod->code}}
                      </span>
                    {{/if}}
                  {{/foreach}}
                {{/if}}
              </td>
              <td>
                {{if $_acte->code_association}}
                  Asso : {{$_acte->code_association}}
                {{/if}}
              </td>
              <td>
                {{if $_acte->montant_depassement && $codage->_show_depassement}}
                  <span class="circled" style="background-color: #aaf" title="{{mb_value object=$_acte field=montant_depassement}}">
                    DH
                  </span>
                {{/if}}
              </td>
            </tr>
          {{/foreach}}
        </table>
      {{/if}}
    </td>
    <td>
      {{if $codage->codable_class == 'CSejour'}}
        <button type="button" class="notext copy" onclick="duplicateCodage({{$codage->_id}});" title="{{tr}}CCodageCCAM-action-duplicate{{/tr}}">
          {{tr}}CCodageCCAM-action-duplicate{{/tr}}
        </button>
      {{/if}}

      {{if !$codage->locked}}
        <button type="button" class="notext edit" onclick="editCodages('{{$codage->codable_class}}', {{$codage->codable_id}}, {{$codage->praticien_id}}{{if $codage->codable_class == 'CSejour'}}, '{{$codage->date}}'{{/if}})"
                title="{{$codage->association_rule}} ({{mb_value object=$codage field=association_mode}})">
          {{tr}}Edit{{/tr}}
        </button>
      {{/if}}

      {{if $codage->locked}}
        <button type="button" class="notext unlock"
                onclick="unlockCodages({{$codage->praticien_id}}, '{{$codage->codable_class}}', {{$codage->codable_id}}{{if $codage->codable_class == 'CSejour'}}, '{{$codage->date}}'{{/if}})">
          {{tr}}Unlock{{/tr}}
        </button>
      {{else}}
        <button type="button" class="notext lock"{{if !$codage->_ref_actes_ccam|@count && (!$codage->_codage_sibling || ($codage->_codage_sibling->_id && !$codage->_codage_sibling->_ref_actes_ccam|@count))}}disabled="disabled"{{/if}}
                onclick="lockCodages({{$codage->praticien_id}}, '{{$codage->codable_class}}', {{$codage->codable_id}}{{if $codage->codable_class == 'CSejour'}}, '{{$codage->date}}'{{/if}})">
          {{tr}}Lock{{/tr}}
        </button>
      {{/if}}

      {{if !$codage->_ref_actes_ccam|@count && (!$codage->_codage_sibling || ($codage->_codage_sibling->_id && !$codage->_codage_sibling->_ref_actes_ccam|@count))}}
        <button type="button" class="notext trash"
                onclick="{{if $codage->codable_class == 'CSejour'}}
                  deleteCodages({{$codage->praticien_id}}, '{{$codage->date}}')
                {{else}}
                  deleteCodages({{$codage->praticien_id}})
                {{/if}}">.
          {{tr}}Delete{{/tr}}
        </button>
      {{/if}}
    </td>
  </tr>
</table>