{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $see == "checkbox"}}
  <script>
    Main.add(function(){
      EditDailyCheck.seeCommentaire(getForm('{{$name_form_checklist}}'), '{{$curr_type->_id}}', 'checkbox');
    });
  </script>
  <label>
    <input type="checkbox" name="_items[{{$curr_type->_id}}_use_comment]" value="texte"
           {{if $curr_type->_commentaire}}checked="checked"{{/if}}
           onclick="EditDailyCheck.seeCommentaire(this.form, '{{$curr_type->_id}}', 'checkbox')"/>
    {{tr}}CDailyCheckItem.checked.texte{{/tr}}
  </label>
{{/if}}

{{if $see == "commentaire"}}
  <div style="float: right;width: 100%;">
  <textarea name="_items[{{$curr_type->_id}}_commentaire]"
            onchange="EditDailyCheck.seeCommentaire(this.form, '{{$curr_type->_id}}', 'commentaire');EditDailyCheck.submitCheckList(this.form, true)"
  >{{$curr_type->_commentaire}}</textarea>
  </div>
{{/if}}