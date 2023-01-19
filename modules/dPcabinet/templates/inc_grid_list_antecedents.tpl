{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=title_class    value="title"}}
{{mb_default var=table_display  value="none"}}
{{mb_default var=appareil_count value=false}}

{{foreach from=$aides_antecedent.$type item=_aides key=appareil}}
  <table id="{{$type}}-{{$appareil}}" style="display: {{$table_display}}; width: 100%" class="tbl me-no-align me-no-border">
    <tr>
      <th colspan="1000" class="{{$title_class}}">
        {{tr}}CAntecedent.appareil.{{$appareil}}{{/tr}}
        {{if $appareil_count}}
          <small>({{$antecedent->_count_rques_aides_appareil.$type.$appareil}})</small>
        {{/if}}
      </th>
    </tr>
    <tr id="textarea-ant-{{$type}}-{{$appareil}}">
      <td colspan="1000">
        <form name="addAnt-{{$type}}-{{$appareil}}" method="post" onsubmit="
          $V(oFormAntFrmGrid._patient_id, '{{$patient->_id}}');
          $V(oFormAntFrmGrid.type, '{{$type}}');
          $V(oFormAntFrmGrid.appareil, '{{$appareil}}');
          $V(oFormAntFrmGrid.rques, this.antecedent.value);
          $V(this.antecedent, '');
          return onSubmitFormAjax(this);"
        >
          <input name="antecedent" size="60"/>
          <button class="submit">
            Ajouter l'antécédent
          </button>
        </form>
      </td>
    </tr>
    {{foreach from=$_aides item=aides_by_line}}
      <tr>
        {{foreach from=$aides_by_line item=curr_aide}}
          {{if $curr_aide|instanceof:'Ox\Mediboard\CompteRendu\CAideSaisie'}}
            {{assign var=owner_icon value="group"}}
            {{if $curr_aide->_owner == "user"}}
              {{assign var=owner_icon value="user"}}
            {{elseif $curr_aide->_owner == "func"}}
              {{assign var=owner_icon value="function"}}
            {{/if}}
            {{assign var=text value=$curr_aide->text}}
            {{assign var=checked value=$curr_aide->_applied}}
            <td class="text {{if $checked}}opacity-30{{/if}} {{$owner_icon}}"
                style="cursor: pointer; width: {{$width}}%; {{if $checked}}cursor: default;{{/if}}">
              <label onmouseover="ObjectTooltip.createDOM(this, 'tooltip_{{$curr_aide->_guid}}')">
                <input type="checkbox" {{if $checked}}checked disabled{{/if}} id="aide_{{$curr_aide->_guid}}"
                       onclick="
                         {{if "dPcabinet CConsultation complete_atcd_mode_grille"|gconf}}
                           $('tooltip_{{$curr_aide->_guid}}').down('button').click();
                         {{else}}
                           addAntecedent(arguments[0] || window.event, '{{$curr_aide->text|smarty:nodefaults|JSAttribute}}', '', '{{$type}}', '{{$appareil}}', this)
                         {{/if}}" />

                {{if $show_text_complet}}
                  {{$curr_aide->text}}
                {{else}}
                  {{$curr_aide->name}}
                {{/if}}
              </label>
              <div style="display: none" id="tooltip_{{$curr_aide->_guid}}">
                <table class="tbl">
                  <tr>
                    <th>
                      {{$curr_aide->text}}
                    </th>
                  </tr>
                  <tr>
                    <td class="button">
                      <button type="button" class="edit"
                              onclick="var event = {ctrlKey: true}; addAntecedent(event, '{{$curr_aide->text|smarty:nodefaults|JSAttribute}}', '', '{{$type}}', '{{$appareil}}', $('aide_{{$curr_aide->_guid}}'))">Compléter</button>
                    </td>
                  </tr>
                </table>
              </div>
            </td>
          {{/if}}
        {{/foreach}}
      </tr>
    {{/foreach}}
  </table>
{{/foreach}}
