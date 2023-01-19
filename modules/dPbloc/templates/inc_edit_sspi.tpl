{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td class="halfPane">
      <form name="sspi-edit" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this)">
        {{mb_class object=$sspi}}
        {{mb_key   object=$sspi}}
        <input type="hidden" name="group_id" value="{{$g}}" />
        <input type="hidden" name="callback" value="Bloc.afterEditSSPI" />
        <table class="form me-no-box-shadow me-no-align">
          <tr>
            {{mb_include module=system template=inc_form_table_header object=$sspi}}
          </tr>
          <tr>
            <th>{{mb_label object=$sspi field=libelle}}</th>
            <td>{{mb_field object=$sspi field=libelle}}</td>
          </tr>
          <tr>
            <td class="button" colspan="2">
              {{if $sspi->_id}}
                <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
                <button type="button" class="trash" onclick="confirmDeletion(this.form,{typeName:'',objName:'{{$sspi->libelle|smarty:nodefaults|JSAttribute}}', ajax: true})">
                  {{tr}}Delete{{/tr}}
                </button>
              {{else}}
                <button type="submit" class="new">{{tr}}Create{{/tr}}</button>
              {{/if}}
            </td>
          </tr>
        </table>
      </form>

      {{if $sspi->_id}}
        <table class="tbl">
          <tr>
            <th class="title" colspan="2">
              <form name="addBlocSSPI" method="post" style="float: left;"
                    onsubmit="return onSubmitFormAjax(this, Bloc.reloadLists.curry('{{$sspi->_id}}'));">
                {{mb_class class=CSSPILink}}
                <input type="hidden" name="sspi_id" value="{{$sspi->_id}}" />
                <select name="bloc_id">
                  {{foreach from=$blocs_list item=_bloc}}
                    <option value="{{$_bloc->_id}}">{{$_bloc->_view}}</option>
                  {{/foreach}}
                </select>
                <button type="button" class="add notext" onclick="this.form.onsubmit();">{{tr}}Add{{/tr}}</button>
              </form>
              {{tr}}CBlocOperatoire|pl{{/tr}}
            </th>
          </tr>
          <tr>
            <th colspan="2">{{mb_title class=CBlocOperatoire field=nom}}</th>
          </tr>

          <tbody id="sspi_bloc_{{$sspi->_id}}">
          {{mb_include module=bloc template=inc_list_sspi_blocs}}
          </tbody>
        </table>

        <br />

        <button type="button" class="new" onclick="Bloc.editPoste(null, '{{$sspi->_id}}');">{{tr}}CPosteSSPI-title-create{{/tr}}</button>

        <table class="tbl">
          <tr>
            <th class="title" colspan="2">{{tr}}CSSPI-back-postes_sspi{{/tr}}</th>
          </tr>
          <tr>
            <th>{{tr}}CPosteSSPI-nom{{/tr}}</th>
            <th>{{tr}}CPosteSSPI-type{{/tr}}</th>
          </tr>
          <tbody id="sspi_postes_{{$sspi->_id}}">
          {{mb_include module=bloc template=inc_list_sspi_postes}}
          </tbody>
        </table>
      {{/if}}
    </td>
  </tr>
</table>