{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button type="button" onclick="Indispo.editIndispo(''); updateSelected('list_indispos')" class="new">
  {{tr}}CIndispoRessource-create{{/tr}}
</button>

<table class="tbl">
  <tr>
    <th class="title">
      <a style="display: inline" href="#1" onclick="Indispo.refreshListIndispos('', '{{$prev_month}}')">&lt;&lt;&lt;</a>
      {{$date_indispo|date_format:"%b %G"}}
      <form name="changeDateList" class="prepared" method="get">
      <input type="hidden" name="date" value="{{$date_indispo}}" onchange="Blocage.refreshList('', this.value)"/>
      </form>
      <a style="display: inline" href="#1" onclick="Indispo.refreshListIndispos('', '{{$next_month}}')">&gt;&gt;&gt;</a>
    </th>
  </tr>
  <tr>
  {{foreach from=$types_ressources item=_type_ressource key=type_ressource_id}}
    <tr>
      <th class="category">
        {{$_type_ressource->libelle}}
      </th>
    </tr>
    {{foreach from=$ressources.$type_ressource_id item=_ressource key=ressource_id}}
      <tr>
        <th class="section">
          {{$_ressource->libelle}}
        </th>
      </tr>
      {{foreach from=$indispos.$ressource_id item=_indispo}}
        <tr {{if $_indispo->_id == $indispo_ressource_id}}class="selected"{{/if}}>
          <td>
            <a href="#1" onclick="Indispo.editIndispo('{{$_indispo->_id}}'); updateSelected('list_indispos', this.up('tr'))">
              {{mb_include module=system template=inc_interval_datetime from=$_indispo->deb to=$_indispo->fin}}
            </a>
            <div class="compact">
              {{mb_value object=$_indispo field=commentaire}}
            </div>
          </td>
        </tr>
      {{foreachelse}}
        <tr>
          <td class="empty">
            {{tr}}CIndispoRessource.none{{/tr}}
          </td>
        </tr>
      {{/foreach}}
    {{foreachelse}}
      <tr>
        <td class="empty">
          {{tr}}CRessourceMaterielle.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  {{foreachelse}}
    <tr>
      <td class="empty">
        {{tr}}CTypeRessource.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>