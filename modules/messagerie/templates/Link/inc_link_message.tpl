{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{* Scripts *}}
{{mb_script module=messagerie script=messagerie_link ajax=true}}
{{mb_script module=patients   script=pat_selector    ajax=true}}
{{mb_script module=files      script=file            ajax=true}}

<script>
    Main.add(function() {
        MessagingLink.initPatientSearch('_patient_search');

        PatSelector.init = function() {
            this.sForm = "search-menu";
            this.sId   = "patient_id";
            this.pop();
        }
    });
</script>

<div class="MessagingLink">
    <div class="MessagingLink-search">
        <div class="MessagingLink-searchMenu">
            <form name="search-menu" method="get" onsubmit="return MessagingLink.submitSearch(this);">
                <input type="hidden" name="m"          value="messagerie"/>
                <input type="hidden" name="a"          value="showPatientContext"/>
                <input type="hidden" name="patient_id" value="" onchange="this.form.onsubmit();"/>
                <div class="MessagingLink-searchTitle">
                    {{tr}}fast-search{{/tr}}
                </div>
                <div class="MessagingLink-searchField">
                    <label class="MessagingLink-searchLabel" for="_patient_search">
                        {{tr}}CMessagingLink-Search-Patient{{/tr}}
                    </label>
                    <div class="MessagingLink-searchInput">
                        {{me_form_field}}
                            <input type="text" name="_patient_search">
                            <button type="button" class="me-tertiary lookup notext"
                                    title="{{tr}}CMessagingLink-Title-Advanced search{{/tr}}"
                                    onclick="PatSelector.init();">
                            </button>
                        {{/me_form_field}}
                    </div>
                </div>
            </form>
        </div>
        <div id="result-search" class="MessagingLink-searchResult">
            <div class="MessagingLink-empty">
                <div class="MessagingLink-emptyTitle">
                    {{tr}}CMessagingLink-Msg-Select patient before{{/tr}}
                </div>
            </div>
        </div>
    </div>
    <div class="MessagingLinkList">
        <div class="MessagingLinkList-header">
            <div class="MessagingLinkList-preaction">
                <input type="checkbox"
                       name="checkboxItems"
                       title="{{tr}}CMessagingLink-Title-Select all items{{/tr}}"
                       onchange="MessagingLink.selectAttachments(this)">
            </div>
            <div class="MessagingLinkList-title">
                <strong>
                    {{tr}}CMessagingLink-Title-Available items{{/tr}}
                </strong>
                {{if $count > 1}}
                    <span>
                        {{$count}} {{tr}}CMessagingLink-Title-Item|pl{{/tr}}
                    </span>
                {{else}}
                    <span>
                        {{$count}} {{tr}}CMessagingLink-Title-Item{{/tr}}
                    </span>
                {{/if}}
            </div>
            <div class="MessagingLinkList-action">
                <button type="button" class="me-primary" id="buttonLink"
                        title="{{tr}}CMessagingLink-Title-Link file{{/tr}}"
                        disabled>
                    <i class="mdi mdi-paperclip mdi-18px"></i>
                    <span style="vertical-align: top">{{tr}}CMessagingLink-Action-Link file{{/tr}}</span>
                </button>
            </div>
        </div>
        <div class="MessagingLinkList-content">
            <div class="MessagingLinkList-contentField">
                <div class="MessagingLinkList-contentTitle">
                    {{tr}}CMessagingLink-Title-Mail{{/tr}}
                </div>
                <div class="MessagingLinkList-contentResult">
                    {{mb_include module=messagerie template="Link/Card/inc_mail_card" mail=$mail}}
                </div>
            </div>
            <div class="MessagingLinkList-contentField scrollFlex">
                <div class="MessagingLinkList-contentTitle">
                    {{if $attachments|@count > 1}}
                        {{tr}}CMessagingLink-Title-Attachment|pl{{/tr}}
                    {{else}}
                        {{tr}}CMessagingLink-Title-Attachment{{/tr}}
                    {{/if}}
                </div>
                <div class="MessagingLinkList-contentResult">
                    {{foreach from=$attachments item=attachment}}
                        {{mb_include module=messagerie template="Link/Card/inc_attachment_card" attachment=$attachment categories=$categories}}
                    {{foreachelse}}
                        <div class="MessagingLinkList-empty">
                            <div class="MessagingLinkList-emptyTitle">
                                {{tr}}CMessagingLink.none{{/tr}}
                            </div>
                            <div class="MessagingLinkList-emptyImage">
                                <img src="./modules/messagerie/images/empty_attachment_list.svg" alt="{{tr}}Empty{{/tr}}"/>
                            </div>
                        </div>
                    {{/foreach}}
                </div>
            </div>
        </div>
    </div>
</div>
