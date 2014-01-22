<!--{*
 * 開発支援プラグイン
 * Copyright (C) 2014 Seiji Nitta All Rights Reserved.
 * http://zenith6.github.io/
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *}-->

<!--{include file='system/plg_Devel_console_tab.tpl'}-->

<h2>PHP の実行</h2>

<form name="form1" id="form1" method="post" target="result">
    <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME|h}-->" value="<!--{$transactionid|h}-->" />
    <input type="hidden" name="mode" value="execute" />
    <input type="hidden" name="context" value="<!--{$context|h}-->" />

    <table class="form">
        <tr>
            <th colspan="2"><!--{$form.code.title|h}--><span class="attention"> *</span></th>
        </tr>
        <tr>
            <td colspan="2">
                <textarea name="code" maxlength="<!--{$form.code.maxlength|h}-->" size="80" rows="30" class="area90" wrap="off" style="width: 100%;" <!--{if $form.code.error}--><!--{sfSetErrorStyle}--><!--{/if}-->><!--{$form.code.value|h}--></textarea>
                <!--{if $form.code.error}--><span class="attention"><!--{$form.code.error}--></span><!--{/if}-->
            </td>
        </tr>
        <tr>
            <th><!--{$form.output_format.title|h}--><span class="attention"> *</span></th>
            <td>
                <!--{html_radios name="output_format" options=$form.output_format.options selected=$form.output_format.value separator=' '}-->
                <!--{if $form.output_format.error}--><span class="attention"><!--{$form.output_format.error}--></span><!--{/if}-->
            </td>
        </tr>
        <tr>
            <th><!--{$form.transaction.title|h}--><span class="attention"> *</span></th>
            <td>
                <!--{html_radios name="transaction" options=$form.transaction.options selected=$form.transaction.value separator=' '}-->
                <!--{if $form.transaction.error}--><span class="attention"><!--{$form.transaction.error}--></span><!--{/if}-->
            </td>
        </tr>
    </table>

    <div class="btn-area">
        <ul>
            <li><a class="btn-action" href="javascript:;" onclick="fnSetFormSubmit('form1', 'mode', 'execute'); return false;"><span class="btn-next">実行する Ctrl+Enter</span></a></li>
        </ul>
    </div>
</form>

<link rel="stylesheet" type="text/css" href="<!--{$smarty.const.PLUGIN_HTML_URLPATH|h}-->Devel/codemirror/lib/codemirror.css" media="all" />
<script src="<!--{$smarty.const.PLUGIN_HTML_URLPATH|h}-->Devel/codemirror/lib/codemirror.js"></script>
<script src="<!--{$smarty.const.PLUGIN_HTML_URLPATH|h}-->Devel/codemirror/edit/matchbrackets.js"></script>
<script src="<!--{$smarty.const.PLUGIN_HTML_URLPATH|h}-->Devel/codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="<!--{$smarty.const.PLUGIN_HTML_URLPATH|h}-->Devel/codemirror/mode/xml/xml.js"></script>
<script src="<!--{$smarty.const.PLUGIN_HTML_URLPATH|h}-->Devel/codemirror/mode/javascript/javascript.js"></script>
<script src="<!--{$smarty.const.PLUGIN_HTML_URLPATH|h}-->Devel/codemirror/mode/css/css.js"></script>
<script src="<!--{$smarty.const.PLUGIN_HTML_URLPATH|h}-->Devel/codemirror/mode/clike/clike.js"></script>
<script src="<!--{$smarty.const.PLUGIN_HTML_URLPATH|h}-->Devel/codemirror/mode/php/php.js"></script>

<script>
var editor = CodeMirror.fromTextArea($("textarea[name='code']").get(0), {
    lineNumbers: true,
    matchBrackets: true,
    mode: "text/x-php",
    indentUnit: 4,
    extraKeys: {
        "Ctrl-Enter": function (editor) {
            var form = editor.getInputField().form;
            form.submit();
        }
    }
});
</script>

<h2>実行結果</h2>
<iframe id="result" name="result" width="100%" height="400"></iframe>
