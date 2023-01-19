{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
editListItem = function(line) {
  var form = line.up('form');
  $V(form.ex_list_item_id, line.get('id'));
  $V(form.elements.name, line.get('name'));
  $V(form.elements.code, line.get('code'));
  var button = form.down('button');
  button.removeClassName('add').addClassName('save');
  form.down('button.cancel').setVisibility(true);
};

cancelEditListItem = function(form) {
  $V(form.ex_list_item_id, "");
  $V(form.elements.name, "");
  $V(form.elements.code, "");
  var button = form.down('button');
  button.removeClassName('save').addClassName('add');
  form.down('button.cancel').setVisibility(false);
};
  
Main.add(function(){
  var form = getForm("edit-{{$context->_guid}}");
  if (!form || !form.elements.coded) return;
  
  $A(form.elements.coded).each(function(e){
    e.observe("click", function(){
      getForm('CExListItem-create').select("table .code").invoke('setVisible', e.value == 1);
    });
  });
});
</script>

{{assign var=coded value=false}}

{{if $context|instanceof:'Ox\Mediboard\System\Forms\CExList' && $context->coded == 1}}
  {{assign var=coded value=true}}
{{/if}}

{{assign var=owner_field value=$context->getBackRefField()}}

<form name="CExListItem-create" method="post" action="?" 
      onsubmit="return onSubmitFormAjax(this, {onComplete: 
        {{if $context|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}
          (function(){ if(!$V(this.elements.ex_list_item_id)) { (function(){ $('save-to-take-effect').show(); }).delay(1); } ExField.edit('{{$context->_id}}') }).bind(this)
        {{else}} 
          MbObject.edit.curry('{{$context->_guid}}')
        {{/if}} })">
        
  {{mb_class class=CExListItem}}
  <input type="hidden" name="ex_list_item_id" value="" class="ref" />
  <input type="hidden" name="{{$owner_field}}" value="{{$context->_id}}" />
  
  <table class="main tbl me-no-box-shadow me-no-align">
    <tr>
      <th class="narrow"></th>
      <th class="narrow code" {{if !$coded}}style="display: none"{{/if}}>
        {{mb_title class=CExListItem field=code}}
      </th>
      <th>
        {{mb_title class=CExListItem field=name}}
      </th>
      
      {{if $context|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}
        <th class="narrow">Formulaire à déclencher</th>
      {{/if}}
      
      <th class="narrow"></th>
      
      {{if $context|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}
        <th class="narrow">
          Coché par<br />défaut
        </th>
      {{/if}}
    </tr>
    
    <tr>
      <td>
        <button class="add notext compact">{{tr}}Add{{/tr}}</button>
      </td>
      <td class="code" {{if !$coded}} style="display: none" {{/if}}>{{mb_field class=CExListItem field=code size=6}}</td>
      <td {{if $context|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}colspan="2"{{/if}}>
        {{mb_field class=CExListItem field=name style="width: 99%;"}}
      </td>
      <td>
        <button class="cancel notext compact" type="button" onclick="cancelEditListItem(this.form)" style="visibility: hidden;">
          {{tr}}Cancel{{/tr}}
        </button>
      </td>
      
      {{if $context|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}
        <td style="text-align: center;" title="Aucune valeur par défaut">
          {{mb_include module=forms template=inc_ex_list_default_value value=""}}
        </td>
      {{/if}}
    </tr>
    
    {{foreach from=$context->_back.list_items item=_item}}
      {{assign var=_item_id value=$_item->_id}}
      
      <tr data-id="{{$_item->_id}}" data-name="{{$_item->name}}" data-code="{{$_item->code}}">
        <td>
          <button class="edit notext compact" type="button" onclick="editListItem($(this).up('tr'))">
            {{tr}}Edit{{/tr}}
          </button>
        </td>
        <td class="code" {{if !$coded}} style="display: none" {{/if}}>{{mb_value object=$_item field=code}}</td>
        <td class="text">{{mb_value object=$_item field=name}}</td>
        
        {{if $context|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}
          {{mb_include module=forms template=inc_ex_field_triggers value=$_item->_id}}
        {{/if}}
        
        <td></td>
        
        {{if $context|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}
          <td style="text-align: center;">
            {{mb_include module=forms template=inc_ex_list_default_value value=$_item->_id}}
          </td>
        {{/if}}
      </tr>
    {{foreachelse}}
      <tr>
        <td class="empty" colspan="5">{{tr}}CExListItem.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</form>
