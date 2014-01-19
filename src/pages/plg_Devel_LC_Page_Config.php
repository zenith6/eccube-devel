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

require_once CLASS_EX_REALDIR . 'page_extends/admin/LC_Page_Admin_Ex.php';

/**
 * プラグインの設定画面
 *
 * @package Blank
 * @author Seiji Nitta
 */
class plg_Devel_LC_Page_Config extends LC_Page_Admin_Ex {
    /**
     * @var Zenith_Eccube_PageContext
     */
    public $context;
    
    public function init() {
        parent::init();

        $this->template = TEMPLATE_ADMIN_REALDIR . 'ownersstore/plg_Devel_config.tpl';
        $this->tpl_subtitle = 'プラグイン設定';
    }
    
    public function process() {
        parent::process();
        
        $this->context = $this->restoreContext();
        
        $mode = $this->getMode();
        switch ($mode) {
            case 'save':
                $this->doSave();
                break;
                
            case 'edit':
            default:
                $this->doEdit();
                break;
        }

        $this->sendResponse();
    }
    
    /**
     * @return Zenith_Eccube_PageContext
     */
    protected function restoreContext() {
        $context = $this->createDefaultContext();
        $encoded = @$_REQUEST['context'];
        if ($encoded !== null) {
            $context->restore($encoded);
        }
        return $context;
    }
    
    /**
     * @return Zenith_Eccube_PageContext
     */
    protected function createDefaultContext() {
        $context = new Zenith_Eccube_PageContext(array(), AUTH_MAGIC);
        
        return $context;
    }
    
    /**
     * @param SC_FormParam_Ex $params
     * @param array $errors
     */
    protected function doEdit(SC_FormParam_Ex $params = null, array $errors = array()) {
        if (!$params) {
            $params = $this->buildFormParam();
            $this->setDefaultFormValues($params);
        }

        $form = $this->buildForm($params, $errors);
        $this->form = $form;
    }
    
    protected function setDefaultFormValues(SC_FormParam_Ex $params) {
        $settings = Devel::loadSettings();
        
        $params->setValue('use_holderjs', (int)$settings['use_holderjs']);
    }
    
    protected function doSave() {
        try {
            $query = SC_Query_Ex::getSingletonInstance();
            $query->begin();
            
            $params = $this->buildFormParam();
            $params->setParam($_POST);
            
            $errors = $this->validateFormParams($params);
            if ($errors) {
                $query->rollback();
                $this->doEdit($params, $errors);
                return;
            }
            
            $this->updateSettings($params);

            $query->commit();

            $this->tpl_javascript = "$(window).load(function () { alert('登録しました。'); });";
            $this->doEdit($params);
        } catch (Exception $e) {
            $query->rollback();
            
            throw $e;
        }
    }
    
    /**
     * @param SC_FormParam_Ex $params
     * @param array $errors
     * @return array
     */
    protected function buildForm(SC_FormParam_Ex $params, array $errors = array()) {
        $form = array();
        
        foreach ($params->keyname as $index => $key) {
            $form[$key] = array(
                'title' => $params->disp_name[$index],
                'value' => $params->getValue($key),
                'maxlength' => $params->length[$index],
                'error' => null,
            );
        }
        
        foreach ($errors as $key => $error) {
            $form[$key]['error'] = $error;
        }
        
        return $form;
    }
    
    /**
     * @return SC_FormParam_Ex
     */
    protected function buildFormParam() {
        $params = new SC_FormParam_Ex();

        $params->addParam('holder.js を使用する', 'use_holderjs', 1, 'n', array());
        
        return $params;
    }
    
    /**
     * @param SC_FormParam_Ex $params
     * @return array
     */
    protected function validateFormParams(SC_FormParam_Ex $params) {
        $errors = $params->checkError();

        return $errors;
    }
    
    /**
     * @param SC_FormParam_Ex $params
     */
    protected function updateSettings(SC_FormParam_Ex $params) {
        $settings = Devel::loadSettings(true);
        
        $settings['use_holderjs'] = (bool)$params->getValue('use_holderjs');
        
        Devel::saveSettings($settings);
    }
}
