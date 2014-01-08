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

<!--{if $error}-->
    <div class="result-error">
        <p><span class="attention"><!--{$error|h}--></span></p>
    </div>
<!--{/if}-->

<div class="result-output">
    <!--{$output}-->
</div>

<script>
$(window).load(function () {
    var height = document.body.scrollHeight;
    height = Math.min(400, height);
    window.parent.document.getElementById('result').height = height;
});
</script>

<style>
body, html, #popup-container {
    margin: 0;
    padding: 0;
    width: 100%;
}

#popup-header {
    display: none;
}
</style>

<!--{include file="`$smarty.const.TEMPLATE_ADMIN_REALDIR`admin_popup_footer.tpl"}-->
