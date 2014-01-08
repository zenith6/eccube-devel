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
        $serialized = @$_REQUEST['context'];
        if ($serialized !== null) {
            $context = Zenith_Eccube_PageContext::restore($serialized);
        } else {
            $context = $this->createContext();
        }
        return $context;
    }
    
    /**
     * @return Zenith_Eccube_PageContext
     */
    protected function createContext() {
        $context = new Zenith_Eccube_PageContext();
        
        return $context;
    }
    
    protected function doEdit($errors = array()) {
        $params = $this->buildFormParam($this->context);
        $form = $this->buildForm($params, $errors);
        $this->form = $form;
    }
    
    protected function doSave() {
        try {
            $query = SC_Query_Ex::getSingletonInstance();
            $query->begin();
            
            $params = $this->buildFormParam($this->context);
            $params->setParam($_POST);
            
            $errors = $this->validateFormParam($params, $this->context);
            if ($errors) {
                $query->rollback();
                $this->doEdit($errors);
                return;
            }

            $query->commit();

            $this->tpl_javascript = "$(window).load(function () { alert('登録しました。'); });";
            $this->doEdit();
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
    protected function buildForm(SC_FormParam_Ex $params, $errors = array()) {
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
     * @param Zenith_Eccube_PageContext $context
     * @return SC_FormParam_Ex
     */
    protected function buildFormParam(Zenith_Eccube_PageContext $context) {
        $params = new SC_FormParam_Ex();
        
        return $params;
    }
    
    /**
     * @param SC_FormParam_Ex $params
     * @param Zenith_Eccube_PageContext $context
     * @return array
     */
    protected function validateFormParam(SC_FormParam_Ex $params, Zenith_Eccube_PageContext $context) {
        $errors = $params->checkError();

        return $errors;
    }
}
