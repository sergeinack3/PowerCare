{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  // Puts all checked consultation categorie object UIDs at page loading on form
  Main.add(function () {
    var consultationCategorieIds = $('consultation_categorie_ids');
    var consultationCategorieCheckedIds = [];

      {{if $plage_id}}
          {{foreach from=$consultation_categories item=_consultation_categorie}}
              {{if $_consultation_categorie->_sync_appfine}}
                  consultationCategorieCheckedIds.push({{$_consultation_categorie->_id}});
              {{/if}}
          {{/foreach}}
      {{else}}
          // On coche par défaut tous les motifs
          document.getElementsByName('categorie_selection')[0].checked = true;
          var inputs = document.getElementsByName('consultation-categorie');
          for (var i = 0; i < inputs.length; i++) {
              inputs[i].checked = true;
              inputs[i].disabled = 'disabled';
              consultationCategorieCheckedIds.push(inputs[i].value);
          }
      {{/if}}

    consultationCategorieIds.value = consultationCategorieCheckedIds.sort().join(',');
  });

  /**
   * Adds or remove ConsultationCategorie object UID from/to input tag.
   *
   * @param {HTMLInputElement} element
   */
  toggleConsultationCategorieState = function (element) {
    var consultationCategorieIds = $('consultation_categorie_ids');
    var consultationCategorieTokens = consultationCategorieIds.value.split(',');

    // Strange empty string at the beginning of array, removes it
    if (consultationCategorieTokens[0] === '') {
      consultationCategorieTokens.shift();
    }

    // If user checked this ConsultationCategorie object, adds it
    // Uf user unchecked this ConsultationCategorie object, removes it
    if (element.checked) {
      consultationCategorieTokens.push(element.value);
    } else {
      consultationCategorieTokens = consultationCategorieTokens.filter(token => element.value !== token)
    }

    // Puts joined array in input
    consultationCategorieIds.value = consultationCategorieTokens.sort().join(',');

    // Si tous les motifs sont décochés => on les recoche tous par défaut et on met "plage ouverte pour tous les motifs".
    var inputs = document.getElementsByName('consultation-categorie');
    var all_inputs_checked = false;
    for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].checked === true) {
            all_inputs_checked = true;
        }
    }

    if (!all_inputs_checked) {
        document.getElementsByName('categorie_selection')[0].checked = true;

        var all_inputs_ids = [];
        for (var j = 0; j < inputs.length; j++) {
            inputs[j].checked = true;
            inputs[j].disabled = 'disabled';
            all_inputs_ids.push(inputs[j].value);
        }
        consultationCategorieIds.value = all_inputs_ids.sort().join(',');
    }
  };
</script>

<th>{{tr}}CAppFineClient-msg-Consultation categories{{/tr}}</th>
<td>
  <div>
    <input type="radio" name="categorie_selection" {{if $plage_id && $all_synchro}}checked{{/if}} value="0" onchange="appFineClient.toggleConsultationCategorieSelection(this)"/><strong>{{tr}}appFineClient-msg-Plage available for all motif|pl{{/tr}}</strong>
    <br>
    <input type="radio" name="categorie_selection" {{if $plage_consult_categories && !$all_synchro}}checked{{/if}} value="1" onchange="appFineClient.toggleConsultationCategorieSelection(this)"/>{{tr}}appFineClient-msg-Plage available for specific motif|pl{{/tr}}
  </div>
<div>
    {{foreach from=$consultation_categories item=_consultation_categorie}}
      <input
        id="consultation-categorie-{{$_consultation_categorie->_id}}"
        class="consultation-categorie"
        name="consultation-categorie"
        type="checkbox"
        value="{{$_consultation_categorie->_id}}"
        {{if $_consultation_categorie->_sync_appfine}}checked{{/if}}
        {{if !$plage_id || !$plage_consult_categories || $all_synchro}}disabled{{/if}}
        onchange="toggleConsultationCategorieState($('consultation-categorie-{{$_consultation_categorie->_id}}')); ExercicePlace.enableCreate(this.form)"
      >
      <label for="consultation-categorie-{{$_consultation_categorie->_id}}">
          {{$_consultation_categorie->nom_categorie}}
      </label>
      <br/>
        {{foreachelse}}
      <div class="small-info">{{tr}}CConsultationCategorie-none{{/tr}}</div>
    {{/foreach}}
</div>
</td>
