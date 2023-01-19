{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$list item=_doc_item}}
  <tr>
    <td class="{{cycle name=cellicon values="dark, light"}}">
      {{assign var="elementId" value=$_doc_item->_id}}
      {{if $_doc_item->_class=="CCompteRendu"}}
        {{if $app->user_prefs.pdf_and_thumbs}}
          {{assign var="srcImg" value="?m=files&raw=thumbnail&document_guid=`$_doc_item->_class`-`$_doc_item->_id`&profile=medium"}}
        {{else}}
          {{assign var="srcImg" value="images/pictures/medifile.png"}}
        {{/if}}
      {{else}}
        {{assign var="srcImg" value="?m=files&raw=thumbnail&document_guid=CFile-$elementId&profile=medium"}}
      {{/if}}
      
      <a href="#" onclick="ZoomAjax('{{$object->_class}}', '{{$object->_id}}', '{{$_doc_item->_class}}', '{{$elementId}}', '0');" title="Afficher l'aperçu">
        <img src="{{$srcImg}}" alt="-" width="64"/>
      </a>

    </td>
    <td class="text {{cycle name=celltxt values="dark, light"}}" style="vertical-align: middle;">
      <strong>
        {{if $_doc_item|instanceof:'Ox\Mediboard\CompteRendu\CCompteRendu' && $_doc_item->valide}}
          <i class="me-icon lock me-primary"  onmouseover="ObjectTooltip.createEx(this, '{{$_doc_item->_guid}}', 'locker')"></i>
        {{/if}}
        {{$_doc_item}}
        {{if $_doc_item->private}}
          &mdash; <em>{{tr}}CCompteRendu-private{{/tr}}</em>
        {{/if}}
      </strong>
      <hr class="me-border-bottom-width-1"/>
      {{mb_include module=files template=inc_file_toolbar notext=notext}}
    </td>
  </tr>
{{foreachelse}}
<tr>
  <td colspan="2" class="empty">
    {{tr}}CFile.none{{/tr}}
  </td>
</tr>
{{/foreach}}
