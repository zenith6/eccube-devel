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

<h2>ダミー商品の生成</h2>

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
    </table>

    <div class="btn-area">
        <ul>
            <li><a class="btn-action" href="javascript:;" onclick="fnSetFormSubmit('form1', 'mode', 'execute'); return false;"><span class="btn-next">登録する</span></a></li>
        </ul>
    </div>
</form>
