{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=include_form value=1}}
{{mb_default var=see_alertes value=0}}
{{mb_default var=name_form value=""}}

{{if $line->_recent_modification}}
  {{if 'Ox\Core\Handlers\Facades\HandlerManager::isObjectHandlerActive'|static_call:'CPrescriptionAlerteHandler' && $line->_ref_alerte->_id}}
    {{if !$see_alertes}}
      <div id="alert_manuelle{{$name_form}}_{{$line->_ref_alerte->_id}}">
      {{assign var=img_src value="ampoule"}}
      {{if $line->_urgence}}
        {{assign var=img_src value="ampoule_urgence"}}
      {{elseif in_array($line->_chapitre, array("psy", "kine"))}}
        {{assign var=img_src value="ampoule_green"}}
      {{/if}}
      <img style="float: left" src="images/icons/{{$img_src}}.png" onclick="alerte_prescription = ObjectTooltip.createDOM(this, 'editAlerte{{$name_form}}-{{$line->_ref_alerte->_id}}', { duration: 0}); "/>
    {{/if}}
      {{if $include_form || $see_alertes}}
        <div id="editAlerte{{$name_form}}-{{$line->_ref_alerte->_id}}" style="display: none;">
          <table class="form">
            <tr>
              <th class="category">{{tr}}Alerte{{/tr}}</th>
            </tr>
            <tr>
              <td class="text" style="width: 300px;">
                {{mb_value object=$line->_ref_alerte field=comments}}
              </td>
            </tr>
            <tr>
              <td class="button">
                <form name="modifyAlert{{$name_form}}-{{$line->_ref_alerte->_id}}" action="?" method="post" class="form-alerte{{if $line->_urgence}}-urgence{{/if}}-_{{$line->_guid}}"
                      onsubmit="return onSubmitFormAjax(this, {
                        onComplete: function() { $('alert_manuelle{{$name_form}}_{{$line->_ref_alerte->_id}}').hide(); if(alerte_prescription ) { alerte_prescription.hide(); }} });">
                  <input type="hidden" name="m" value="system" />
                  <input type="hidden" name="dosql" value="do_alert_aed" />
                  <input type="hidden" name="del" value="" />
                  <input type="hidden" name="alert_id" value="{{$line->_ref_alerte->_id}}" />
                  <input type="hidden" name="handled" value="1" />
                  <button type="button" class="tick" onclick="this.form.onsubmit();">{{tr}}Treat{{/tr}}</button>
                </form>
              </td>
            </tr>
          </table>
        </div>
      {{/if}}
    {{if !$see_alertes}}
      </div>
    {{/if}}
  {{elseif !$see_alertes}}
    <div class="me-bulb-info me-bulb-ampoule{{if in_array($line->_chapitre, array("psy", "kine"))}}_green{{/if}}"
         title="{{tr}}CPrescriptionLine-recent_modif{{/tr}}" style="float: left">
      <img class="me-no-display"
           src="images/icons/ampoule{{if in_array($line->_chapitre, array("psy", "kine"))}}_green{{/if}}.png"/>
    </div>
    {{if is_array($line->_dates_urgences) && array_key_exists($date, $line->_dates_urgences)}}
      <div class="me-bulb-info me-bulb-ampoule_urgence"
           title="{{tr}}Emergency{{/tr}}" style="float: left">
        <img class="me-no-display" style="float: left" src="images/icons/ampoule_urgence.png" title="{{tr}}Emergency{{/tr}}"/>
      </div>
    {{/if}}
  {{/if}}
{{/if}}
