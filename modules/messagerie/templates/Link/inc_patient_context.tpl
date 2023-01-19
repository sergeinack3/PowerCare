{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=offset value='Ox\Mediboard\Messagerie\Controllers\Legacy\MessagerieLinkController::DEFAULT_SEARCH_LIMIT'|constant}}

<div class="MessagingLinkContext">
    <div class="MessagingLinkContext-header">
        <div class="MessagingLinkContextCard">
            <div class="MessagingLinkContextCard-image">
                {{mb_include module=patients template=inc_vw_photo_identite size="50" mode="read"}}
            </div>
            <div class="MessagingLinkContextCard-content">
                <strong>
                    {{$patient->_civilite}}
                    {{$patient->nom_jeune_fille}}
                    {{$patient->prenom}}
                </strong>
                <span>
                    {{$patient->_age}}
                    <span>
                        - {{$patient->naissance|date_format:$conf.date}}
                    </span>
                </span>
            </div>
        </div>
    </div>
    <div class="MessagingLinkContext-list">
        <div class="MessagingLinkContext-section">
            <div class="MessagingLinkContext-sectionRoot">
                <input type="radio"
                       name="radioItem"
                       checked
                       data-id="{{$patient->_guid}}"
                       id="radio-patient"
                       onchange="MessagingLink.checkLink();">
                <label for="radio-patient">
                    <strong>
                        {{tr}}CPatient-Patient folder{{/tr}}
                    </strong>
                </label>
            </div>
        </div>
        {{if $hospitalizations && $hospitalizations.count > 0}}
            <div class="MessagingLinkContext-section">
                <span class="MessagingLinkContext-sectionTitle">
                    {{if $hospitalizations.count > 1}}
                        {{tr}}CSejour|pl{{/tr}}
                    {{else}}
                        {{tr}}CSejour{{/tr}}
                    {{/if}}
                </span>
                {{mb_include module=messagerie template="Link/Context/inc_hospitalizations_context" hospitalizations=$hospitalizations }}
                {{if $hospitalizations.count > $offset}}
                    <div class="MessagingLinkContext-sectionAction">
                        <button type="button" class="me-tertiary"
                                title="{{tr}}CMessagingLink-Title-Load more hospitalizations{{/tr}}"
                                onclick="MessagingLink.showMorePatientContext(this, 'CSejour', '{{$offset}}', '{{$hospitalizations.count-$offset}}')">
                            <i class="mdi mdi-chevron-down mdi-18px"></i>
                            <span style="vertical-align: top">
                                {{tr var1=$hospitalizations.count-$offset}}
                                    CMessagingLink-Title-More results (%s)
                                {{/tr}}
                            </span>
                        </button>
                    </div>
                {{/if}}
            </div>
        {{/if}}
        {{if $consultations && $consultations.count > 0}}
            <div class="MessagingLinkContext-section">
                <span class="MessagingLinkContext-sectionTitle">
                    {{if $consultations.count > 1}}
                        {{tr}}CConsultation|pl{{/tr}}
                    {{else}}
                        {{tr}}CConsultation{{/tr}}
                    {{/if}}
                </span>
                {{mb_include module=messagerie template="Link/Context/inc_consultations_context" consultations=$consultations }}
                {{if $consultations.count > $offset}}
                    <div class="MessagingLinkContext-sectionAction">
                        <button type="button" class="me-tertiary"
                                title="{{tr}}CMessagingLink-Title-Load more consultations{{/tr}}"
                                onclick="MessagingLink.showMorePatientContext(this, 'CConsultation', '{{$offset}}', '{{$consultations.count-$offset}}')">
                            <i class="mdi mdi-chevron-down mdi-18px"></i>
                            <span style="vertical-align: top">
                                {{tr var1=$consultations.count-$offset}}
                                    CMessagingLink-Title-More results (%s)
                                {{/tr}}
                            </span>
                        </button>
                    </div>
                {{/if}}
            </div>
        {{/if}}
        {{if $events && $events.count > 0}}
            <div class="MessagingLinkContext-section">
                <span class="MessagingLinkContext-sectionTitle">
                    {{if $events.count > 1}}
                        {{tr}}CMessagingLink-Title-PatientEvent|pl{{/tr}}
                    {{else}}
                        {{tr}}CMessagingLink-Title-PatientEvent{{/tr}}
                    {{/if}}
                </span>
                {{mb_include module=messagerie template="Link/Context/inc_events_context" events=$events }}
                {{if $events.count > $offset}}
                    <div class="MessagingLinkContext-sectionAction">
                        <button type="button" class="me-tertiary"
                                title="{{tr}}CMessagingLink-Title-Load more events{{/tr}}"
                                onclick="MessagingLink.showMorePatientContext(this, 'CEvenementPatient', '{{$offset}}', '{{$events.count-$offset}}')">
                            <i class="mdi mdi-chevron-down mdi-18px"></i>
                            <span style="vertical-align: top">
                                {{tr var1=$events.count-$offset}}
                                    CMessagingLink-Title-More results (%s)
                                {{/tr}}
                            </span>
                        </button>
                    </div>
                {{/if}}
            </div>
        {{/if}}
    </div>
</div>
