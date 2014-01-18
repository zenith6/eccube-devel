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

<!--{include file='plg_Devel_flash.tpl' flash=$flash}-->
<!--{include file='system/plg_Devel_dummy_tab.tpl'}-->

<h2>ダミーカテゴリの生成</h2>

<form name="form1" id="form1" method="post">
    <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME|h}-->" value="<!--{$transactionid|h}-->" />
    <input type="hidden" name="mode" value="execute" />
    <input type="hidden" name="context" value="<!--{$context|h}-->" />

    <table class="form">
        <tr>
            <th><!--{$form.number.title|h}--><span class="attention"> *</span></th>
            <td>
                <!--{if $form.number.error}--><span class="attention"><!--{$form.number.error}--></span><!--{/if}-->
                <input type="text" name="number" value="<!--{$form.number.value|h}-->" maxlength="<!--{$form.number.maxlength|h}-->" size="6" class="box6" <!--{if $form.number.error}--><!--{sfSetErrorStyle}--><!--{/if}--> />
            </td>
        </tr>
        <tr>
            <th><!--{$form.parent.title|h}--><span class="attention"> *</span></th>
            <td>
                <!--{if $form.parent.error}--><span class="attention"><!--{$form.parent.error}--></span><!--{/if}-->
                <select name="parent" <!--{if $form.parent.error}--><!--{sfSetErrorStyle}--><!--{/if}-->>
                    <!--{html_options options=$form.parent.options selected=$form.parent.value}-->
                </select> 以下に追加する
            </td>
        </tr>
        <tr>
            <th>カテゴリ名のランダム文字列長<span class="attention"> *</span></th>
            <td>
                <!--{if $form.min_name_length.error}--><span class="attention"><!--{$form.min_name_length.error}--></span><!--{/if}-->
                <!--{if $form.max_name_length.error}--><span class="attention"><!--{$form.max_name_length.error}--></span><!--{/if}-->
                <input type="text" name="min_name_length" value="<!--{$form.min_name_length.value|h}-->" maxlength="<!--{$form.min_name_length.maxlength|h}-->" size="6" class="box10" <!--{if $form.min_name_length.error}--><!--{sfSetErrorStyle}--><!--{/if}--> />
                ～
                <input type="text" name="max_name_length" value="<!--{$form.max_name_length.value|h}-->" maxlength="<!--{$form.max_name_length.maxlength|h}-->" size="6" class="box10" <!--{if $form.max_name_length.error}--><!--{sfSetErrorStyle}--><!--{/if}--> />
            </td>
        </tr>
        <tr>
            <th>カテゴリ名の書式</th>
            <td>
                <!--{if $form.name_prefix.error}--><span class="attention"><!--{$form.name_prefix.error}--></span><!--{/if}-->
                <!--{if $form.name_suffix.error}--><span class="attention"><!--{$form.name_suffix.error}--></span><!--{/if}-->
                <input type="text" name="name_prefix" value="<!--{$form.name_prefix.value|h}-->" maxlength="<!--{$form.name_prefix.maxlength|h}-->" size="30" class="box30" <!--{if $form.name_prefix.error}--><!--{sfSetErrorStyle}--><!--{/if}--> />
                + ランダム文字列
                + <input type="text" name="name_suffix" value="<!--{$form.name_suffix.value|h}-->" maxlength="<!--{$form.name_suffix.maxlength|h}-->" size="30" class="box30" <!--{if $form.name_suffix.error}--><!--{sfSetErrorStyle}--><!--{/if}--> />
            </td>
        </tr>
    </table>

    <div class="btn-area">
        <ul>
            <li><a class="btn-action" href="javascript:;" onclick="fnSetFormSubmit('form1', 'mode', 'execute'); return false;"><span class="btn-next">登録する</span></a></li>
        </ul>
    </div>
</form>
