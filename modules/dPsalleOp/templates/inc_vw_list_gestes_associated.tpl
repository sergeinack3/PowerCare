{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form action="?" method="post" name="chooseGesteFromCategory">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="dosql" value="do_geste_perop_multi_from_category" />
  <input type="hidden" name="_geste_perop_ids" value=""/>
  <input type="hidden" name="protocole_geste_perop_id" value="{{$protocole_geste_perop_id}}"/>

  <table class="main tbl">
    <tbody>
    {{foreach from=$evenement_categorie->_ref_gestes_perop item=_geste}}
      <tr>
        {{if !$show_only}}
          <td>
            <input class="geste_selected" type="checkbox" name="geste_{{$_geste->_id}}" value="{{$_geste->_id}}"/>
          </td>
        {{/if}}
        <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_geste->_guid}}')">
           {{mb_value object=$_geste field=libelle}}
        </span>
        </td>
        <td class="text">
          {{mb_value object=$_geste field=description}}
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="3" class="empty">
          {{tr}}CGestePerop.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}

    {{if !$show_only}}
      <tr>
        <td class="button" colspan="3">
          <button type="button" class="save" title=""
                  onclick="GestePerop.AddProtocoleItemGestePeropFromCategory(this.form);">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    {{/if}}
    </tbody>
    <thead>
    <tr>
      <th class="title" colspan="3">
        {{tr var1=$evenement_categorie->_view}}CAnesthPeropCategorie-The perop gestures associated with the category %s{{/tr}}
        ({{$evenement_categorie->_ref_gestes_perop|@count}})
      </th>
    </tr>
    <tr>
      {{if !$show_only}}
        <th class="narrow">
          <input class="" type="checkbox" name="all_gestes" onclick="GestePerop.selectAllLineItems(this);"/>
        </th>
      {{/if}}
      <th class="narrow">{{mb_label class=CGestePerop field=libelle}}</th>
      <th class="text">{{mb_label class=CGestePerop field=description}}</th>
    </tr>
    </thead>
  </table>
</form>
