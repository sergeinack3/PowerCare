{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=need_tab value=false}}
{{assign var=counter_max value=$start+$step}}

<script>
  changePage = function (page) {
    var form = getForm("modlang");
    $V(form.elements.start, page);
    form.onsubmit();
  }
</script>

<div id="translations-container">
  <form action="?m={{$m}}" name="modlang" method="get" onsubmit="return onSubmitFormAjax(this, null, 'translations-container')">

  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="a" value="displayTranslations" />
  <input type="hidden" name="start" value="0" />

  <table class="form" style="width: 50%; float: left;">
    <tr>
      <th>
        <label for="module" title="Filtrer le module de traduction">{{tr}}CModule{{/tr}}</label>
      </th>
      <td>
        <select name="module" class="notNull" onchange="this.form.onsubmit()">
        {{foreach from=$modules item=_module}}
        <option value="{{$_module}}" {{if $_module == $module}} selected="selected" {{/if}}>
          {{if $_module == "common"}}&mdash;{{/if}}
          {{tr}}module-{{$_module}}-court{{/tr}}
        </option>
        {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>
        <label for="language">Language</label>
      </th>
      <td>
        <select name="language" class="notNull" onchange="this.form.onsubmit();">
        {{foreach from=$locales item=_locale}}
        <option value="{{$_locale}}" {{if $language == $_locale}}selected="selected"{{/if}}>
          {{tr}}language.{{$_locale}}{{/tr}}</option>
        {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>
        <label for="reference">Reference</label>
      </th>
      <td>
        <select name="reference" onchange="this.form.onsubmit();">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$locales item=_locale}}
          <option value="{{$_locale}}" {{if $reference == $_locale}}selected="selected"{{/if}}>
            {{tr}}language.{{$_locale}}{{/tr}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
  </table>

  </form>

  <script type="text/javascript">
  Main.add(function () {
    getForm('modlang').elements.module.sortByLabel();
  });

  </script>

  <!-- Encouragements -->
  <div style="width: 50%; float: right;">
    {{if $completion == 0}}
    <div class="small-error">
      <strong>Attention...</strong>
      <div>Ce module n'est absolument pas localisé, on ne peut lui garantir un affichage utilisable.</div>
      <div>
        Il compte un total de <strong>{{$local_count|integer}}</strong> termes à traduire.
      </div>
    </div>
    {{elseif $completion < 50}}
    <div class="small-warning">
      <strong>Important</strong>
      <div>Ce module est peu traduit, cela va probablement poser des problèmes d'affichage.</div>
      <div>
        Il compte <strong>{{$local_count|integer}}</strong> localisations
        sur un total de <strong>{{$total_count|integer}}</strong> termes,
        soit une complétude de <strong>{{$completion}}%</strong>.
      </div>
    </div>
    {{elseif $completion < 100}}
    <div class="small-info">
      <strong>Important</strong>
      <div>Ce module est en cours de traduction, c'est un bon début, il reste encore des efforts à faire !</div>
      <div>
        Il compte <strong>{{$local_count|integer}}</strong> localisations
        sur un total de <strong>{{$total_count|integer}}</strong> termes,
        soit une complétude de <strong>{{$completion}}%</strong>.
      </div>
    </div>
    {{else}}
    <div class="small-success">
      <strong>Félicitations !</strong>
      <div>Ce module est totalement traduit, ce qui est un gage de qualité manifeste !</div>
      <div>Il compte un total de <strong>{{$local_count|integer}}</strong> termes parfaitement traduits.</div>
    </div>
    {{/if}}

  </div>

  <script>
    Main.add(Control.Tabs.create.curry("tab-classes", true, {
      afterChange: function (container) {
        ViewPort.SetAvlHeight(container.down('div.available-height'), 1);
      }
    }));

    enableSubmit = function(textarea) {
      textarea.form.down('button.modify').enable();
    };

    renameTextarea = function(input) {
      $(input).up().next().select('input,textarea').each(function(textarea) {
        textarea.name='s['+input.value+']'}
      )
    }
  </script>

  <table class="main layout">
    <tr>
      <td class="narrow">
        <script>
          Main.add(ViewPort.SetAvlHeight.curry('tab-classes', 1));
        </script>
        <!-- Control tabs -->
        <ul id="tab-classes" class="control_tabs_vertical small" style="float: left; width: 20em;">
          {{foreach from=$items key=class item=_item}}
          <li>
            <a href="#class-{{$class}}" {{if $completions.$class.percent < 100}} class="wrong" {{/if}}>
              {{if $class == "Other" || $class == "Action" || $class == "Config"}}Module: {{/if}}
              {{tr}}{{$class}}{{/tr}}
              <small>({{$completions.$class.percent}}%)</small>
            </a>
          </li>
          {{/foreach}}
        </ul>
      </td>

      <td>
        <!-- Modification des items -->
        {{foreach from=$items key=class item=_item}}

          {{if $class == 'Other' && $counter_total > 500}}
            {{assign var=counter value=0}}
            {{assign var=need_tab value=true}}

            {{mb_include module=system template=inc_pagination current=$start step=$step total=$counter_total change_page="changePage"}}
          {{/if}}

          <form action="?m=developpement" name="translate-{{$class}}" method="post" class="prepared"
                onsubmit="return onSubmitFormAjax(this);">

            <input type="hidden" name="m" value="developpement" />
            <input type="hidden" name="tab" value="displayTranslations" />
            <input type="hidden" name="module" value="{{$module}}" />
            <input type="hidden" name="language" value="{{$language}}" />
            <input type="hidden" name="dosql" value="do_translate_aed" />

            <div id="class-{{$class}}" class="tbl" style="display: none;">

              <button type="submit" class="modify oneclick" disabled="true" style="float: right;">{{tr}}Save{{/tr}}: {{$class}}</button>

              <div class="available-height" style="clear: both;">

              <table class="tbl">
                <tr>
                  <th class="title" style="width: 33%;">Stringt<t/th>
                  {{if $reference !== $language}}
                  <th class="title" style="width: 33%;">Reference: {{tr}}language.{{$reference}}{{/tr}}</th>
                  {{/if}}
                  <th class="title" colspan="2" style="width: 33%;">Language: {{tr}}language.{{$language}}{{/tr}}</th>
                </tr>

                {{foreach from=$_item key=nom item=tabTrad}}
                  <tbody class="hoverable">
                  <tr>
                    <th class="section" colspan="5">
                      {{$class}} : {{$nom}}
                    </th>
                  </tr>

                {{foreach from=$tabTrad key=chaine item=trad name=trad}}
                  {{if $need_tab}}
                    {{assign var=counter value=$counter+1}}
                  {{/if}}

                  {{if (!$need_tab) || ($need_tab && $counter < $counter_max && $counter > $start)}}
                    <tr>
                      <td>{{$chaine}}</td>

                      {{if $reference !== $language}}
                        <td class="text">{{if (isset($ref_items.$chaine|smarty:nodefaults))}}<em>{{$ref_items.$chaine}}</em>{{/if}}</td>
                      {{/if}}

                      {{assign var=sanitized_trad value=$trad|replace:'|overwrite|':''}}
                      <td class="narrow">
                        {{if $sanitized_trad|smarty:nodefaults !== $trad}}
                            <i class="me-icon me-warning warning" title="{{tr}}CTranslationOverwrite{{/tr}}"></i>
                        {{/if}}
                      </td>
                      <td>
                        <textarea name="s[{{$chaine}}]" onchange="enableSubmit(this);">{{$sanitized_trad|smarty:nodefaults}}</textarea>
                      </td>
                    </tr>
                  {{/if}}
                {{/foreach}}
                </tbody>
                {{/foreach}}

                {{if array_key_exists($class, $archives)}}
                <tr>
                  <td colspan="10">
                    <div class="small-info">
                      Cette classe est une classe d'archive, toutes ces traductions héritent de sa classe parente.
                    </div>
                  </td>
                </tr>
                {{/if}}

                <!-- Anonnymous entries -->
                {{if $class == "Other"}}
                <tr>
                  <th class="section" colspan="5">
                    {{$class}} : {{tr}}Anonymous{{/tr}}
                  </th>
                </tr>
                {{foreach from=1|range:5 item=m}}
                <tr>
                  <td>
                    <input type="text" name="empty_locales" value="" onchange="renameTextarea(this);" />
                  </td>
                  <td>
                    <textarea name="_" rows="2" onchange="enableSubmit(this);"></textarea>
                  </td>
                </tr>
                {{/foreach}}
                {{/if}}

              </table>
            </div>

            </div>
          </form>

        {{/foreach}}
      </td>
    </tr>
  </table>
</div>
