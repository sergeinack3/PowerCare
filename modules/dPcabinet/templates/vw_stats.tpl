{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=stats ajax=$ajax}}

<script type="text/javascript">
  Main.add(function() {
    Control.Tabs.create('tabs_stats', true);
    Stats.init('FilterMedCorrespondants');
  });
</script>

<ul id="tabs_stats" class="control_tabs">
  <li><a href="#nb_consults">Nombre de consultations</a></li>
  <li><a href="#prise_rdv">Prises de RDV</a></li>
  <li><a href="#medecins_correspondants">Médecins correspondants / adressants</a></li>
</ul>
  
<div id="nb_consults" style="display: none;">
  <form name="FilterNbConsults" action="?" method="get" onsubmit="if (checkForm(this)) { return onSubmitFormAjax(this, null, 'refresh_nb_consults') }">
    <input type="hidden" name="m" value="dPcabinet" />
    <input type="hidden" name="a" value="ajax_stats_nb_consults" />
  
    <table class="form">
      <tr>
        <th>{{mb_label object=$filter field=_function_id}}</th>
        <td>
          <select name="_function_id" onchange="$V(this.form._user_id, '', false)">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{mb_include module=mediusers template=inc_options_function list=$functions selected=$filter->_function_id}}
          </select>
        </td>
    
        <th>{{mb_label object=$filter field=_date_min}}</th>
        <td>{{mb_field object=$filter field=_date_min form=FilterNbConsults register=true canNull=false onchange="Stats.checkMaxPeriod(this)"}}</td>
      </tr>
      
      <tr>
        <th>{{mb_label object=$filter field=_other_function_id}}</th>
        <td>
          <select name="_other_function_id" class="{{$filter->_props._other_function_id}}">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{mb_include module=mediusers template=inc_options_function list=$functions selected=$filter->_other_function_id}}
          </select>
        </td>
    
        <th>{{mb_label object=$filter field=_date_max}}</th>
        <td>{{mb_field object=$filter field=_date_max form=FilterNbConsults register=true canNull=false onchange="Stats.checkMaxPeriod(this)"}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$filter field=_user_id}}</th>
        <td colspan="3">
          <select name="_user_id" onchange="$V(this.form._function_id, '', false)">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{mb_include module=mediusers template=inc_options_mediuser list=$users selected=$filter->_user_id show_type=1}}
          </select>
        </td>
      </tr>
      <tr>
        <td class="button" colspan="4">
          <button type="submit" class="change me-primary">{{tr}}Compute{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>

  <div id="refresh_nb_consults"></div>
</div>

<div id="prise_rdv" style="display: none;">
  <form name="FilterPriseRdv" action="?" method="get" onsubmit="if (checkForm(this)) { return onSubmitFormAjax(this, null, 'refresh_prise_rdv')}">
    <input type="hidden" name="m" value="dPcabinet" />
    <input type="hidden" name="a" value="ajax_stats_prise_rdv" />

    <table class="form">
      <tr>
        <th>{{mb_label object=$filter field=_user_id}}</th>
        <td>
          <select name="_user_id">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{mb_include module=mediusers template=inc_options_mediuser list=$users selected=$filter->_user_id show_type=1}}
          </select>
        </td>
    
        <th>{{mb_label object=$filter field=_date_min}}</th>
        <td>{{mb_field object=$filter field=_date_min form=FilterPriseRdv register=true canNull=false onchange="Stats.checkMaxPeriod(this)"}}</td>
      </tr>
      
      <tr>
        <th>{{mb_label class=CConsultation field="demande_nominativement"}}</th>
        <td>{{mb_field class=CConsultation field="demande_nominativement"}}</td>

        <th>{{mb_label object=$filter field=_date_max}}</th>
        <td>{{mb_field object=$filter field=_date_max form=FilterPriseRdv register=true canNull=false onchange="Stats.checkMaxPeriod(this)"}}</td>
      </tr>
  
      <tr>
        <td class="button" colspan="4">
          <button type="submit" class="change me-primary">{{tr}}Compute{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
  <div id="refresh_prise_rdv"></div>
</div>

<div id="medecins_correspondants" style="display: none;">
  <form name="FilterMedCorrespondants">

      <table class="form">
        <tr>
          <th>{{mb_label object=$filter field=_function_id}}</th>
          <td>
            <select name="_function_id" onchange="$V(this.form._user_id, '', false)">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{mb_include module=mediusers template=inc_options_function list=$functions selected=$filter->_function_id}}
            </select>
          </td>
        </tr>

        <tr>
          <th>{{mb_label object=$filter field=_user_id}}</th>
          <td style="vertical-align: top">
            <select name="_user_id" onchange="$V(this.form._function_id, '', false)">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{mb_include module=mediusers template=inc_options_mediuser list=$users selected=$filter->_user_id show_type=1}}
            </select>
          </td>
        </tr>

        <tr>
          <th>{{tr}}base-on-doctors{{/tr}}</th>
          <td>
            <select name="doctors">
              <option value="gp">{{tr}}correspondent-patients{{/tr}}</option>
              <option value="addressed">{{tr}}direct-patient-appointment{{/tr}}</option>
            </select>
          </td>
        </tr>

        <tr class="addressed-dates" style="display: none;">
          <th>{{mb_label object=$filter field=_date_min}}</th>
          <td>{{mb_field object=$filter field=_date_min form=FilterMedCorrespondants register=true canNull=false}}</td>
        </tr>

        <tr class="addressed-dates" style="display: none;">
          <th>{{mb_label object=$filter field=_date_max}}</th>
          <td>{{mb_field object=$filter field=_date_max form=FilterMedCorrespondants register=true canNull=false}}</td>
        </tr>

        <tr>
          <td></td>
          <td>
            <button type="button" class="corresponding-compute change me-primary">{{tr}}Compute{{/tr}}</button>
            <button type="button" class="corresponding-download download">{{tr}}Download{{/tr}} (CSV)</button>
          </td>
        </tr>
      </table>
  </form>
  
  <div id="refresh_medecins_correspondants"></div>
</div>

