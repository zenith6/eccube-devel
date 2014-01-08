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

<h2>SQL の実行</h2>

<form name="form1" id="form1" method="post">
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
            <th><!--{$form.transaction.title|h}--></th>
            <td>
                <!--{html_radios name="transaction" options=$form.transaction.options selected=$form.transaction.value separator=' '}-->
                <!--{if $form.transaction.error}--><span class="attention"><!--{$form.transaction.error}--></span><!--{/if}-->
            </td>
        </tr>
    </table>

    <div class="btn-area">
        <ul>
            <li><a class="btn-action" href="javascript:;" onclick="fnSetFormSubmit('form1', 'mode', 'execute'); return false;"><span class="btn-next">実行する</span></a></li>
        </ul>
    </div>
</form>

<!--{if $results}-->
    <h2>実行結果</h2>
    
    <!--{foreach name=result from=$results item=result}-->
        <h3>式 <!--{$smarty.foreach.result.index+1|number_format|h}--></h3>
        
        <!--{if $result.error}-->
            <p class="attention"><!--{$result.message|h}--></p>
        <!--{else}-->
            <table>
                <caption><!--{$result.statement|h|nl2br}--></caption>
                <thead>
                    <tr>
                        <!--{foreach from=$result.columns item=col}-->
                            <th><!--{$col|h}--></th>
                        <!--{/foreach}-->
                    </tr>
                </thead>
                <tbody>
                    <!--{foreach from=$result.rows item=row}-->
                        <tr>
                            <!--{foreach from=$row item=col}-->
                                <td><!--{$col|h}--></td>
                            <!--{/foreach}-->
                        </tr>
                    <!--{/foreach}-->
                </tbody>
            </table>
        <!--{/if}-->
    <!--{/foreach}-->
<!--{/if}-->
