{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=hide_hatching value=false}}
{{mb_default var=show_object_class value=true}}
{{mb_default var=callback value="Prototype.emptyFunction"}}

{{* act = active *}}
{{assign var=nb_act_docs value=$object->_nb_docs-$object->_nb_cancelled_docs}}
{{assign var=nb_act_files value=$object->_nb_files-$object->_nb_cancelled_files}}

<table class="main layout">
  <tr>
    <td style="vertical-align: middle !important; {{if $hide_hatching}}background: none !important{{/if}};">
      {{if $nb_act_docs == 0 && $nb_act_files == 0 && $object->_nb_forms == 0}}
        <div class="empty">
          {{if $show_object_class}}
            {{tr}}{{$object->_class}}{{/tr}} :
          {{/if}}
          {{tr}}CEvenementPatient-msg-document-none{{/tr}}
        </div>
      {{else}}
        {{if $show_object_class}}
          {{tr}}{{$object->_class}}{{/tr}}
        {{/if}}
        <button type="button" class="search me-tertiary"
                onclick="DocumentV2.viewDocs('{{$patient_id}}', '{{$object->_id}}', '{{$object->_class}}', {{$callback}})">
          {{if $nb_act_docs > 0}}
            {{$nb_act_docs}} document{{if $nb_act_docs > 1}}s{{/if}}
          {{/if}}
          {{if $nb_act_files > 0}}
            {{if $nb_act_docs}}-{{/if}} {{$nb_act_files}} fichier{{if $nb_act_files > 1}}s{{/if}}
          {{/if}}
          {{if $object->_nb_forms}}
            {{if $nb_act_docs || $nb_act_files}}-{{/if}} {{$object->_nb_forms}} formulaire{{if $object->_nb_forms > 1}}s{{/if}}
          {{/if}}
        </button>
      {{/if}}
    </td>
  </tr>
</table>
