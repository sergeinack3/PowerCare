{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=_is_dentiste value=false}}
{{mb_default var=executant_id value=false}}
{{mb_default var=execution value=false}}
{{mb_default var=modal value=false}}
{{mb_default var=order_col value='code'}}
{{mb_default var=order_way value='ASC'}}
{{mb_default var=target value='listActesNGAP'}}
{{mb_default var=display value=null}}
{{mb_default var=show_tarifs value=false}}

{{assign var=codage_rights value='dPccam codage rights'|gconf}}
{{assign var=user value='Ox\Mediboard\Mediusers\CMediusers::get'|static_call:null}}

{{if $modal}}
  <div id="listActesNGAP" data-object_id="{{$subject->_id}}" data-object_class="{{$subject->_class}}"{{if $executant_id}} data-executant_id="{{$executant_id}}"{{/if}}{{if $execution}} data-execution="{{$execution}}"{{/if}}{{if $code}} data-code="{{$code}}"{{/if}}{{if $coefficient}} data-coefficient="{{$coefficient}}"{{/if}}{{if $show_tarifs}} data-show_tarifs="{{$show_tarifs}}"{{/if}}>
{{/if}}

{{if $show_tarifs && $acte_ngap && (($object->_class != 'CSejour' && !$object->_coded && $can->edit) || ($object->_class == 'CSejour' && !$object->_coded && 'dPccam codage allow_ngap_cotation_sejour'|gconf))}}
  <div id="tarifs_sejours"></div>
  <script type="text/javascript">
    Main.add(function() {
      new Url("soins", "ajax_tarifs_sejour")
        .addParam("sejour_id", '{{$subject->_id}}')
        .addParam("type_codes", 'ngap')
        .requestUpdate("tarifs_sejours");
    });

    loadActes =function() {
      new Url('cabinet', 'httpreq_vw_actes_ngap')
        .addParam('object_id', '{{$subject->_id}}')
        .addParam('object_class', '{{$subject->_class}}')
        .addParam('executant_id', '{{$executant_id}}')
        .addParam('execution', '{{$execution}}')
        .addParam('code', '{{$code}}')
        .addParam('coefficient', '{{$coefficient}}')
        .addParam('page', '0')
        .addParam('show_tarifs', '1')
        .requestUpdate('listActesNGAP');
    };
  </script>
{{/if}}

{{mb_script module=ccam script=actes_ngap ajax=1}}
<table class="tbl me-no-align me-no-box-shadow">
  {{if $object->_coded}}
    {{if $object->_class == "CConsultation"}}
      <tr>
        <td colspan="20">
          <div class="small-info">{{tr}}CCodable-codage_closed{{/tr}}</div>
        </td>
      </tr>
    {{else}}
      <tr>
        <td colspan="20" class="text">
          <div class="small-info">
            {{assign var=config value='dPsalleOp COperation modif_actes'|gconf}}
            {{if strpos($config, 'sortie_sejour') !== false}}
              {{assign var=config value='sortie_sejour'}}
            {{/if}}
            Les actes ne peuvent plus être modifiés pour la raison suivante : {{tr}}{{$object->_coded_message}}{{/tr}}
            <br />
            Veuillez contacter le PMSI pour toute modification.
          </div>
        </td>
      </tr>
    {{/if}}
  {{elseif $object->_class == 'CSejour' && !'dPccam codage allow_ngap_cotation_sejour'|gconf}}
    <tr>
      <td class="text" colspan="20">
        <div class="small-info">
          {{tr}}CSejour-msg-cotation_ngap_forbidden{{/tr}}
        </div>
      </td>
    </tr>
  {{/if}}

  {{if (!$can->edit && $subject->_class == "CConsultation") || !$can->read}}
    <tr>
      <td colspan="20" class="text">
        <div class="small-info">Vous n'avez pas les droits nécessaires pour coder les actes</div>
      </td>
    </tr>
  {{else}}
    {{if $object->_count_actes_ngap && $object->_count_actes_ngap != $object->_ref_actes_ngap|@count}}
      <tr>
        <td colspan="20">
          {{mb_include module=system template=inc_pagination total=$object->_count_actes_ngap current=$page step=10 change_page="ActesNGAP.changePage.curry('$target')"}}
        </td>
      </tr>
    {{/if}}
    {{mb_include module=cabinet template=inc_header_codage_ngap}}

    {{assign var=readonly value=1}}
    {{if $acte_ngap && (($object->_class != 'CSejour' && !$object->_coded && $can->edit) || ($object->_class == 'CSejour' && !$object->_coded && 'dPccam codage allow_ngap_cotation_sejour'|gconf))}}
      {{assign var=readonly value=0}}
      {{mb_include module=cabinet template=inc_line_codage_ngap acte=$acte_ngap}}
    {{elseif $display == 'pmsi'}}
      {{assign var=readonly value=0}}
    {{/if}}

    {{foreach from=$object->_ref_actes_ngap item=_acte_ngap}}
      {{if !$executant_id || $_acte_ngap->executant_id == $executant_id}}
        {{* Gestion des droits sur les codages*}}
        {{if @$modules.dPpmsi->_can->edit}}
          {{assign var=readonly value=0}}
        {{elseif $codage_rights == 'self' && $user->_id != $_acte_ngap->executant_id && $user->isProfessionnelDeSante()}}
          {{assign var=readonly value=1}}
        {{elseif $codage_rights == 'self' && $user->_id != $_acte_ngap->executant_id && !$user->isProfessionnelDeSante() && !$_acte_ngap->_ref_executant->getPerm(2)}}
          {{assign var=readonly value=1}}
        {{elseif $codage_rights == 'user_rights' && !$_acte_ngap->_ref_executant->getPerm(2)}}
          {{assign var=readonly value=1}}
        {{else}}
          {{assign var=readonly value=0}}
        {{/if}}

        {{mb_include module=cabinet template=inc_line_codage_ngap acte=$_acte_ngap}}
      {{/if}}
    {{foreachelse}}
      <tr>
        <td colspan="20" class="empty">{{tr}}CActeNGAP.none{{/tr}}</td>
      </tr>
   {{/foreach}}
  {{/if}}
</table>

{{if $modal}}
  </div>
{{/if}}