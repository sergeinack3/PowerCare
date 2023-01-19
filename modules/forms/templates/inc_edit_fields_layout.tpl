{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=grid_colspan value=$ex_class->getGridWidth()+1}}

<script type="text/javascript">
Main.add(function(){
  Control.Tabs.create("field_groups_layout");
  //ExClass.putCellSpans($$(".drop-grid")[0]);
});

toggleList = function(select, ex_group_id) {
  // Quick search reset
  $$(".hostfield-list-"+ex_group_id).each(function(e) {
    e.select('li').invoke('show');
  });

  var input = $('forms-hostfields-quicksearch-'+ex_group_id);
  input.value = '';
  input.onkeyup();

  $$(".hostfield-list-"+ex_group_id).invoke("hide");
  $$(".hostfield-"+ex_group_id+"-"+$V(select))[0].setStyle({display: "inline-block"});
}
</script>

<form name="form-layout-field" method="post" action="" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="dosql" value="do_ex_class_field_aed" />
  <input type="hidden" name="ex_class_field_id" value="" />
  
  <input type="hidden" name="coord_label_x" class="coord" value="" />
  <input type="hidden" name="coord_label_y" class="coord" value="" />
  <input type="hidden" name="coord_field_x" class="coord" value="" />
  <input type="hidden" name="coord_field_y" class="coord" value="" />
</form>

<form name="form-layout-message" method="post" action="" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="dosql" value="do_ex_class_message_aed" />
  <input type="hidden" name="ex_class_message_id" value="" />
  
  <input type="hidden" name="coord_title_x" class="coord" value="" />
  <input type="hidden" name="coord_title_y" class="coord" value="" />
  <input type="hidden" name="coord_text_x" class="coord" value="" />
  <input type="hidden" name="coord_text_y" class="coord" value="" />
</form>

<form name="form-layout-hostfield" method="post" action="" onsubmit="return false">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="dosql" value="do_ex_class_host_field_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="ex_class_host_field_id" value="" />
  <input type="hidden" name="ex_class_id" value="{{$ex_class->_id}}" />
  <input type="hidden" name="ex_group_id" value="" />
  <input type="hidden" name="host_class" value="" />
  <input type="hidden" name="field" value="" />
  <input type="hidden" name="callback" value="" />
  
  <input type="hidden" name="coord_label_x" class="coord" value="" />
  <input type="hidden" name="coord_label_y" class="coord" value="" />
  <input type="hidden" name="coord_value_x" class="coord" value="" />
  <input type="hidden" name="coord_value_y" class="coord" value="" />
</form>

<ul class="control_tabs me-margin-top-0" id="field_groups_layout" style="font-size: 0.9em;">
  {{foreach from=$ex_class->_ref_groups item=_group}}
    <li>
      <a href="#group-layout-{{$_group->_guid}}" style="padding: 2px 4px;">
        {{$_group->name}} <small>({{$_group->_ref_fields|@count}})</small>
      </a>
    </li>
  {{/foreach}}
  <li style="font-size: 1.2em; font-weight: bold;">
    <label title="Plutôt que glisser-déposer">
      <input type="checkbox" onclick="ExClass.setPickMode(this.checked)" checked="checked" />
      Disposer par clic
    </label>
  </li>
</ul>

{{assign var=groups value=$ex_class->_ref_groups}}

<form name="form-grid-layout" method="post" onsubmit="return false" class="prepared pickmode">
  
{{foreach from=$grid key=_group_id item=_grid}}

<div id="group-layout-{{$groups.$_group_id->_guid}}" style="display: none;" class="group-layout me-padding-0 me-no-border">
  
{{mb_include module=forms template=inc_out_of_grid_elements}}

<table class="main drop-grid" style="border-collapse: collapse;">
  <tr>
    <th colspan="{{$grid_colspan}}" class="title">Disposition</th>
  </tr>
  <tr>
    <th class="me-bg-elevation-2" style="background: #ddd;"></th>
    {{foreach from=$_grid|@first key=_x item=_field}}
      <th class="me-bg-elevation-2" style="background: #ddd;">{{$_x}}</th>
    {{/foreach}}  
  </tr>
  
  {{foreach from=$_grid key=_y item=_line}}
  <tr>
    <th class="me-bg-elevation-2" style="padding: 4px; width: 2em; text-align: right; background: #ddd;">{{$_y}}</th>
    {{foreach from=$_line key=_x item=_group}}
      <td style="border: 1px dotted #aaa; min-width: 2em; padding: 0; vertical-align: middle;" class="cell">
        <div class="droppable grid" data-x="{{$_x}}" data-y="{{$_y}}">
          {{if $_group.object}}
            {{if $_group.object|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}
              {{if $_group.object->disabled || $_group.object->hidden}}
                &nbsp;
              {{else}}
                {{mb_include module=forms template=inc_ex_field_draggable
                             _field=$_group.object
                             _type=$_group.type}}
              {{/if}}
            {{elseif $_group.object|instanceof:'Ox\Mediboard\System\Forms\CExClassHostField'}}
              {{assign var=_host_field value=$_group.object}}
              {{assign var=_host_class value=$_host_field->host_class}}
              {{assign var=_host_object value=$ex_class->_host_objects.$_host_class}}
            
              {{mb_include module=forms template=inc_ex_host_field_draggable 
                           _host_field=$_group.object 
                           ex_group_id=$_group_id 
                           _field=$_group.object->field 
                           trad=true
                           _type=$_group.type
                          _class=$_host_class}}
            {{else}}
              {{mb_include module=forms template=inc_ex_message_draggable 
                           _field=$_group.object 
                           ex_group_id=$_group_id 
                           _type=$_group.type}}
            {{/if}}
          {{else}}
            &nbsp;
          {{/if}}
        </div>
      </td>
    {{/foreach}}
  </tr>
  {{/foreach}}
</table>

</div>

{{/foreach}}

</form>
