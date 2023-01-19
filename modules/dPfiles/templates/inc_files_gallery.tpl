{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  document.title += " - {{$object}}";
</script>

<table class="main form">
  {{foreach from=$files item=_files_by_cat}}
    {{if $_files_by_cat.items|@count}}
      <tr>
        <th class="category" colspan="2">
          {{$_files_by_cat.name}}
        </th>
      </tr>
      {{assign var=i value=0}}
      {{foreach from=$_files_by_cat.items item=_file}}
        {{assign var=element_id value=$_file->_id}}
        {{assign var=element_class value=$_file->_class}}
        {{if $_file|instanceof:'Ox\Mediboard\CompteRendu\CCompteRendu'}}
          {{assign var=cr_name value=$_file->nom}}
        {{/if}}
        {{if $i == 0}}
          <tr>
        {{/if}}
        <td style="padding: 1em; text-align: center; width: 50%; vertical-align: middle;">
          <a style="text-decoration: none;"
            href="?m=files&dialog=preview_files&popup=1&elementClass={{$element_class}}&elementId={{$element_id}}&objectId={{$object->_id}}&objectClass={{$object->_class}}">
            {{if $element_class == "CCompteRendu"}}
              <img style="border: 1px solid #000;" src="images/pictures/medifile.png" />
            {{else}}
              {{thumbnail document=$_file profile=large style="width: 350; border: 1px solid #000;"}}
            {{/if}}
          </a>
          <br />
          {{if $element_class == "CCompteRendu"}}
            {{$cr_name}}
          {{else}}
            {{$_file->file_name}}
          {{/if}}
        </td>
        {{if $i == 1}}
          </tr>
        {{/if}}
        {{math equation="x+1" x=$i assign=i}}
        {{if $i == 2}}
          {{assign var=i value=0}}
        {{/if}}
      {{foreachelse}}
        <td colspan="2" class="empty">
         {{tr}}CFile.none{{/tr}}
        </td>
      {{/foreach}}
    {{/if}}
  {{/foreach}}
</table>

