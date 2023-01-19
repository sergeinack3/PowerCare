{{*
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editCommentaire" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: Control.Modal.close})">
  {{mb_class object=$commentaire}}
  {{mb_key   object=$commentaire}}
  <input type="hidden" name="del" value="0" />
  {{if $callback}}
    <input type="hidden" name="callback" value="{{$callback}}" />
  {{/if}}
  {{mb_field object=$commentaire field=salle_id hidden=true}}
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$commentaire duplicate=$clone show_notes=false}}

    <tr>
      <th>
        {{mb_label object=$commentaire field=debut}}
      </th>
      <td>
        {{mb_field object=$commentaire field=debut form=editCommentaire register=true}}
      </td>
    </tr>
    
    <tr>
      <th>
        {{mb_label object=$commentaire field=fin}}
      </th>
      <td>
        {{mb_field object=$commentaire field=fin form=editCommentaire register=true}}
      </td>
    </tr>
    
    <tr>
      <th>
        {{mb_label object=$commentaire field=color}}
      </th>
      <td>
        {{mb_field object=$commentaire field="color" form=editCommentaire}}
      </td>
    </tr>
    
    <tr>
      <th>
        {{mb_label object=$commentaire field=libelle}}
      </th>
      <td>
        {{mb_field object=$commentaire field=libelle form="editCommentaire"}}
      </td>
    </tr>
    
    <tr>
      <th>
        {{mb_label object=$commentaire field=commentaire}}
      </th>
      <td>
        {{mb_field object=$commentaire field=commentaire form="editCommentaire"}}

      </td>
    </tr>
    
    <tr>
      <td colspan="2" class="button">
        {{if !$commentaire->_id}}
          <button type="submit" class="save">{{tr}}Create{{/tr}}</button>
        {{else}}
          <button type="button" class="save" onclick="this.form.onsubmit()">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash"
            onclick="confirmDeletion(this.form, {
              typeName: 'le commentaire',
              objName: '{{$commentaire->libelle}}',
              ajax: true
              });Control.Modal.close();">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
