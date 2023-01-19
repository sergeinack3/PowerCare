{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="questions-container">
    {{foreach from=$invoice->questions item=question}}
        <div id="question-{{$question->id}}-container" class="jfse-question">
            <form name="question-{{$question->id}}" method="post" action="?" onsubmit="return false">
                <input type="hidden" name="question_id" value="{{$question->id}}">
                <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
                <input type="hidden" name="nature" value="{{$question->nature}}">

                <div class="small-info" style="text-align: center;">
                    {{$question->question}}
                    <br>
                    {{if $question->type == 2 && $question->nature == 7}}
                        <input type="hidden" name="answer" value="{{$question->answer}}" class="date notNull">
                        <script type="text/javascript">
                            Main.add(() => {
                                Calendar.regField(getForm('question-{{$question->id}}').elements['answer']);
                            });
                        </script>
                    {{elseif $question->type == 2}}
                        <input type="text" name="answer" value="{{$question->answer}}">
                    {{elseif $question->type == 1}}
                        <select name="answer">
                            <option value="">&mdash; {{tr}}Select{{/tr}}</option>
                            {{foreach from=$question->possible_answers key=value item=text}}
                                <option value="{{$value}}"{{if $question->answer == $value}} selected="selected"{{/if}}>{{$text}}</option>
                            {{/foreach}}
                        </select>
                    {{else}}
                        <label>
                            {{tr}}Yes{{/tr}}
                            <input type="radio" name="answer" value="1"{{if $question->answer == '1'}} checked="checked"{{/if}}/>
                        </label>
                        <label>
                            {{tr}}No{{/tr}}
                            <input type="radio" name="answer" value="0"{{if $question->answer == '0'}} checked="checked"{{/if}}/>
                        </label>
                    {{/if}}
                </div>
            </form>
        </div>
    {{/foreach}}
    <div style="text-align: center; margin-bottom: 5px;">
        <button type="button" class="tick" onclick="Invoicing.sendQuestionsAnswers('{{$invoice->id}}');">{{tr}}Validate{{/tr}}</button>
    </div>
</div>

<script type="text/javascript">
    Main.add(function() {
        Invoicing.displayQuestions();
    });
</script>
