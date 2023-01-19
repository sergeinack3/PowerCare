{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form me-no-align me-no-box-shadow">
  <tr>
    <th class="title me-padding-0">Sur le plan social</th>
  </tr>
</table>
<form name="Social-mere-{{$patient->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$patient}}
  {{mb_key   object=$patient}}
  <input type="hidden" name="_count_changes" value="0" />
  <table class="form me-no-align me-no-box-shadow me-small-form">
    <tr>
      <th class="halfPane">{{mb_label object=$patient field=ressources_financieres}} de la mère</th>
      <td>
        {{mb_field object=$patient field=ressources_financieres
        style="width: 12em;" emptyLabel="CPatient.ressources_financieres."}}
      </td>
    </tr>
    <tr>
      <th class="halfPane">{{mb_label object=$patient field=regime_sante}}</th>
      <td>{{mb_field object=$patient field=regime_sante}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=c2s}}</th>
      <td>{{mb_field object=$patient field=c2s typeEnum=checkbox}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=ame}}</th>
      <td>{{mb_field object=$patient field=ame typeEnum=checkbox}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$patient field=hebergement_precaire}}</th>
      <td>{{mb_field object=$patient field=hebergement_precaire default=""}}</td>
    </tr>
  </table>
</form>
<form name="Social-pere-{{$pere->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$pere}}
  {{mb_key   object=$pere}}
  <input type="hidden" name="_count_changes" value="0" />
  <table class="form me-no-align me-no-box-shadow">
    <tr>
      <th class="halfPane">{{mb_label object=$pere field=ressources_financieres}} du père</th>
      {{if $grossesse->pere_id}}
        <td>
          {{mb_field object=$pere field=ressources_financieres
          style="width: 12em;" emptyLabel="CPatient.ressources_financieres."}}
        </td>
      {{else}}
        <td class="empty">Père non renseigné</td>
      {{/if}}
    </tr>
  </table>
</form>

{{if !$print}}
  <form name="Social-dossier-{{$dossier->_guid}}" method="post"
        onsubmit="return onSubmitFormAjax(this);">
    {{mb_class object=$dossier}}
    {{mb_key   object=$dossier}}
    <input type="hidden" name="_count_changes" value="0" />
    <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
    <table class="form me-no-box-shadow me-no-align">
      <tr>
        <th class="halfPane">{{mb_label object=$dossier field=rques_social}}</th>
        <td>{{mb_field object=$dossier field=rques_social form=Social-dossier-`$dossier->_guid`}}</td>
      </tr>
    </table>
  </form>
{{else}}
  <table class="form me-no-box-shadow me-no-align">
    <tr>
      <th class="halfPane">{{mb_label object=$dossier field=rques_social}}</th>
      <td>{{mb_label object=$dossier field=rques_social}}</td>
    </tr>
  </table>
{{/if}}
