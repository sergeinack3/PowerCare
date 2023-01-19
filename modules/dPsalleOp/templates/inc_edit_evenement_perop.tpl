{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=salleOp script=geste_perop ajax=true}}

<script>
  Main.add(function () {
    GestePerop.showCIMs10('{{$operation->_ref_sejour->grossesse_id}}');

    var form = getForm("edit-evenement-{{$evenement->_guid}}");
    GestePerop.incidentAntecedent(form);

    var url = new Url("dPsalleOp", "ajax_gestes_perop_autocomplete");
    url.addParam("edit", "1");
    url.addParam("input_field", "geste_perop_id_view");
    url.addParam("view_field", "libelle");
    url.autoComplete(form.geste_perop_id_view, null, {
      minChars:           0,
      method:             "get",
      select:             "view",
      dropdown:           true,
      afterUpdateElement: function (field, selected) {
        var id = selected.getAttribute("id").split("-")[2];
        var desc = selected.getAttribute("data-desc");
        var categorie_id = selected.getAttribute("data-categorie_id");
        var incident = selected.getAttribute("data-incident");
        var antecedent_code_cim = selected.getAttribute("data-antecedent_code_cim");
        var view = selected.innerHTML.trim();

        $V(form.geste_perop_id, id);
        $V(form.libelle, desc);
        $V(form.categorie_id, categorie_id);
        $V(form.geste_perop_id_view, view);
        $V(form.incident, incident);

        GestePerop.showGestePrecisions(id);

        if (antecedent_code_cim) {
          $('do_antecedent').show();
          form.antecedent.checked = true;
          setTimeout(function () {$$('input[name=codecim][value="'+ antecedent_code_cim +'"]')[1].checked = true;}, 700);
        }
        else {
          $V(form.antecedent, 0);
          $('do_antecedent').hide();
        }
      }
    });

    {{if $limit_date_min}}
      Calendar.regField(form.datetime, {limit:
          {
            start: '{{$limit_date_min}}'
          }
      });
    {{/if}}
  });
</script>

  <form name="edit-evenement-{{$evenement->_guid}}" method="post" action="?">
    <input type="hidden" name="m" value="dPsalleOp" />
    {{mb_class object=$evenement}}
    {{mb_key object=$evenement}}
    {{mb_field object=$evenement field=operation_id hidden=true}}
    {{mb_field object=$evenement field=user_id      value=$app->_ref_user->_id hidden=true}}

    {{if $evenement->_id}}
      {{mb_field object=$evenement field=libelle hidden=true}}
    {{/if}}

    <table class="main form">
      {{mb_include module=system template=inc_form_table_header object=$evenement}}

      {{if $evenement|instanceof:'Ox\Mediboard\SalleOp\CAnesthPerop'}}
        <tr>
          <th>{{mb_label object=$evenement field=datetime}}</th>
          <td>{{mb_field object=$evenement field=datetime form="edit-evenement-`$evenement->_guid`" register=true}}</td>
        </tr>
        <tr>
          <th>{{mb_label object=$evenement field=geste_perop_id}}</th>
          <td>
            {{mb_field object=$evenement field=geste_perop_id hidden=1}}

            {{if $evenement->_ref_geste_perop && $evenement->_ref_geste_perop->_id}}
              {{$evenement->_ref_geste_perop->_view}}
            {{else}}
              <input type="text" name="geste_perop_id_view" value="{{$evenement->_ref_geste_perop}}" />
            {{/if}}
          </td>
        </tr>
          {{assign var=precisions value=$evenement->_ref_geste_perop->_ref_precisions}}
          {{assign var=valeurs    value=$evenement->_ref_geste_perop_precision->_ref_valeurs}}
          <tr>
            <th>{{mb_label object=$evenement field=geste_perop_precision_id}}</th>
            <td>
              <div id="list_precisions_gesture">
                {{mb_include module=salleOp template=inc_vw_geste_precisions precisions=$precisions evenement=$evenement}}
              </div>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$evenement field=precision_valeur_id}}</th>
            <td>
              <div id="list_precision_valeurs">
                  {{mb_include module=salleOp template=inc_vw_precision_valeurs valeurs=$valeurs evenement=$evenement}}
              </div>
            </td>
          </tr>
        {{if !$evenement->_id}}
          <tr>
            <th>{{mb_label object=$evenement field=libelle}}</th>
            <td>
              {{if $operation->_ref_anesth->_id}}
                {{assign var=contextUserId value=$operation->_ref_anesth->_id}}
                {{assign var=contextUserView value=$operation->_ref_anesth->_view|smarty:nodefaults:JSAttribute}}
              {{else}}
                {{assign var=contextUserId value=$app->_ref_user->_id}}
                {{assign var=contextUserView value=$app->_ref_user->_view|smarty:nodefaults:JSAttribute}}
              {{/if}}

              {{mb_field object=$evenement field=libelle form="edit-evenement-`$evenement->_guid`"
              aidesaisie="contextUserId: '$contextUserId', contextUserView: '$contextUserView'"}}
            </td>
          </tr>
        {{/if}}
        <tr>
          <th>{{mb_label object=$evenement field=commentaire}}</th>
          <td>
            {{mb_field object=$evenement field=commentaire}}
          </td>
        </tr>
        <tr>
          <th>{{mb_label object=$evenement field=incident}}</th>
          <td>{{mb_field object=$evenement field=incident typeEnum=radio}}</td>
        </tr>
        <tr id="show_make_antecedent">
          <td></td>
          <td>
            <label>
              <input name="antecedent" type="checkbox" value="" onchange="GestePerop.incidentAntecedent(this.form);"/>
              {{tr}}CAntecedent-action-Make an antecedent{{/tr}}
            </label>
          </td>
        </tr>
        <tr>
          <td></td>
          <td id="do_antecedent"></td>
        </tr>
        {{if !$evenement->_id}}
          <tr>
            <th>{{mb_label object=$evenement field=categorie_id}}</th>
            <td>
              <select name="categorie_id">
                <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                {{foreach from=$evenement_categories item=_category}}
                  <option value="{{$_category->_id}}" {{if $evenement->_ref_categorie && ($_category->_id == $evenement->_ref_categorie->_id)}}selected{{/if}}>{{$_category->libelle}}</option>
                {{/foreach}}
              </select>
            </td>
          </tr>
        {{/if}}
        <tr>
          <td class="button" colspan="2">
            <button type="button" class="submit" onclick="return GestePerop.submitPerOp(this.form, '{{$limit_date_min}}');">{{tr}}Save{{/tr}}</button>

            {{if $evenement->_id}}
              <button type="button" class="trash" onclick="confirmDeletion(this.form, {typeName: 'l\'événement', objName:'{{$evenement->_shortview|smarty:nodefaults|JSAttribute}}', ajax:true}, {onComplete:function() { Control.Modal.close(); reloadSurveillance.perop();}});">{{tr}}Delete{{/tr}}</button>
            {{/if}}
          </td>
        </tr>
      {{/if}}
    </table>
  </form>

  <form name="addAntecedentIncident" action="?" method="post">
    <input type="hidden" name="m"           value="patients" />
    <input type="hidden" name="del"         value="0" />
    <input type="hidden" name="dosql"       value="do_antecedent_aed" />
    <input type="hidden" name="_patient_id" value="{{$operation->_ref_sejour->patient_id}}" />
    <input type="hidden" name="_sejour_id"  value="{{$operation->sejour_id}}" />
    <input type="hidden" name="date"        value="now" />
    <input type="hidden" name="type"        value="anesth" />
    <input type="hidden" name="rques"       value="" />
  </form>
