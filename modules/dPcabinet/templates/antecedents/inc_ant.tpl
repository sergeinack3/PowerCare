{{*
 * @package Mediboard\dPcabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<li class="{{if $antecedent->annule}}cancelled{{/if}}"
    style="{{if $antecedent->annule}}display: none;{{/if}} {{if $antecedent->owner_id != $app->user_id}}padding-bottom: 7px;{{/if}}">
  <form name="Del-{{$antecedent->_guid}}" action="?m=dPcabinet" method="post">
    <input type="hidden" name="m" value="dPpatients" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="dosql" value="do_antecedent_aed" />
    {{mb_key object=$antecedent}}

    <input type="hidden" name="annule" value="" />

    <!-- Seulement si l'utilisateur est le créateur -->
    {{if $antecedent->owner_id == $app->user_id && !$create_antecedent_only_prat}}
      <button title="{{tr}}Delete{{/tr}}" class="trash notext not-printable me-tertiary me-dark" type="button" onclick="
        {{if $antecedent->_codes_cim10 || ($sejour->_id && $antecedent->_antecedent_sejour->_id)}}
          Antecedent.checkSignificativeElements('{{$antecedent->_id}}'{{if $sejour->_id}}, '{{$sejour->_id}}'{{/if}});
        {{else}}
          Antecedent.remove(this.form, function() {
          if (window.DossierMedical) {
          DossierMedical.reloadDossierPatient(null, '{{$type_see}}');
          }
          if (window.reloadAtcd) {
          reloadAtcd();
          }
          if (window.reloadAtcdMajeur) {
          reloadAtcdMajeur();
          }
          if (window.reloadAtcdOp) {
          reloadAtcdOp();
          }
          })
        {{/if}}">
        {{tr}}Delete{{/tr}}
      </button>
    {{/if}}

    {{if $_is_anesth && $sejour->_id && !$create_antecedent_only_prat}}
      <button class="add notext not-printable me-tertiary" type="button" onclick="copyAntecedent({{$antecedent->_id}})">
        {{tr}}Add{{/tr}} comme élément significatif
      </button>
    {{/if}}
  </form>

  {{assign var=creation_date value=$antecedent->creation_date|date_format:'%Y-%m-%d'}}
  {{assign var=_color value='black'}}
  {{if $IS_MEDIBOARD_EXT_DARK}}
      {{assign var=_color value="rgba(255, 255, 255, 0.6)"}}
  {{/if}}
  {{if $antecedent->majeur}}
    {{assign var=_color value='#f00'}}
    {{if $IS_MEDIBOARD_EXT_DARK}}
      {{assign var=_color value="#ff8366"}}
    {{/if}}
  {{elseif $antecedent->important}}
    {{assign var=_color value='#fd7d26'}}
    {{if $IS_MEDIBOARD_EXT_DARK}}
      {{assign var=_color value="#feb17d"}}
    {{/if}}
  {{elseif ($context_date_max && $context_date_min && $creation_date >= $context_date_min && $creation_date <= $context_date_max)
           || ($context_date_max && !$context_date_min && $creation_date == $context_date_max)}}
    {{assign var=_color value='darkblue'}}
    {{if $IS_MEDIBOARD_EXT_DARK}}
      {{assign var=_color value="#6666ba"}}
    {{/if}}
  {{elseif $context_date_max && $creation_date >= $context_date_max}}
    {{assign var=_color value='dimgrey'}}
    {{if $IS_MEDIBOARD_EXT_DARK}}
      {{assign var=_color value="#a5a5a5"}}
    {{/if}}
  {{/if}}
  {{assign var=class_canceled value="antecedent_element"}}
  {{if $antecedent->absence}}
    {{assign var=class_canceled value="antecedent_element_NP"}}
  {{/if}}
  <span class="{{if !$antecedent->annule}}{{$class_canceled}}{{/if}}" style="color: {{$_color}};" onmouseover="ObjectTooltip.createEx(this, '{{$antecedent->_guid}}')" data-antecedent_id="{{$antecedent->_id}}">
    {{if $sort_by_date}}
      <strong style="margin-right: 5px;">
        {{if $antecedent->type    }} {{mb_value object=$antecedent field=type    }} {{/if}}
        {{if $antecedent->appareil}} {{mb_value object=$antecedent field=appareil}} {{/if}}
      </strong>
    {{/if}}
    {{if $antecedent->date}}
      {{mb_value object=$antecedent field=date}} :
    {{/if}}
    {{$antecedent->rques|nl2br}}{{if $antecedent->family_link && ($antecedent->type == "fam")}} ({{mb_value object=$antecedent field="family_link"}}) {{/if}}
  </span>

  {{if $antecedent->_codes_cim10|@count || $antecedent->_codes_ccam|@count}}
    <div id="detail_codes_cim_{{$antecedent->_id}}" style="display: inline-block;">
      <span style="margin-left: 20px;"></span>
        {{foreach from=$antecedent->_codes_cim10_detail item=_code_cim}}
          <span class="texticon texticon-cim10"
                title="{{$_code_cim->libelle}} ({{tr}}mod-dPcim10-tab-court{{/tr}})">
              {{$_code_cim->code}}
          </span>
        {{/foreach}}
        {{foreach from=$antecedent->_codes_ccam item=_code_ccam}}
          <span class="texticon texticon-ccam-atcd"
                title="{{$_code_ccam->libelleLong}} ({{tr}}CCAM{{/tr}})">
              {{$_code_ccam->code}}
          </span>
        {{/foreach}}
    </div>
  {{/if}}
</li>
