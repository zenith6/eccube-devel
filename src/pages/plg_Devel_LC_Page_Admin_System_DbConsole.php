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
 * SQL の実行
 *
 * @package Devel
 * @author Seiji Nitta
 */
class plg_Devel_LC_Page_Admin_System_DbConsole extends LC_Page_Admin_Ex {
    public function init() {
        parent::init();
        
        $this->tpl_mainpage = 'system/plg_Devel_db_console.tpl';
        $this->tpl_mainno = 'system';
        $this->tpl_subno = 'console';
        $this->tpl_maintitle = 'システム設定';
        $this->tpl_subtitle = 'コンソール - SQL の実行';
    }
    
    public function process() {
        parent::process();

        if (!DEBUG_MODE) {
            $message = 'このページはデバッグモードが有効時にのみ利用可能です。パラメーター設定から DEBUG_MODE を true に設定して下さい。';
            SC_Helper_HandleError_Ex::displaySystemError($message);
            return;
        }
        
        $this->context = $this->restoreContext();
        
        $mode = $this->getMode();
        switch ($mode) {
            case 'execute':
                $this->doExecute();
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
        
        $context['code'] = '';
        $context['transaction'] = 'rollback';
        
        return $context;
    }

    protected function doEdit($errors = array()) {
        $params = $this->buildFormParam($this->context);
        $form = $this->buildForm($params, $errors);
        $this->form = $form;
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
                'title'     => $params->disp_name[$index],
                'value'     => $params->getValue($key),
                'maxlength' => $params->length[$index],
                'error'     => null,
            );
        }
        
        foreach ($errors as $key => $error) {
            $form[$key]['error'] = $error;
        }
        
        $form['transaction']['options'] = array(
            'commit'   => 'コミット',
            'rollback' => 'ロールバック',
        );
        
        return $form;
    }
    
    /**
     * @param Zenith_Eccube_PageContext $context
     * @return SC_FormParam_Ex
     */
    protected function buildFormParam(Zenith_Eccube_PageContext $context) {
        $params = new SC_FormParam_Ex();
        
        $params->addParam('SQL 文', 'code', 65536, '', array('EXIST_CHECK', 'MAX_LENGTH_CHECK'), $context['code']);
        $params->addParam('トランザクション', 'transaction', 10, '', array('EXIST_CHECK'), $context['transaction']);
        
        return $params;
    }
    
    /**
     * @param SC_FormParam_Ex $params
     * @return array
     */
    protected function validateFormParam(SC_FormParam_Ex $params) {
        $errors = $params->checkError();

        return $errors;
    }
    
    protected function doExecute() {
        try {
            $params = $this->buildFormParam($this->context);
            $params->setParam($_POST);
            
            $errors = $this->validateFormParam($params);
            if ($errors) {
                $this->doEdit($errors);
                return;
            }
            
            try {
                $query = SC_Query_Ex::getSingletonInstance();
                
                $query->begin();
                $tx = $params->getValue('transaction');
                switch ($tx) {
                    case 'commit':
                        $tx_label = 'コミット';
                        break;
                        
                    case 'rollback':
                    default:
                        $tx_label = 'ロールバック';
                        break;
                }
                $this->transaction = $tx_label;
    
                $code = $params->getValue('code');
                $this->context['code'] = $code;
            
                $stmts = self::splitStatements($code);
                $results = array();
                $mdb2 = $query->conn;
                foreach ($stmts as $stmt) {
                    $results[] = $this->executeStatement($stmt, $mdb2);
                }
                $this->results = $results;

                switch ($tx) {
                    case 'commit':
                        $query->commit();
                        break;
                        
                    case 'rollback':
                    default:
                        $query->rollback();
                        break;
                }
            } catch (Exception $e) {
                $this->error = $e->__toString();
            }
            
            $this->doEdit();
        } catch (Exception $e) {
            $query->rollback();

            throw $e;
        }
    }
    
    protected function executeStatement($stmt, $mdb2) {
        $log = array(
            'statement' => $stmt,
            'error'     => false,
            'message'   => '',
            'rows'      => array(),
            'columns'   => array(),
        );

        $info = self::getStatementInfo($stmt);
        if ($info['empty']) {
            return $log;
        }
        
        if ($info['select']) {
            $result = $mdb2->query($stmt);
        } else {
            $result = $mdb2->exec($stmt);
        }
        
        if (PEAR::isError($result)) {
            $log['error'] = true;
            $log['message'] = $result->toString();
        } elseif (is_int($result)) {
            $log['rows'] = array($result);
            $log['columns'] = array('* affected rows *');
        } else {
            $log['rows'] = $result->fetchAll();
            $log['columns'] = array_flip($result->getColumnNames());
        
            $result->free();
        }
        
        return $log;
    }
    
    private static function getStatementInfo($stmt) {
        self::$stmtInfo = array(
            'empty' => true,
            'select' => false,
        );
        
        $pattern = '#\'[^\']*\'|--[^\\r\\n]*[\\r\\n]*|/\*(?:[^*]|\*[^/])*\*/|\\w+|\\s+|.#mi';
        $stmts = preg_replace_callback($pattern, array('plg_Devel_LC_Page_Admin_System_DbConsole', 'updateStatementInfo'), $stmt);
        
        return self::$stmtInfo;
    }
    
    private static $stmtInfo;

    private static function updateStatementInfo($captures) {
        $token = $captures[0];

        if (strtolower($token) == 'select') {
            self::$stmtInfo['select'] = true;
            self::$stmtInfo['empty'] = false;
            return;
        }
        
        if (trim($token) == '') {
            return;
        }

        switch (substr($token, 0, 1)) {
            case '-':
            case '/':
            case ';':
                break;
                
            default:
                self::$stmtInfo['empty'] = false;
                break;
        }
    }
    
    private static function splitStatements($stmt) {
        // 文の区切り文字をヌル文字に変換
        $pattern = '#\'[^\']*\'|--[^\\r\\n]*[\\r\\n]*|/\*(?:[^*]|\*[^/])*\*/|\\w+|\\s+|.#m';
        $stmts = preg_replace_callback($pattern, array('plg_Devel_LC_Page_Admin_System_DbConsole', 'replaceLineDelimiter'), $stmt);
        
        // ヌル文字で文を区切る
        $stmts = (array)explode("\x00", $stmts);
        
        return $stmts;
    }

    private static function replaceLineDelimiter($captures) {
        $token = $captures[0];
        
        switch (substr($token, 0, 1)) {
            case ';':
                return "\x00";
            
            default:
                return $token;
        }
    }
}
