{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{math equation=x-y-z x=$object->_nb_files_docs y=$object->_nb_cancelled_files z=$object->_nb_cancelled_docs assign=nb_files_docs}}

{{mb_default var=compact value=false}}

<button type="button" style="width: 3em; display: block" id="docItem_{{$object->_guid}}"
        class="rtl me-tertiary me-dark right{{if !$nb_files_docs}}-disabled{{/if}} droppable {{if $compact}}compact{{/if}}"
        onclick="setObject( {
          objClass: '{{$object->_class}}',
          keywords: '',
          id: {{$object->_id}},
          view: '{{$object->_view|smarty:nodefaults|JSAttribute}}' }); ViewFullPatient.select(this.up('tr').down('a'));"
        title="{{$nb_files_docs}} {{tr}}CDocumentItem{{/tr}}"
        data-guid="{{$object->_guid}}">
  {{$nb_files_docs}}</button>