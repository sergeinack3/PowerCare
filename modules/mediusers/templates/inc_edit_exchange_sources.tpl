{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create("tab_edit_exchange_source", true);
  });
</script>

<ul id="tab_edit_exchange_source" class="control_tabs small me-no-border-top">
  <li ><a href="#edit-e-mails">{{tr}}CExchangeSource.e-mails{{/tr}}</a></li>
  <li><a href="#edit-archiving">{{tr}}CExchangeSource.archiving{{/tr}}</a></li>
</ul>

<div id="edit-e-mails" style="display: none;">
  <script>
    Main.add(Control.Tabs.create.curry("tab_edit_emails", true));
  </script>

  {{* On ne peut avoir qu'une seule source SMTP *}}
  <fieldset>
    <legend>
      {{tr}}CExchangeSource.smtp-desc{{/tr}}

      {{assign var=smtp_source value=$smtp_sources.0}}

      {{if !$smtp_source->_id}}
      <button class="add notext"
              onclick="ExchangeSource.editSource('{{$smtp_source->_guid}}', true, '{{$smtp_source->name}}',
                '{{$smtp_source->_wanted_type}}', null, ExchangeSource.refreshUserSources)">
        {{tr}}CSourceSMTP.new{{/tr}}
      </button>
      {{/if}}
    </legend>

    {{mb_include module=system template=inc_vw_list_sources sources=$smtp_sources}}
  </fieldset>

  <fieldset class="me-margin-top-8">
    <legend>
      {{tr}}CExchangeSource.pop-desc{{/tr}}

      {{if "messagerie"|module_active}}
        <button class="add notext"
                onclick="ExchangeSource.editSource('{{$new_source_pop->_guid}}', true,
                  '{{$new_source_pop->name}}', '{{$new_source_pop->_wanted_type}}',
                  '{{$new_source_pop->object_class}}-{{$new_source_pop->object_id}}')">
          {{tr}}CSourcePOP.new{{/tr}}
        </button>
      {{/if}}
    </legend>

    {{if !"messagerie"|module_active}}
      {{mb_include module=system template=module_missing mod=messagerie}}
    {{else}}
      {{mb_include module=system template=inc_vw_list_sources sources=$pop_sources}}
    {{/if}}
  </fieldset>
</div>

<div id="edit-archiving" style="display: none;">
  <fieldset>
    <legend>
      {{tr}}CExchangeSource.archiving-desc{{/tr}}

      {{assign var=archiving_source value=$archiving_sources.0}}

      {{if !$archiving_source->_id}}
        <button class="add notext"
                onclick="ExchangeSource.editSource('{{$archiving_source->_guid}}', true, '{{$archiving_source->name}}',
                  '{{$archiving_source->_wanted_type}}', null, ExchangeSource.refreshUserSources)">
          {{tr}}CSourceFTP.new{{/tr}}
        </button>
      {{/if}}
    </legend>

    {{mb_include module=system template=inc_vw_list_sources sources=$archiving_sources}}
  </fieldset>
</div>
