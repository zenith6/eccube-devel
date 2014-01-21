<?php
/*
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
 */

/**
 * プラグインの情報クラス.
 *
 * @package Devel
 * @author Seiji Niita
 */
class plugin_info {
    public static $PLUGIN_CODE = "Devel";
    
    public static $PLUGIN_NAME = "開発支援プラグイン";
    
    public static $PLUGIN_VERSION = "1.1.0-dev";
    
    public static $COMPLIANT_VERSION = "2.12.0, 2.12.1, 2.12.2, 2.12.3, 2.12.4, 2.12.5, 2.12.6, 2.13.0, 2.13.1";
    
    public static $AUTHOR = "Seiji Nitta";
    
    public static $DESCRIPTION = "開発を支援する機能を追加します。";
    
    public static $AUTHOR_SITE_URL = "http://zenith6.github.io/";
    
    public static $CLASS_NAME = "Devel";
    
    public static $HOOK_POINTS = array();
    
    public static $PLUGIN_SITE_URL = "https://github.com/zenith6/eccube-devel/";
    
    public static $LICENSE = "LGPL";
}
