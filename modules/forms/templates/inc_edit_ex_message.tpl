{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
Main.add(function(){
  var form = getForm("editMessage");
  form.elements.text.select();
  ExFieldPredicate.initAutocomplete(form, '{{$ex_message->_ref_ex_group->ex_class_id}}');
});
</script>

{{if !$ex_message->_ref_ex_group->_ref_ex_class->pixel_positionning}}
<div class="small-info">
  Les Titres/Textes sont des zones d'information à placer sur la grille (Disposition du formulaire).<br />
  <strong>Le libellé n'a pas nécessairement besoin d'être placé sur la grille</strong>.
</div>
{{/if}}

<form name="editMessage" method="post" action="?" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="dosql" value="do_ex_class_message_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="callback" value="ExMessage.editCallback" />
  {{mb_key object=$ex_message}}
  {{mb_field object=$ex_message field=ex_group_id hidden=true}}
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$ex_message colspan="4"}}
    
    <tr>
      <th>{{mb_label object=$ex_message field=type}}</th>
      <td>{{mb_field object=$ex_message field=type emptyLabel="Normal" onchange="\$('text-preview').className='small-'+\$V(this)"}}</td>

      <th>{{mb_label object=$ex_message field=title}}</th>
      <td>{{mb_field object=$ex_message field=title}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$ex_message field=tab_index}}</th>
      <td colspan="3">{{mb_field object=$ex_message field=tab_index form="editMessage" increment=true}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$ex_message field=text}}</th>
      <td>
        <div id="text-preview" class="small-{{$ex_message->type}}">
          {{mb_field object=$ex_message field=text}}
        </div>
      </td>
      <th>{{mb_label object=$ex_message field=description}}</th>
      <td>
        {{mb_field object=$ex_message field=description}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$ex_message field=predicate_id}}</th>
      <td>
        <input type="text" name="predicate_id_autocomplete_view" size="70"
               value="{{$ex_message->_ref_predicate->_view}}" placeholder=" -- Toujours afficher -- " />
        {{mb_field object=$ex_message field=predicate_id hidden=true}}
        <button class="new notext" onclick="ExFieldPredicate.create(null, null, this.form)" type="button">
          {{tr}}New{{/tr}}
        </button>
      </td>

      <td colspan="2">
        {{if $ex_message->_id}}
          <table class="main layout" style="table-layout: fixed; width: 1%;">
            {{mb_include module=dPsante400 template=inc_widget_list_hypertext_links object=$ex_message show_separator=false}}
          </table>
        {{/if}}
      </td>
    </tr>
    
    {{if $ex_message->_ref_ex_group->_ref_ex_class->pixel_positionning}}
    <tr>
      <th class="narrow">{{mb_label object=$ex_message field=coord_left}}</th>
      <td class="narrow">{{mb_field object=$ex_message field=coord_left increment=true form=editMessage}}</td>
      <th class="narrow">{{mb_label object=$ex_message field=coord_top}}</th>
      <td>{{mb_field object=$ex_message field=coord_top increment=true form=editMessage}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$ex_message field=coord_width}}</th>
      <td>{{mb_field object=$ex_message field=coord_width increment=true form=editMessage}}</td>
      <th>{{mb_label object=$ex_message field=coord_height}}</th>
      <td>{{mb_field object=$ex_message field=coord_height increment=true form=editMessage}}</td>
    </tr>
    {{/if}}
    
    <tr>
      <th></th>
      <td colspan="3">
        <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>

        {{if $ex_message->_id}}
          <button type="button" class="trash" onclick="confirmDeletion(this.form,{ajax:true,typeName:'le message ',objName:'{{$ex_message->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

{{mb_include module=forms template=inc_list_entity_properties object=$ex_message}}
