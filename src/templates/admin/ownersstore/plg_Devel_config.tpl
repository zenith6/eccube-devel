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

<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_header.tpl"}-->

<h2><!--{$tpl_subtitle|h}--></h2>

<form name="form1" id="form1" method="post" action="<!--{$smarty.server.REQUEST_URI|h}-->">
    <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME|h}-->" value="<!--{$transactionid|h}-->" />
    <input type="hidden" name="mode" value="save" />
    <input type="hidden" name="context" value="<!--{$context|h}-->" />

    <div class="form">
        <h3>設定</h3>
        
        <table class="form">
            <tr>
                <th>ダミー画像</th>
                <td>
                    <!--{if $form.use_holderjs.error}--><span class="attention"><!--{$form.use_holderjs.error}--></span><!--{/if}-->
                    <label><input type="checkbox" name="use_holderjs" value="1" <!--{if $form.use_holderjs.value}-->checked="checked"<!--{/if}--> <!--{if $form.use_holderjs.error}--><!--{sfSetErrorStyle}--><!--{/if}--> /> <!--{$form.use_holderjs.title|h}--></label><br />
                    ダミー商品等で holder.js を使用している場合に有効にして下さい。
                </td>
            </tr>
        </table>
        
        <div class="btn-area">
            <ul>
                <li>
                    <a class="btn-action" href="#" onclick="window.close(); return false;"><span class="btn-next">閉じる</span></a>
                </li>
                <li>
                    <a class="btn-action" href="#" onclick="document.form1.submit(); return false;"><span class="btn-next">登録する</span></a>
                </li>
            </ul>
        </div>
    </div>
</form>

<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_footer.tpl"}-->
