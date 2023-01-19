{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $lines|@count}}
  <script>
    editForm = getForm("editLine");
    addForm = getForm('addLineSSR');

    removeLineSSR = function(line_id){
      $V(editForm.del, '1');
      $V(editForm.prescription_line_element_id, line_id);
      return onSubmitFormAjax(editForm);
    };

    stopLineSSR = function(line_id){
      $V(editForm.del, '0');
      $V(editForm.prescription_line_element_id, line_id);
      $V(editForm.date_arret, '{{$current_date}}');
      return onSubmitFormAjax(editForm);
    };

    resetField = function(){
      $V(addForm.debut, '');
      $V(editForm.date_arret, '');
    };

    submitAndClose = function(){
      return onSubmitFormAjax(addForm, { onComplete: function() {
        updateListLines();
        resetField();
        modalWindow.close();
      } } );
    }
  </script>
  {{if $warning}}
    {{tr}}CPrescriptionLine-after-already_present{{/tr}} :
    <br />
    <strong style="padding-left: 2em;">{{$element}}</strong>
    <br />
    <div class="button">
      <button onclick="stopLineSSR('{{$last_line->_id}}'); $V(addForm.debut, '{{$current_date}}'); submitAndClose();"
              class="tick">
        {{tr}}srr-add_after{{/tr}}
      </button>
      <button onclick="modalWindow.close();" class="cancel">{{tr}}Cancel{{/tr}}</button>
    </div>
  {{else}}
    {{tr}}CPrescriptionLine-msg_before_add{{/tr}}:
    <br />
    <strong style="padding-left: 2em;">{{$element}}</strong>
    <br />
    <div class="button">
      <button onclick="removeLineSSR('{{$last_line->_id}}'); submitAndClose();" class="tick">
        {{tr}}srr-replace_all_lines{{/tr}}
      </button>
      <button onclick="stopLineSSR('{{$last_line->_id}}'); $V(addForm.debut, '{{$current_date}}'); submitAndClose();"
              class="tick">
        {{tr}}srr-add_after{{/tr}}
      </button>
      <button onclick="modalWindow.close();" class="cancel">{{tr}}Cancel{{/tr}}</button>
    </div>
  {{/if}}
{{/if}}