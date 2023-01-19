{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_context value=0}}

{{assign var=pdf_and_thumbs value=$app->user_prefs.pdf_and_thumbs}}

{{foreach from=$list item=docitems_by_cat key=cat}}
  <div class="compact">
    {{if $cat != ""}}
      {{$cat}}
    {{else}}
      {{tr}}CFilesCategory.none{{/tr}}
    {{/if}}
  </div>
  <ul>
    {{foreach from=$docitems_by_cat item=_docitem}}
      <li>
        {{if $_docitem|instanceof:'Ox\Mediboard\CompteRendu\CCompteRendu'}}
        <button type="button" class="print notext me-tertiary"
                onclick="{{if $pdf_and_thumbs}}
                Document.printPDF({{$_docitem->_id}}, {{if $_docitem->signature_mandatory}}1{{else}}0{{/if}}, {{if $_docitem->valide}}1{{else}}0{{/if}});
                {{else}}
                Document.print({{$_docitem->_id}});
                {{/if}}">{{tr}}Print{{/tr}}</button>
          <a href="#document-{{$_docitem->_id}}" style="display: inline;"
             onclick="Document.edit('{{$_docitem->_id}}')">
              {{$_docitem->nom}}
          </a>
        {{else}}
          <a href="#document-{{$_docitem->_id}}" style="display: inline;"
               onclick="return popFile('{{$_docitem->object_class}}','{{$_docitem->object_id}}','{{$_docitem->_class}}','{{$_docitem->_id}}')">
            {{$_docitem->file_name}}
          </a>
        {{/if}}

        {{if $show_context}}
          {{assign var=context value=$_docitem->_ref_object}}
          : <span onmouseover="ObjectTooltip.createEx(this, '{{$context->_guid}}')">
          {{if $context|instanceof:'Ox\Mediboard\Cabinet\CConsultation'}}
            Consultation du {{$context->_date|date_format:$conf.date}} - {{$context->_ref_chir}}
          {{else}}
            {{$context}}
          {{/if}}
        </span>
        {{/if}}
      </li>
    {{/foreach}}
  </ul>
{{foreachelse}}
  <div class="empty">
    {{tr}}None{{/tr}}
  </div>
{{/foreach}}
