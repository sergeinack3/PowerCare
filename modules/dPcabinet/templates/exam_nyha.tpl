{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=$m script="exam_nyha"}}
{{assign var=consultation value=$exam_nyha->_ref_consult}}
<script type="text/javascript">

// Lancement du reload
window.opener.ExamDialog.reload('{{$consultation->_id}}');

</script>

<form name="editFrmNyha" action="" method="post" onsubmit="return checkForm(this)">

<input type="hidden" name="m" value="dPcabinet" />
<input type="hidden" name="dosql" value="do_exam_nyha_aed" />
<input type="hidden" name="del" value="0" />
{{mb_key   object=$exam_nyha}}
{{mb_field object=$exam_nyha field="consultation_id" hidden=1}}

<table class="form">
  <tr>
    {{if $exam_nyha->_id}} 
	    <th class="title modify text" colspan="3">
			  {{mb_include module=system template=inc_object_idsante400 object=$exam_nyha}}
			  {{mb_include module=system template=inc_object_history    object=$exam_nyha}}
			  {{mb_include module=system template=inc_object_notes      object=$exam_nyha}}

	      Consultation de '{{$consultation->_ref_patient}}'<br />
	      le {{$consultation->_date|date_format:$conf.longdate}}
	      par {{if $consultation->_ref_chir->isPraticien()}}le Dr{{/if}} {{$consultation->_ref_chir}}
	    </th>
    {{else}}
	    <th class="title text me-th-new" colspan="3">
	      Consultation de '{{$consultation->_ref_patient}}'<br />
	      le {{$consultation->_date|date_format:$conf.longdate}}
	      par {{if $consultation->_ref_chir->isPraticien()}}le Dr{{/if}} {{$consultation->_ref_chir}}
	    </th>
    {{/if}}
  </tr>
  <tr>
    <th><strong>Classe 1</strong></th>
    <td class="text" colspan="2">
      Patient porteur d'une cardiopathie sans limitation de l'activité physique.
      Une activité physique ordinaire n'entraîne aucun symptôme.
    </td>
  </tr>
  <tr>
    <th><strong>Classe 2</strong></th>
    <td class="text" colspan="2">
      Patient dont la cardiopathie entraîne une limitation modérée de l'activité physique
      sans gêne au repos. L'activité quotidienne ordinaire est responsable d'une fatigue,
      d'une dyspnée, de palpitations ou d'un angor.
    </td>
  </tr>
  <tr>
    <th><strong>Classe 3</strong></th>
    <td class="text" colspan="2">
      Patient dont la cardiopathie entraîne une limitation marquée de l'activité physique
      sans gêne au repos.
    </td>
  </tr>
  <tr>
    <th><strong>Classe 4</strong></th>
    <td class="text" colspan="2">
      Patient dont la cardiopathie empêche toute activité physique. Des signes d'insufisance
      cardiaque ou un angor peuvent exister même au repos.
    </td>
  </tr>
  
  <tr>
    <th class="title" colspan="3">Questionnaire</th>
  </tr>
  
  <tr>
    <th>
      <label for="q1" title=""><strong>1</strong></label>
    </th>
    <td class="text">
      <label for="q1" title="">
        Le patient peut-il descendre un étage d'escalier sans s'arrêter ?
      </label>
    </td>
    <td>
      {{mb_field object=$exam_nyha field=q1 separator="<br />" onchange="changeValue(this.name,'q2a','q3a')"}}
    </td>
  </tr>
  
  <tbody id="viewq2a" {{if $exam_nyha->q1==0}}style="display:none;"{{/if}}>
  <tr>
    <th>
      <label for="q2a" title=""><strong>2a</strong></label>
    </th>
    <td class="text">
      <label for="q2a" title="">
        Le patient peut-il monter un étage d'escalier sans s'arrêter ?<br />
        <em>ou</em><br />
        marcher d'un pas alerte sur un terrain plat<br />
        <em>ou</em><br />
        Peut-il...<br />
        jardiner, ratisser, désherber, danser (slow) ?
      </label>
    </td>
    <td>
      {{mb_field object=$exam_nyha field=q2a separator="<br />" onchange="changeValue(this.name,'q2b','')"}}
    </td>
  </tr>
  </tbody>

  <tbody id="viewq2b" {{if $exam_nyha->q2a==0}}style="display:none;"{{/if}}>
  <tr>
    <th>
      <label for="q2b" title=""><strong>2b</strong></label>
    </th>
    <td class="text">
      <label for="q2b" title="">
        Le patient peut-il monter un étage d'escalier en portant un enfant d'un an ou plus 
        (- 10 kg ou plus)<br />
        <em>ou</em><br />
        Peut-il porter en terrain plat une bouteille de butane pleine (35 kg) ou un objet plus lourd ?<br />
        <em>ou</em><br />
        Peut-il...<br />
        faire du jogging ? (1/2 heure), faire des travaux extérieurs comme bêcher la terre ? 
        S'adonner à des loisirs tels que le ski alpin, le vélo, le football, le tennis ?
      </label>
    </td>

    <td>
      {{mb_field object=$exam_nyha field=q2b separator="<br />" onchange="changeValue(this.name,'','')"}}
    </td>
  </tr>
  </tbody>

  <tbody id="viewq3a" {{if $exam_nyha->q1==1 || $exam_nyha->q1==null}}style="display:none;"{{/if}}>
  <tr>
    <th>
      <label for="q3a" title=""><strong>3a</strong></label>
    </th>
    <td class="text">
      <label for="q3a" title="">
        Le patient peut-il prendre une douche sans s'arrêter ?<br />
        <em>ou</em><br />
        peut-il marcher d'un pas tranquille sur un terrain plat (500m)<br />
        <em>ou</em><br />
        Peut-il...<br />
        faire son lit ? passer la serpillière ? étendre le linge ? laver les carreaux ?
        jouer aux boules ? (pétanque) jouer au golf ? pousser la tondeuse à gazon ?
      </label>
    </td>
    <td>
      {{mb_field object=$exam_nyha field=q3a separator="<br />" onchange="changeValue(this.name,'q3b','')"}}
    </td>
  </tr>
  </tbody>

  <tbody id="viewq3b" {{if $exam_nyha->q3a==0}}style="display:none;"{{/if}}>
  <tr>
    <th>
      <label for="q3b" title=""><strong>3b</strong></label>
    </th>
    <td class="text">
      <label for="q3b" title="">
        Le patient est-il obligé quand il s'habille de s'arrêter ?<br />
        <em>ou</em><br />
        A-t-il des symptômes<br />
        quand il mange,<br />
        quand il est debout<br />
        assis ou allongé ?
      </label>
    </td>
    <td>
      {{mb_field object=$exam_nyha field=q3b separator="<br />" onchange="changeValue(this.name,'','')"}}
    </td>
  </tr>
  </tbody>

