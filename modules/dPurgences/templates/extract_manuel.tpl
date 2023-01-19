{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=error value=false}}

{{if $error}}
  <div class="warning">
    {{$error}}
  </div>

  {{mb_return}}
{{/if}}


<script>
  Main.add(function () {
    $('encrypt_rpu').disabled = true;
    $('transmit_rpu').disabled = true;

    {{if in_array('activite', $types)}}
      $('encrypt_activite').disabled = true;
      $('transmit_activite').disabled = true;
    {{/if}}

    {{if in_array('urg', $types)}}
      $('encrypt_urg').disabled = true;
      $('transmit_urg').disabled = true;
    {{/if}}

    {{if in_array('uhcd', $types)}}
      $('encrypt_uhcd').disabled = true;
      $('transmit_uhcd').disabled = true;
    {{/if}}
  
    {{if in_array('tension', $types)}}
      $('encrypt_tension').disabled = true;
      $('transmit_tension').disabled = true;
    {{/if}}
    
    {{if in_array('deces', $types)}}
      $('encrypt_deces').disabled = true;
      $('transmit_deces').disabled = true;
    {{/if}}

    {{if in_array('litsChauds', $types)}}
      $('encrypt_litsChauds').disabled = true;
      $('transmit_litsChauds').disabled = true;
    {{/if}}
  });

  Main.add(Control.Tabs.create.curry('tabs-extract', true));
</script>

<ul id="tabs-extract" class="control_tabs">
  {{if in_array('rpu', $types)}}
    <li><a href="#RPU">{{tr}}extract-rpu{{/tr}}</a></li>
  {{/if}}
  {{if in_array('uhcd', $types)}}
    <li><a href="#UHCD">{{tr}}extract-uhcd{{/tr}}</a></li>
  {{/if}}
  {{if in_array('urg', $types)}}
    <li><a href="#URG">{{tr}}extract-urg{{/tr}}</a></li>
  {{/if}}
  {{if in_array('activite', $types)}}
    <li><a href="#ACTIVITE">{{tr}}extract-activite{{/tr}}</a></li>
  {{/if}}
  {{if in_array('tension', $types)}}
    <li><a href="#TENSION">{{tr}}extract-tension{{/tr}}</a></li>
  {{/if}}
  {{if in_array('deces', $types)}}
    <li><a href="#DECES">{{tr}}extract-deces{{/tr}}</a></li>
  {{/if}}
  {{if in_array('litsChauds', $types)}}
    <li><a href="#LITSCHAUDS">{{tr}}extract-litsChauds{{/tr}}</a></li>
  {{/if}}
</ul>

{{if in_array('rpu', $types)}}
<div id="RPU" style="display: none;" class="me-padding-0">
  {{mb_include template=inc_extract type="rpu"}}
</div>
{{/if}}

{{if in_array('uhcd', $types)}}
  <div id="UHCD" style="display: none;" class="me-padding-0">
    {{mb_include template=inc_extract type="uhcd"}}
  </div>
{{/if}}

{{if in_array('urg', $types)}}
<div id="URG" style="display: none;" class="me-padding-0">
  {{mb_include template=inc_extract type="urg"}}
</div>
{{/if}}

{{if in_array('activite', $types)}}
  <div id="ACTIVITE" style="display: none;" class="me-padding-0">
    {{mb_include template=inc_extract type="activite"}}
  </div>
{{/if}}

{{if in_array('tension', $types)}}
  <div id="TENSION" style="display: none;" class="me-padding-0">
    {{mb_include template=inc_extract type="tension"}}
  </div>
{{/if}}

{{if in_array('deces', $types)}}
  <div id="DECES" style="display: none;" class="me-padding-0">
    {{mb_include template=inc_extract type="deces"}}
  </div>
{{/if}}

{{if in_array('litsChauds', $types)}}
  <div id="LITSCHAUDS" style="display: none;" class="me-padding-0">
    {{mb_include template=inc_extract type="litsChauds"}}
  </div>
{{/if}}