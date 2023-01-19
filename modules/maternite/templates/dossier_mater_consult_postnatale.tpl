{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$grossesse->_ref_parturiente}}
{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}

{{mb_script module=maternite script=naissance ajax=1}}

<script>
  listForms = [
    {{foreach from=$dossier->_ref_consultations_post_natale item=_consult_post_natale}}
    getForm("Consult-postnatale-{{$_consult_post_natale->_guid}}"),
    getForm("Consult-postnatale-maman-{{$_consult_post_natale->_guid}}"),
    {{foreach from=$grossesse->_ref_naissances item=naissance}}
    getForm("Consult-postnatale-enfant-{{$_consult_post_natale->_guid}}-{{$naissance->_guid}}"),
    {{/foreach}}
    getForm("Consult-postnatale-constantes-{{$_consult_post_natale->_guid}}"),
    getForm("Consult-postnatale-examen-{{$_consult_post_natale->_guid}}"),
    getForm("Consult-postnatale-conclusion-examen-{{$_consult_post_natale->_guid}}"),
    getForm("Consult-postnatale-info-presc-{{$_consult_post_natale->_guid}}"),
    {{foreachelse}}
    getForm("Consult-postnatale-CConsultationPostNatale-none"),
    {{/foreach}}
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  submitAllForms = function (callBack) {
    includeForms();
    DossierMater.submitAllForms(callBack);
  };

  Main.add(function () {
    {{if !$print && $dossier->_ref_consultations_post_natale|@count}}
    includeForms();
    DossierMater.prepareAllForms();
    {{/if}}
    Control.Tabs.create('tab-consultations_postnatale', true);
  });
</script>

{{mb_include module=maternite template=inc_dossier_mater_header id_close="close_dossier_perinat"
with_save=$dossier->_ref_consultations_post_natale|@count}}

<ul id="tab-consultations_postnatale" class="control_tabs">
  {{foreach from=$dossier->_ref_consultations_post_natale item=_consult_post_natale}}
    <li>
      <a href="#{{$_consult_post_natale->_guid}}">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult_post_natale->_guid}}')">
          {{tr var1=$_consult_post_natale->date|date_format:$conf.date}}CConsultationPostNatale-of{{/tr}}
          ({{$_consult_post_natale->_ref_consultant->_view}})
        </span>
      </a>
    </li>
    {{foreachelse}}
    <li><a href="#CConsultationPostNatale-new">{{tr}}CConsultationPostNatale-title-create{{/tr}}</a></li>
  {{/foreach}}
  {{if $dossier->_ref_consultations_post_natale|@count}}
    <button type="button" class="add" style="float: right;" onclick="DossierMater.addConsultationPostNatale('{{$dossier->_id}}');">
      {{tr}}CConsultationPostNatale{{/tr}}
    </button>
  {{/if}}
</ul>

{{foreach from=$dossier->_ref_consultations_post_natale item=_consult_post_natale}}
  <div id="{{$_consult_post_natale->_guid}}" style="display: none;">
    {{mb_include module=maternite template=vw_form_consult_post_natale consult_post_natale=$_consult_post_natale}}
  </div>
  {{foreachelse}}
  <div id="CConsultationPostNatale-new" style="display: none;">
    {{mb_include module=maternite template=vw_form_consult_post_natale
    consult_post_natale='Ox\Mediboard\Maternite\CDossierPerinat::emptyConsultationPostNatale'|static_call:""}}
  </div>
{{/foreach}}