</table>

<div style="display:none;">
  <input type="radio" name="q2a" value="" {{if $exam_nyha->q2a==""}} checked="checked" {{/if}} />
  <input type="radio" name="q2b" value="" {{if $exam_nyha->q2b==""}} checked="checked" {{/if}} />
  <input type="radio" name="q3a" value="" {{if $exam_nyha->q3a==""}} checked="checked" {{/if}} />
  <input type="radio" name="q3b" value="" {{if $exam_nyha->q3b==""}} checked="checked" {{/if}} />
</div>

<table class="form">
  <tr>
    <th><strong>Classification NYHA</strong></th>
    <td class="HalfPane" id="classeNyha">{{$exam_nyha->_classeNyha}}</td>
  </tr>
  <tr>
    <th><label for="hesitation_0">Réponses du patient sans hésitation</label></th>
    <td>
      <input name="hesitation" class="{{$exam_nyha->_props.hesitation}}" type="radio" value="0" {{if $exam_nyha->_id  && $exam_nyha->hesitation==0}} checked="checked" {{/if}}>{{tr}}CExamNyha.hesitation.0{{/tr}}
      <input name="hesitation" class="{{$exam_nyha->_props.hesitation}}" type="radio" value="1" {{if !$exam_nyha->_id || $exam_nyha->hesitation==1}} checked="checked" {{/if}}>{{tr}}CExamNyha.hesitation.1{{/tr}}
    </td>
  </tr>
  <tr>
    <td class="button" colspan="3">
      {{if $exam_nyha->examnyha_id}}
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
        <button class="trash" type="button" onclick="confirmDeletion(this.form, {typeName:'cet examen complementaire'})">{{tr}}Delete{{/tr}}</button>
      {{else}}
        <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
      {{/if}}
    </td>
  </tr>
</table>
</form>