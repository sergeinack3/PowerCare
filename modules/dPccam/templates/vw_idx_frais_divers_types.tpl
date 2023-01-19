{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td style="width: 60%">
      <a class="button new me-margin-4" style="float: right;" href="?m=dPccam&tab={{$tab}}&frais_divers_type_id=0">
        {{tr}}CFraisDiversType-title-create{{/tr}}
      </a>

      <table class="main tbl">
        <tr>
          <th>{{mb_title class=CFraisDiversType field=code}}</th>
          <th>{{mb_title class=CFraisDiversType field=libelle}}</th>
          <th>{{mb_title class=CFraisDiversType field=tarif}}</th>
          <th>{{mb_title class=CFraisDiversType field=facturable}}</th>
        </tr>

        {{foreach from=$list_types item=_type}}
          <tr>
            <td>
              <a href="?m=dPccam&tab={{$tab}}&frais_divers_type_id={{$_type->_id}}">
                {{mb_value object=$_type field=code}}
              </a>
            </td>
            <td>{{mb_value object=$_type field=libelle}}</td>
            <td>{{mb_value object=$_type field=tarif}}</td>
            <td>{{mb_value object=$_type field=facturable}}</td>
          </tr>
        {{foreachelse}}
          <tr>
            <td colspan="4" class="empty">{{tr}}CFraisDiversType.none{{/tr}}</td>
          </tr>
        {{/foreach}}

      </table>
    </td>
    <td>

      <form name="editFraisDiversType" method="post" action="?m=dPccam&tab={{$tab}}">
        {{mb_class object=$type}}
        {{mb_key object=$type}}
        <input type="hidden" name="del" value="0" />

        <table class="main form">
          {{mb_include module=system template=inc_form_table_header object=$type}}

          <tr>
            <th>{{mb_label object=$type field=code}}</th>
            <td>{{mb_field object=$type field=code size=5}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$type field=libelle}}</th>
            <td>{{mb_field object=$type field=libelle size=40}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$type field=tarif}}</th>
            <td>{{mb_field object=$type field=tarif increment=true form=editFraisDiversType}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$type field=facturable}}</th>
            <td>{{mb_field object=$type field=facturable typeEnum=checkbox}}</td>
          </tr>

          <tr>
            <td class="button" colspan="2">
            {{if $type->_id}}
              <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>
              <button type="button" class="trash" onclick="confirmDeletion(this.form, {typeName:'ce type'})">
                {{tr}}Delete{{/tr}}
              </button>
            {{else}}
              <button type="submit" class="submit">{{tr}}Create{{/tr}}</button>
            {{/if}}
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>
