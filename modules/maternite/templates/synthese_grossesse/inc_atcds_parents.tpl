{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="me-w100" style="font-size: 100%;">
  <tr>
    <th class="category" colspan="4">{{tr}}CAntecedent|pl{{/tr}}</th>
  </tr>
  <tr>
    <td class="text">
        {{if $dossier_medical->_ref_antecedents_by_type}}
            {{foreach from=$dossier_medical->_ref_antecedents_by_type key=key_type item=_antecedent}}
                {{if $_antecedent && $key_type != "alle"}}
                  <strong>{{tr}}CAntecedent.type.{{$key_type}}{{/tr}}</strong>
                    {{foreach from=$_antecedent item=currAnt}}
                      <ul>
                        <li>
                            {{if $currAnt->appareil}}
                              <strong>{{tr}}CAntecedent.appareil.{{$currAnt->appareil}}{{/tr}}</strong>{{/if}}
                            {{if $currAnt->date}}
                                {{mb_value object=$currAnt field=date}} :
                            {{/if}}
                            {{$currAnt->rques}} {{if $currAnt->important}}
                            <strong>({{tr}}CAntecedent-important{{/tr}})</strong>
                            {{elseif $currAnt->majeur}}
                            <strong>({{tr}}CAntecedent-majeur{{/tr}})</strong>
                            {{/if}}
                        </li>
                      </ul>
                    {{/foreach}}
                {{/if}}
            {{/foreach}}
        {{else}}
          <ul>
            <li style="font-weight: bold;">{{tr}}CAntecedent-No antecedent provided{{/tr}}</li>
          </ul>
        {{/if}}
    </td>
  </tr>
    {{* Atcd Père*}}
  <tr>
    <th class="category" colspan="4">{{tr}}CDossierPerinat-tab-Paternal antecedent|pl{{/tr}}</th>
  </tr>
  <tr>
    <td class="text">
        {{assign var=father                 value=$grossesse->_ref_pere}}
        {{assign var=father_constantes      value=$dossier_perinatal->_ref_pere_constantes}}
        {{assign var=father_dossier_medical value=$father->_ref_dossier_medical}}

        {{if ($father_constantes && $father_constantes->_id) || ($father_dossier_medical && $father_dossier_medical->_id)}}
          <ul>
              {{if $father_constantes->poids}}
                <li>
                    {{mb_label object=$father_constantes field=poids}}
                  : {{mb_value object=$father_constantes field=poids}} {{tr}}common-kg{{/tr}}
                </li>
              {{/if}}
              {{if $father_constantes->taille}}
                <li>
                    {{mb_label object=$father_constantes field=taille}}
                  : {{mb_value object=$father_constantes field=taille}} {{tr}}common-cm{{/tr}}
                </li>
              {{/if}}
              {{if $father_dossier_medical->groupe_sanguin && $father_dossier_medical->rhesus}}
                <li>
                    {{mb_value object=$father_dossier_medical field=groupe_sanguin}} {{mb_value object=$father_dossier_medical field=rhesus}}
                </li>
              {{/if}}
              {{if $father_dossier_medical->groupe_ok}}
                <li>
                    {{mb_label object=$father_dossier_medical field=groupe_ok}}
                  : {{mb_value object=$father_dossier_medical field=groupe_ok}}
                </li>
              {{/if}}
              {{if $dossier_perinatal->pere_serologie_vih}}
                <li>
                    {{mb_label object=$dossier_perinatal field=pere_serologie_vih}}
                  : {{mb_value object=$dossier_perinatal field=pere_serologie_vih}}
                </li>
              {{/if}}
              {{if $dossier_perinatal->pere_electrophorese_hb}}
                <li>
                    {{mb_label object=$dossier_perinatal field=pere_electrophorese_hb}}
                  : {{mb_value object=$dossier_perinatal field=pere_electrophorese_hb}}
                </li>
              {{/if}}
              {{if $dossier_perinatal->pere_ant_herpes}}
                <li>
                    {{mb_label object=$dossier_perinatal field=pere_ant_herpes}}
                  : {{mb_value object=$dossier_perinatal field=pere_ant_herpes}}
                </li>
              {{/if}}
              {{if $dossier_perinatal->pere_ant_autre}}
                <li>
                    {{mb_label object=$dossier_perinatal field=pere_ant_autre}}
                  : {{mb_value object=$dossier_perinatal field=pere_ant_autre}}
                </li>
              {{/if}}
          </ul>
        {{/if}}

        {{if $father_atcd.counter > 0}}
          <strong>{{tr}}CAntecedent.type.fam{{/tr}}</strong>
          <ul>
              {{foreach from=$father_atcd.antecedents item=_field}}
                  {{if $dossier_perinatal->$_field}}
                    <li>{{tr}}CDossierPerinat-{{$_field}}{{/tr}}</li>
                  {{/if}}
              {{/foreach}}
          </ul>
        {{elseif ($father_atcd.counter == 0) && !$father_constantes->_id && !$father_dossier_medical->_id}}
          <ul>
            <li style="font-weight: bold;">{{tr}}CAntecedent-No antecedent provided{{/tr}}</li>
          </ul>
        {{/if}}
    </td>
  </tr>
</table>
