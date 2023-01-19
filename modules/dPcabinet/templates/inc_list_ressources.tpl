{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button type="button" class="new" onclick="Ressource.edit(null, '{{$function->_id}}');">{{tr}}CRessourceCab-new{{/tr}}</button>

<table class="tbl">
  <tr>
    <th class="title" colspan="3s">
      {{tr var1=$function->text}}CRessourceCab-list{{/tr}}
    </th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th></th>
    <th>{{mb_label class=CRessourceCab field=libelle}}</th>
  </tr>
  {{foreach from=$ressources item=_ressource}}
  <tr {{if !$_ressource->actif}}class="opacity-40"{{/if}}>
    <td>
      <button type="button" class="edit notext" onclick="Ressource.edit('{{$_ressource->_id}}');">{{tr}}Edit{{/tr}}</button>
    </td>
    <td class="narrow" style="background-color: #{{$_ressource->color}}"></td>
    <td>
      {{mb_value object=$_ressource field=libelle}}

      <div class="compact">
        {{mb_value object=$_ressource field=description}}
      </div>
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="3">
      {{tr}}CRessourceCab.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>