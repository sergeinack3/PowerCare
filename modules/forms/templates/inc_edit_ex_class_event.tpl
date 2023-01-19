{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    ExClassEvent.setEvent(getForm("editClassEvent")._event);

    {{if $ex_class_event->_id}}
        Control.Tabs.create('ex_class_event-tabs', true);
    {{/if}}
  });

  toggleConstraintsMessage = function(type, input) {
    var prefix = (type === 'mandatory') ? 'ex_class_event-mandatory-constraint-msg-' : 'ex_class_event-constraint-msg-';

    if ($V(input) === 'or') {
      $(prefix + 'or').show();
      $(prefix + 'and').hide();
    }
    else {
      $(prefix + 'or').hide();
      $(prefix + 'and').show();
    }
  };
</script>

<form name="editClassEvent" method="post" action="?" onsubmit="return onSubmitFormAjax(this, ExClass.edit.curry({{$ex_class_event->ex_class_id}}))">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="del" value="0" />
  {{mb_class object=$ex_class_event}}
  {{mb_key object=$ex_class_event}}
  
  {{mb_field object=$ex_class_event field=ex_class_id hidden=true}}
  {{mb_field object=$ex_class_event field=host_class hidden=true}}
  {{mb_field object=$ex_class_event field=event_name hidden=true}}
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$ex_class_event colspan="4"}}

    <tr>
      <th style="width: 100px;">{{mb_label object=$ex_class_event field=event_name}}</th>
      <td colspan="3">
        <select name="_event" class="notNull" onchange="ExClassEvent.setEvent(this)">
          <option value="" disabled="disabled" selected="selected"> &ndash; Choisir </option>
          {{foreach from=$classes item=_events key=_class}}
            <optgroup label="{{tr}}{{$_class}}{{/tr}}">
              {{foreach from=$_events item=_params key=_event_name}}
                <option value="{{$_class}}.{{$_event_name}}"
                        {{if @$_params.auto}} data-auto="true" {{/if}} 
                        {{if @$_params.tab}} data-tab="true" {{/if}}
                        {{if $_class == $ex_class_event->host_class && $_event_name == $ex_class_event->event_name}} selected {{/if}}
                        >

                  {{tr}}{{$_class}}{{/tr}} - {{tr}}{{$_class}}-event-{{$_event_name}}{{/tr}}
                  
                  {{if @$_params.auto}} (déclench. auto){{/if}}
                  
                  {{if @$_params.tab}} (affichage en volet){{/if}}

                    {{if 'reference1'|array_key_exists:$_params && 'reference2'|array_key_exists:$_params}}
                      (Lié également à {{tr}}{{$_params.reference1.0}}{{/tr}} et {{tr}}{{$_params.reference2.0}}{{/tr}})
                    {{/if}}
                </option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$ex_class_event field=disabled}}</th>
      <td colspan="3">{{mb_field object=$ex_class_event field=disabled typeEnum=checkbox}}</td>
    </tr>

    {{if $ex_class_event->_id}}
      <tr>
        <th>Lié à </th>
        <td>
          {{tr}}{{$ex_class_event->host_class}}{{/tr}}, {{tr}}{{$ex_class_event->_host_class_options.reference1.0}}{{/tr}}, {{tr}}{{$ex_class_event->_host_class_options.reference2.0}}{{/tr}}
        </td>
      </tr>
    {{/if}}
    
    <tr>
      <th>{{mb_label object=$ex_class_event field=unicity}}</th>
      <td colspan="3">{{mb_field object=$ex_class_event field=unicity typeEnum=radio}}</td>
    </tr>

    <tr>
      <th class="category" colspan="4">{{tr}}CExClassEvent-back-constraints{{/tr}}</th>
    </tr>

    <tr>
      <th>{{mb_label object=$ex_class_event field=constraints_logical_operator}}</th>
      <td>
        {{mb_field object=$ex_class_event field=constraints_logical_operator typeEnum=radio onchange="toggleConstraintsMessage('constraints', this)"}}
      </td>

      <td colspan="2">
        <div class="small-info" id="ex_class_event-constraint-msg-or" {{if $ex_class_event->constraints_logical_operator !== 'or'}} style="display: none;" {{/if}}>
          Le formulaire sera <strong>présenté</strong> s'il n'y a <strong>aucune contrainte</strong>, ou si <strong>au moins l'une des contraintes</strong> est satisfaite.
        </div>

        <div class="small-info" id="ex_class_event-constraint-msg-and" {{if $ex_class_event->constraints_logical_operator !== 'and'}} style="display: none;" {{/if}}>
          Le formulaire sera <strong>présenté</strong> s'il n'y a <strong>aucune contrainte</strong>, ou si <strong>toutes les contraintes</strong> sont satisfaites.
        </div>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$ex_class_event field=mandatory_constraints_logical_operator}}</th>
      <td>
        {{mb_field object=$ex_class_event field=mandatory_constraints_logical_operator typeEnum=radio onchange="toggleConstraintsMessage('mandatory', this)"}}
      </td>

      <td colspan="2">
        <div class="small-info" id="ex_class_event-mandatory-constraint-msg-or" {{if $ex_class_event->mandatory_constraints_logical_operator !== 'or'}} style="display: none;" {{/if}}>
          Le formulaire sera <strong>obligatoire</strong> si <strong>au moins l'une des contraintes</strong> est satisfaite.
        </div>

        <div class="small-info" id="ex_class_event-mandatory-constraint-msg-and" {{if $ex_class_event->mandatory_constraints_logical_operator !== 'and'}} style="display: none;" {{/if}}>
          Le formulaire sera <strong>obligatoire</strong> si <strong>toutes les contraintes</strong> sont satisfaites.
        </div>
      </td>
    </tr>

    <tbody {{if !$ex_class_event->_id}} style="display: none;" {{/if}} class="event-tab-inputs">
      <tr>
        <th class="category" colspan="4">Affichage en volet</th>
      </tr>
      <tr>
        <th>{{mb_label object=$ex_class_event field=tab_name}}</th>
        <td class="narrow">{{mb_field object=$ex_class_event field=tab_name}}</td>
        
        <th class="narrow">{{mb_label object=$ex_class_event field=tab_rank}}</th>
        <td>{{mb_field object=$ex_class_event field=tab_rank increment=true form="editClassEvent" size=2}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$ex_class_event field=tab_show_header}}</th>
        <td class="narrow" colspan="3">{{mb_field object=$ex_class_event field=tab_show_header typeEnum="checkbox"}}</td>
        
        {{*<th class="narrow">{{mb_label object=$ex_class_event field=tab_show_subtabs}}</th>*}}
        {{*<td>{{mb_field object=$ex_class_event field=tab_show_subtabs typeEnum="checkbox"}}</td>*}}
      </tr>
    </tbody>
      
    <tr>
      <th></th>
      <td colspan="3">
        <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>

        {{if $ex_class_event->_id}}
          <button type="button" class="trash" onclick="confirmDeletion(this.form,{ajax:true,typeName:'l\'évènement ',objName:'{{$ex_class_event->_view|smarty:nodefaults|JSAttribute}}'}, ExClass.edit.curry({{$ex_class_event->ex_class_id}}))">
            {{tr}}Delete{{/tr}}
          </button>
          <button type="button" class="search" onclick="ExObject.preview({{$ex_class_event->ex_class_id}}, '{{$ex_class_event->host_class}}-0')">
            {{tr}}Preview{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
  
{{if $ex_class_event->_id}}
  <ul class="control_tabs me-align-auto" id="ex_class_event-tabs">
    <li>
      <a href="#event-constraints">{{tr}}CExClassEvent-back-constraints{{/tr}}</a>
    </li>

    <li>
      <a href="#event-mandatory-constraints">{{tr}}CExClassEvent-back-mandatory_constraints{{/tr}}</a>
    </li>
  </ul>

  <div id="event-constraints" class="me-padding-0 me-align-auto" style="display: none;">
    {{mb_include module=forms template=inc_edit_class_constraints constraints=$ex_class_event->_ref_constraints}}
  </div>

  <div id="event-mandatory-constraints" class="me-align-auto" style="display: none;">
    {{mb_include module=forms template=inc_edit_class_mandatory_constraints constraints=$ex_class_event->_ref_mandatory_constraints}}
  </div>
{{/if}}
