{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hospi     script=affectation_uf     ajax=true}}
{{mb_script module=mediusers script=CMediuserFunctions ajax=true}}
{{mb_script module=mediusers script=export_mediusers   ajax=true}}
{{mb_script module=mediusers script=CMediusers         ajax=true}}

{{if 'appFineClient'|module_active}}
    {{mb_script module=appFineClient script=appFineClient ajax=true}}
{{/if}}

{{assign var=configLDAP value=$conf.admin.LDAP.ldap_connection}}

<script>
  createUserFromLDAP = function(){
    var url = new Url("admin", "ajax_choose_filter_ldap");
    url.requestModal(800, 350);
  };

  changePage = function(page) {
    $V(getForm('listFilter').page, page);
  };

  changeFilter = function(order, way) {
    var form = getForm('listFilter');
    $V(form.order_col, order);
    $V(form.order_way, way);
    form.onsubmit();
  };

  openImportMediusersCSV = function() {
    var url = new Url("mediusers", "vw_import_mediusers_csv");
    url.requestModal("70%", "70%");
  };

  Main.add(function() {
    CMediusers.no_association = "{{$no_association}}";
    CMediusers.ldap_user_actif = "{{$ldap_user_actif}}";
    CMediusers.ldap_user_deb_activite = "{{$ldap_user_deb_activite}}";
    CMediusers.ldap_user_fin_activite = "{{$ldap_user_fin_activite}}";

    {{if $user_id}}
    CMediusers.editMediuser('{{$user_id}}');
    {{/if}}
    getForm('listFilter').onsubmit();
  });
</script>

<div style="padding-bottom: 5px" class="me-margin-top-4 me-padding-left-8">
  {{if $can->edit}}
    {{if !'Ox\Core\CAppUI::restrictUserCreation'|static_call:null}}
      <a href="#" onclick="CMediusers.editMediuser(0)" class="button new">
        {{tr}}CMediusers-title-create{{/tr}}
      </a>
    {{/if}}

    {{if $configLDAP}}
      <button class="new" onclick="createUserFromLDAP()">
        {{tr}}CMediusers_create-ldap{{/tr}}
      </button>
    {{/if}}
  {{/if}}

  {{if 'Ox\Core\CAppUI::conf'|static_call:'dPpatients CPatient function_distinct' || "rpps"|module_active}}
    <button class="new" type="button" onclick="CMediusers.searchMedecinAnnuaire();">
      {{tr}}CMediusers_search-medecin-create{{/tr}}
    </button>
  {{/if}}

  <style>
    fieldset.fieldset_search div {
      display: inline-block;
    }
  </style>

  <button type="button" onclick="openImportMediusersCSV();" class="import">{{tr}}CMediusers-import{{/tr}}</button>
  <button class="fas fa-external-link-alt" onclick="ExportMediusers.openExportMediusersCSV();">{{tr}}CMediusers-export-csv{{/tr}}</button>
</div>

<form name="listFilter" action="?m={{$m}}" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result_search_mb')">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="a" value="ajax_search_mediusers" />
  <input type="hidden" name="page" value="0" onchange="this.form.onsubmit()"/>
  <input type="hidden" name="order_col" value="function_id"/>
  <input type="hidden" name="order_way" value="ASC""/>

  <table class="main layout">
    <tr>
      <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>

      <td>
        <table class="main form">
          <tr>
            <th> Mots clés : </th>
            <td> <input type="text" name="filter" value="{{$filter}}" style="width: 20em;" onchange="$V(this.form.page, 0)" /> </td>

            <th> {{tr}}CFunctions{{/tr}} </th>
            <td>
              <select name="function_id" style="width: 15em;">
                <option value="">&mdash; {{tr}}CFunctions.all{{/tr}}</option>
                {{foreach from=$group->_ref_functions item=_function}}
                  <option value="{{$_function->_id}}">{{$_function}}</option>
                {{/foreach}}
              </select>
            </td>

            <th> {{mb_label class="CMediusers" field="_user_type"}} </th>
            <td>
              <select name="_user_type" style="width: 15em;">
                <option value="">&mdash; {{tr}}All{{/tr}}</option>
                <option value="ps"> > Professionnel de santé</option>
                {{foreach from=$utypes key=curr_key item=_type}}
                  <option value="{{if $curr_key != 0}}{{$curr_key}}{{/if}}" {{if $type == $curr_key}}selected="selected"{{/if}}>
                    {{$_type}}
                  </option>
                {{/foreach}}
              </select>
            </td>
          </tr>

          <tr>
            <th> Verrouillage </th>
            <td>
              <label>{{tr}}All{{/tr}} <input name="locked" value="" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
              <label>Verrouillés <input name="locked" value="1" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
              <label>Non verrouillés <input name="locked" value="0" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
            </td>

            <th> Inactif </th>
            <td>
              <label>{{tr}}All{{/tr}} <input name="inactif" value="" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
              <label>Inactifs <input name="inactif" value="1" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
              <label>Actifs <input name="inactif" value="0" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
            </td>

            <th> Type d'utilisateur </th>
            <td>
              <label>{{tr}}All{{/tr}} <input name="user_loggable" value="" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
              <label>Humain <input name="user_loggable" value="human" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
              <label>Robot <input name="user_loggable" value="robot" type="radio" onchange="$V(this.form.page, 0, false)"/></label>
            </td>
          </tr>

          {{if $configLDAP}}
          <tr>
            <th>{{mb_label object=$mediuser field=_ldap_bound}}</th>
            <td>
              {{mb_field object=$mediuser field=_ldap_bound}}
            </td>

            <th></th>
            <td></td>

            <th></th>
            <td></td>
          </tr>
          {{/if}}

          <tr>
            <td colspan="6">
              <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
            </td>
          </tr>
       </table>
      </td>
    </tr>
  </table>
</form>


<div id="result_search_mb" style="overflow: hidden" class="me-padding-8"></div>
