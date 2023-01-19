{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="out-of-grid droppable">
  <script type="text/javascript">
  Main.add(function(){
    Control.Tabs.create("class-message-layout-tabs-{{$_group_id}}", null, {afterChange: function(container) {
      if (container.id = "outofgrid-hostfields-{{$_group_id}}") {
        $('forms-hostfields-quicksearch-{{$_group_id}}').focus();
      }
    }});
  });
  </script>
  
  <ul class="control_tabs me-margin-top-0" id="class-message-layout-tabs-{{$_group_id}}">
    {{if !$ex_class->pixel_positionning}}
    <li>
      <a href="#outofgrid-class-fields-{{$_group_id}}">Champs</a>
    </li>
    {{/if}}
    <li>
      <a href="#outofgrid-messages-{{$_group_id}}">Textes / Messages</a>
    </li>
    <li>
      <a href="#outofgrid-hostfields-{{$_group_id}}">Champs de Mediboard</a>
    </li>
  </ul>
  
  <!-- Fields -->
  {{if !$ex_class->pixel_positionning}}
  <div id="outofgrid-class-fields-{{$_group_id}}" class="me-padding-0" style="display: none;">
    <table class="main tbl me-no-align me-no-box-shadow" style="table-layout: fixed;">
      <tr>
        <th>Libellés</th>
        <th>Valeurs</th>
      </tr>
    </table>
    
    <table class="main layout" style="table-layout: fixed;">
      <tr>
        <td class="label-list" data-x="" data-y="" style="padding: 4px; height: 2em; vertical-align: top;">
          {{foreach from=$out_of_grid.$_group_id.label item=_field}}
            {{if !$_field->disabled && !$_field->hidden}}
              {{mb_include module=forms template=inc_ex_field_draggable _type="label"}}
            {{/if}}
          {{/foreach}}
        </td>
    
        <td class="field-list" data-x="" data-y="" style="padding: 4px; vertical-align: top;">
          {{foreach from=$out_of_grid.$_group_id.field item=_field}}
            {{if !$_field->disabled && !$_field->hidden}}
              {{mb_include module=forms template=inc_ex_field_draggable _type="field"}}
            {{/if}}
          {{/foreach}}
        </td>
      </tr>
    </table>
  </div>
  {{/if}}
  
  <!-- Messages -->
  <div id="outofgrid-messages-{{$_group_id}}" style="display: none;">
    <table class="main tbl" style="table-layout: fixed;">
      <tr>
        <th>Titres des messages (pas nécessaire de les placer)</th>
        <th>Messages</th>
      </tr>
    </table>
    
    <table class="main layout" style="table-layout: fixed;">
      <tr>
        <td class="message_title-list" data-x="" data-y="" style="padding: 4px; vertical-align: top;">
          {{foreach from=$out_of_grid.$_group_id.message_title item=_field}}
            {{mb_include module=forms template=inc_ex_message_draggable _type="message_title"}}
          {{/foreach}}
        </td>
        <td class="message_text-list" data-x="" data-y="" style="padding: 4px; vertical-align: top;">
          {{foreach from=$out_of_grid.$_group_id.message_text item=_field}}
            {{mb_include module=forms template=inc_ex_message_draggable _type="message_text"}}
          {{/foreach}}
        </td>
      </tr>
    </table>
  </div>
  
  <!-- Host fields -->
  <div id="outofgrid-hostfields-{{$_group_id}}" style="display: none;">
    {{mb_include module=forms template=inc_outofgrid_hostfields}}
  </div>
</div>