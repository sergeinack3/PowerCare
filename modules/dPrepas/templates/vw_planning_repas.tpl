{{*
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function devalidationRepas(validation_id) {
    var form = getForm("validRepas");
    $V(form.del, 1);
    $V(form.validationrepas_id, validation_id);
    oForm.submit();
  }

  function validationRepas(typerepas_id) {
    var form = getForm("validRepas");
    $V(form.typerepas_id, typerepas_id);
    form.submit();
  }

  Main.add(function () {
    Calendar.regField(getForm("changeDate").date, null, {noView: true});
  });
</script>

<form name="validRepas" action="?m={{$m}}" method="post">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="dosql" value="do_validationrepas_aed" />
  <input type="hidden" name="date" value="{{$date}}" />
  <input type="hidden" name="service_id" value="{{$service_id}}" />
  <input type="hidden" name="typerepas_id" value="" />
  <input type="hidden" name="validationrepas_id" value="" />
</form>

<table class="main">
  <tr>
    <td>
      <form name="FrmSelectService" action="?m={{$m}}" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />

        <label for="service_id" title="Veuillez sélectionner un service">Service</label>
        <select name="service_id" onchange="this.form.submit();">
          <option value="">&mdash; Veuillez sélectionner un service</option>
          {{foreach from=$services item=curr_service}}
            <option value="{{$curr_service->service_id}}" {{if $curr_service->service_id == $service_id}}selected{{/if}}>
              {{$curr_service->nom}}
            </option>
          {{/foreach}}
        </select>
        pour le {{$date|date_format:$conf.longdate}}
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
      </form>
      <br />
    </td>
  </tr>
  {{if $service_id}}
    <tr>
      <td>
        <table class="tbl">
          <tr>
            <td colspan="2"></td>
            {{foreach from=$listTypeRepas item=curr_type}}
              <th class="category">
                {{assign var="type_id" value=$curr_type->_id}}
                {{assign var="validation" value=$service->_ref_validrepas.$date.$type_id}}
                {{if $validation->validationrepas_id}}
                  <button type="button" class="cancel notext" onclick="devalidationRepas({{$validation->validationrepas_id}})"
                          style="float:right;">{{tr}}Cancel{{/tr}}</button>
                {{else}}
                  <button type="button" class="tick notext" onclick="validationRepas({{$curr_type->_id}})"
                          style="float:right;">{{tr}}Modify{{/tr}}</button>
                {{/if}}
                {{$curr_type->nom}}
              </th>
            {{/foreach}}
          </tr>

          {{foreach from=$service->_ref_chambres item=curr_chambre}}
            {{foreach from=$curr_chambre->_ref_lits item=curr_lit}}
              {{foreach from=$curr_lit->_ref_affectations item=curr_affect}}
                <tr>
                  <td>{{$curr_chambre->_shortview}} - {{$curr_lit->_shortview}}</td>
                  <td>{{$curr_affect->_ref_sejour->_ref_patient->_view}}</td>
                  {{foreach from=$listTypeRepas key=keyType item=curr_type}}
                    <td class="button">
                      {{if ($date == $curr_affect->entree|iso_date
                      && $curr_affect->entree|date_format:$conf.time > $curr_type->fin)
                      ||
                      ($date == $curr_affect->sortie|iso_date
                      && $curr_type->debut > $curr_affect->sortie|date_format:$conf.time)
                      }}
                        -
                      {{elseif $curr_affect->_list_repas.$date.$keyType->repas_id && $curr_affect->_list_repas.$date.$keyType->menu_id}}
                        <a
                          href="?m={{$m}}&tab=vw_edit_repas&affectation_id={{$curr_affect->affectation_id}}&typerepas_id={{$keyType}}">
                          <img src="images/icons/tick-dPrepas.png" width="20" height="20" alt="Repas commandé" />
                        </a>
                      {{elseif $curr_affect->_list_repas.$date.$keyType->repas_id}}
                        <a
                          href="?m={{$m}}&tab=vw_edit_repas&affectation_id={{$curr_affect->affectation_id}}&typerepas_id={{$keyType}}">
                          <img src="images/icons/no.png" width="20" height="20" alt="" />
                        </a>
                      {{else}}
                        <a
                          href="?m={{$m}}&tab=vw_edit_repas&affectation_id={{$curr_affect->affectation_id}}&typerepas_id={{$keyType}}">
                          <img src="images/icons/flag.png" width="20" height="20" alt="Repas à commander" />
                        </a>
                      {{/if}}
                    </td>
                  {{/foreach}}
                </tr>
              {{/foreach}}
            {{/foreach}}
          {{/foreach}}
        </table>
      </td>
    </tr>
  {{/if}}
</table>