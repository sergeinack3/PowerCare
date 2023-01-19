{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "atih"|module_active}}

  {{assign var=config_uf value="atih uf uf_pmsi"|gconf}}
  <script>
    disabledUFforPMSI = function (config_uf) {
      $("Edit-CUniteFonctionnelle_type_autorisation_um").disabled = true;
      $("Edit-CUniteFonctionnelle_type_autorisation_um").hide();
      $("config_uf_pmsi").addClassName("warning");
      $("config_uf_pmsi").innerHTML = $T('PMSI-msg-Available only for UF %s', config_uf);
    };

    chooseUmPMSI = function (element, config_uf) {
      if (config_uf == element.value) {
        $("Edit-CUniteFonctionnelle_type_autorisation_um").disabled = false;
        $("Edit-CUniteFonctionnelle_type_autorisation_um").show();
        $("config_uf_pmsi").removeClassName("warning");
        $("config_uf_pmsi").innerHTML = "";
      }
      else {
        disabledUFforPMSI(config_uf);
      }
    };

    Main.add(function () {
      var config_uf = {{$config_uf}}.
      id;

      getForm("Edit-CUniteFonctionnelle").select("input[type=radio]:checked").each(function (oRadio) {
        if (oRadio.value != config_uf) {
          disabledUFforPMSI(config_uf);
        }
      });
    });
  </script>
{{/if}}

{{assign var=config_uf value="atih uf uf_pmsi"|gconf}}

<form name="Edit-CUniteFonctionnelle" action="" method="post"
      onsubmit="return onSubmitFormAjax(this, {onComplete: Infrastructure.UF.refreshList})">
  {{mb_class object=$uf}}
  {{mb_key   object=$uf}}
  <input type="hidden" name="del" value="0" />
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$uf}}
    <tr>
      <th>{{mb_label object=$uf field=group_id}}</th>
      <td>{{mb_field object=$uf field=group_id options=$etablissements}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$uf field=type}}</th>
      <td>{{mb_field object=$uf field=type typeEnum=radio onclick="chooseUmPMSI(this, '$config_uf');"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$uf field=type_sejour}}</th>
      <td>{{mb_field object=$uf field=type_sejour emptyLabel="Choose"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$uf field=code}}</th>
      <td>{{mb_field object=$uf field=code}}</td>
    </tr>
    {{if "atih"|module_active}}
      <tr>
        <th title="{{tr}}CUniteMedicaleInfos-PMSI medical unit-desc{{/tr}}">{{tr}}CUniteMedicaleInfos-PMSI medical unit{{/tr}}</th>
        <td>
          <select name="type_autorisation_um" style="width:14em; vertical-align: top">
            <option value="">{{tr}}Choose{{/tr}}</option>
            {{foreach from=$ums_infos item=_um}}
              {{assign var=code value=$_um->um_code}}
              <option value="{{$_um->_id}}" {{if $uf->type_autorisation_um == $_um->_id}}selected{{/if}}>{{$_um->_id}} - {{$code}}
                - {{$ums.$code->libelle}} - {{tr var1=$_um->nb_lits}}CLit-%s bed(|pl){{/tr}}
                - {{$_um->date_effet|date_format:$conf.date}}</option>
            {{/foreach}}
          </select>
          {{if $ums_infos|@count == 0}}
            <div class="warning"
                 style="display:inline-block">{{tr}}CUniteMedicaleInfos-msg-You must first configure the UMs in the pmsi module for access{{/tr}}</div>
          {{/if}}
          <div id="config_uf_pmsi" class="" style="display:inline-block"></div>
        </td>
      </tr>
    {{/if}}
    <tr>
      <th>{{mb_label object=$uf field=libelle}}</th>
      <td>{{mb_field object=$uf field=libelle}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$uf field=date_debut}}</th>
      <td>{{mb_field object=$uf field=date_debut form="Edit-CUniteFonctionnelle" register=true}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$uf field=date_fin}}</th>
      <td>{{mb_field object=$uf field=date_fin form="Edit-CUniteFonctionnelle" register=true}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$uf field=description}}</th>
      <td>{{mb_field object=$uf field=description}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $uf->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="confirmDeletion(
                    this.form,
                    {typeName:'lUF',objName: $V(this.form.libelle), ajax: 1},
                    Infrastructure.UF.refreshList
                    )">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>