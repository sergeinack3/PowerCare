{{*
* @package Mediboard\Provenance
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=provenance script=provenance}}

<div id="listProvenances">
  <button type="button" class="new me-primary me-margin-top-4" onclick="Provenance.edit(0)">
    {{tr}}CProvenance-title-create{{/tr}}
  </button>
  <table class="tbl">
    <tr>
      <th class="title" colspan="3">
        {{tr}}CProvenance.all{{/tr}}
      </th>
    </tr>
    <tr class="category">
      <th>
        {{mb_colonne class="CProvenance" field="libelle" order_col=$order_col order_way=$order_way function="Provenance.provSortBy"}}
      </th>
      <th>
        {{tr}}CProvenance-desc{{/tr}}
      </th>
      <th class="narrow">
        {{mb_colonne class="CProvenance" field="actif" order_col=$order_col order_way=$order_way function="Provenance.provSortBy"}}
      </th>
    </tr>
    {{foreach from=$provenances item=_prov}}
      <tr>
        <td>
          <a href="#" onclick="Provenance.edit({{$_prov->_id}})">
            {{mb_value object=$_prov field=libelle}}
          </a>
        </td>
        <td>{{mb_value object=$_prov field=desc }}</td>
        <td>{{mb_value object=$_prov field=actif }}</td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="3">
          {{tr}}CProvenance.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  </table>
</div>
