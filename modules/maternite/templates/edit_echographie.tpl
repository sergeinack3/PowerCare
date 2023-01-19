{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=maternite script=dossierMater ajax=1}}

<script>
  listForms = [
    {{foreach from=$foetus key=number_foetus item=_foetus}}
      getForm("Echographie_{{$number_foetus}}"),
    {{/foreach}}
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  refreshEchographies = function () {
    Control.Modal.close();
    Control.Modal.refresh();
  };

  submitAllForms = function (callBack) {
    includeForms();
    DossierMater.submitAllForms(callBack);
  };

  Main.add(function () {
    includeForms();
    DossierMater.prepareAllForms();

    {{if $grossesse->multiple && ($grossesse->nb_foetus <= 1)}}
      DossierMater.informNumberFetuse('{{$grossesse->_id}}');
    {{/if}}
  });
</script>

{{mb_include module=maternite template=inc_dossier_mater_header with_buttons=0}}

<form name="Echographie-{{$echographie->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$echographie}}
  {{mb_key   object=$echographie}}
  <input type="hidden" name="grossesse_id" value="{{$echographie->grossesse_id}}" />
  <input type="hidden" name="_count_changes" value="0" />

  <table class="main layout">
    <tr>
      <td colspan="2">
        <table class="form">
          <tr>
            <td colspan="2" class="button">
              <button type="button" class="save" onclick="submitAllForms(refreshEchographies);">
                  {{tr}}common-action-Save and close{{/tr}}
              </button>
              <button type="button" class="close" onclick="Control.Modal.close();">
                  {{tr}}Close{{/tr}}
              </button>
            </td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$echographie field=date}}</th>
            <td>
                {{mb_field object=$echographie field=date form=Echographie-`$echographie->_guid` register=true onchange="DossierMater.copyValuesOtherForm(this);"}}
            </td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$echographie field=type_echo}}</th>
            <td>{{mb_field object=$echographie field=type_echo onchange="DossierMater.copyValuesOtherForm(this);"}}</td>
          </tr>
          {{if $grossesse->multiple}}
            <tr>
              <th class="halfPane">
                <span title="{{tr}}CSurvEchoGrossesse-Chorionicity-desc{{/tr}}">{{tr}}CSurvEchoGrossesse-Chorionicity{{/tr}}</span>
              </th>
              <td>
                {{mb_field object=$echographie field=bcba form=Echographie-`$echographie->_guid` increment=true min=1 onchange="DossierMater.copyValuesOtherForm(this);"}}
                  {{mb_label object=$echographie field=bcba}}
                <br />
                {{mb_field object=$echographie field=mcma form=Echographie-`$echographie->_guid` increment=true min=1 onchange="DossierMater.copyValuesOtherForm(this);"}}
                  {{mb_label object=$echographie field=mcma}}
                <br />
                {{mb_field object=$echographie field=mcba form=Echographie-`$echographie->_guid` increment=true min=1 onchange="DossierMater.copyValuesOtherForm(this);"}}
                  {{mb_label object=$echographie field=mcba}}
              </td>
            </tr>
          {{/if}}
        </table>
      </td>
    </tr>
  </table>
</form>

{{foreach from=$foetus key=number_foetus item=_foetus}}
  <div id="enfant_{{$number_foetus}}">
    {{mb_include module=maternite template=inc_echographie_multiple echo=$_foetus}}
  </div>
{{/foreach}}
