{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=patient ajax=1}}

<script>
  Main.add(function () {
    var form = getForm('search_grossesse_modal');
    Calendar.regField(form.terme_date);
    Calendar.regField(form.terme_start);
    Calendar.regField(form.terme_end);

    Patient.form_search = form.name;

    {{if $lastname}}
    form.onsubmit();
    {{/if}}

  });
</script>

<form method="get" name="search_grossesse_modal" onsubmit="return onSubmitFormAjax(this, null, 'result_search_grossesse');">
  <input type="hidden" name="m" value="maternite" />
  <input type="hidden" name="a" value="ajax_search_grossesse" />

  <table class="main">
    <tr>
      <td class="halfPane">
        <table class="form">
          {{assign var=nom           value=""}}
          {{assign var=prenom        value=""}}
          {{assign var=naissance     value="--"}}
          {{assign var=sexe          value="f"}}
          {{assign var=cp            value=""}}
          {{assign var=ville         value=""}}
          {{assign var=patient_nda   value=""}}
          {{assign var=patient_ipp   value=""}}
          {{assign var=prat_id       value=""}}
          {{assign var=see_link_prat value=""}}
          {{assign var=board         value=0}}
          {{assign var=see_link_prat value=0}}
          {{assign var=form          value="search_grossesse_modal"}}
          {{assign var=sexe_disabled value=1}}

          {{mb_include module=patients template=inc_form_fields_search_patient}}
        </table>
      </td>
      <td style="vertical-align: top;">
        <fieldset>
          <legend>{{mb_title class=CGrossesse field=terme_prevu}}</legend>
          <label>
            Exactement : <input type="text" name="terme_date" style="display: none;" />
          </label>
          ou
          <label>
            Après <input type="text" name="terme_start" style="display: none;" />
          </label>,
          <label>
            Avant <input type="text" name="terme_end" style="display: none;" />
          </label>
        </fieldset>
        <fieldset>
          <legend>Autres options</legend>
          <label>
            {{mb_title class=CGrossesse field=multiple}}
            {{mb_field class=$grossesse field=multiple typeEnum=select emptyLabel=All}}

          </label>

          <label>
            {{mb_title class=CGrossesse field=num_semaines}}
            {{mb_field object=$grossesse field=num_semaines emptyLabel="Pas de fausse couche"}}
          </label>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button class="search">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<table class="tbl">
  <thead>
  <tr>
    <th>{{mb_title class=CGrossesse field=parturiente_id}}</th>
    <th>{{mb_title class=CGrossesse field=terme_prevu}}</th>
    <th class="narrow">{{mb_title class=CGrossesse field=multiple}}</th>
    <th>{{mb_title class=CGrossesse field=num_semaines}}</th>
    <th class="narrow">action</th>
  </tr>
  </thead>
  <tbody id="result_search_grossesse">
  <tr>
    <td class="empty" colspan="5">Effectuez une recherche avec les critères ci-dessus</td>
  </tr>
  </tbody>
</table>
