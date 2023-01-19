{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="duplicate_sejour" method="post" action="?">
  <input type="hidden" name="m" value="ssr" />
  <input type="hidden" name="dosql" value="do_duplicate_sejour_ssr_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="module" value="{{$m}}" />
  <input type="hidden" name="sejour_id" value="" />
  <input type="hidden" name="original_sejour_id" value="" />
</form>

<script>
  duplicateSejour = function(sejour_id, original_sejour_id){
    var form = getForm("duplicate_sejour");
    $V(form.sejour_id, sejour_id);
    $V(form.original_sejour_id, original_sejour_id);
    form.submit();
  }
</script>

<table class="tbl">
  <tr>
    <th class="title" colspan="6">{{tr}}CSejour{{$m|strtoupper}}-all_before{{/tr}}</th>
  </tr>

  <tr>
    <th style="width:  5em;">{{mb_title class="CSejour" field="entree"}}</th>
    <th style="width:  5em;">{{mb_title class="CSejour" field="sortie"}}</th>
    <th style="width: 20em;">{{mb_title class="CSejour" field="libelle"}}</th>
    <th style="width: 12em;">{{mb_title class=CSejour field=praticien_id}}</th>
    <th class="narrow"><label title="{{tr}}CPrescription{{/tr}}">{{tr}}srr-prescription-court{{/tr}}</label></th>
    <th class="narrow"><button class="change notext" style="visibility: hidden;">{{tr}}Duplicate{{/tr}}</button></th>
  </tr>

  {{foreach from=$sejours item=_sejour}}
    <tr>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
          {{mb_value object=$_sejour field=entree format=$conf.date}}
        </span>
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
          {{mb_value object=$_sejour field=sortie format=$conf.date}}
        </span>
      </td>
      <td class="text">
        {{mb_include module=system template=inc_get_notes_image object=$_sejour mode=view float=right}}
        {{assign var=bilan value=$_sejour->_ref_bilan_ssr}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$bilan->_guid}}')">
          {{mb_value object=$_sejour field=libelle}}
        </span>
        {{assign var=libelle value=$_sejour->libelle|upper|smarty:nodefaults}}
        {{assign var=color value=$colors.$libelle}}
        {{if $color->color}}
          <div class="motif-color" style="background-color: #{{$color->color}};" ></div>
        {{/if}}
      </td>

      <td class="text">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien}}
        {{assign var=prat_demandeur value=$_sejour->_ref_bilan_ssr->_ref_prat_demandeur}}
        {{if $prat_demandeur->_id}}
        <br />{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$prat_demandeur}}
        {{/if}}
      </td>

      <td style="text-align: center;">
        {{assign var=prescription value=$_sejour->_ref_prescription_sejour}}
        {{if $prescription->_id}}
          {{if $prescription->_count_recent_modif_presc}}
            <img src="images/icons/ampoule.png" onmouseover="ObjectTooltip.createEx(this, '{{$prescription->_guid}}')"/>
          {{else}}
            <img src="images/icons/ampoule_grey.png" onmouseover="ObjectTooltip.createEx(this, '{{$prescription->_guid}}')"/>
          {{/if}}
        {{/if}}
      </td>

      <td class="button">
        {{if !$sejour->_ref_prescription_sejour->_count.prescription_line_element}}
          <button type="button" class="duplicate notext" onclick="duplicateSejour('{{$sejour->_id}}', '{{$_sejour->_id}}');">{{tr}}Duplicate{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  {{foreachelse}}
    <tr><td class="empty" colspan="6">{{tr}}CSejour.none{{/tr}}</td></tr>
  {{/foreach}}
</table>
