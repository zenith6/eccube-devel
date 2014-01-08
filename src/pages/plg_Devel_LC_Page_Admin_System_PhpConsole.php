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
 * PHP の実行
 *
 * @package Devel
 * @author Seiji Nitta
 */
class plg_Devel_LC_Page_Admin_System_PhpConsole extends LC_Page_Admin_Ex {
    public function init() {
        parent::init();
        
        $this->tpl_mainpage = 'system/plg_Devel_php_console.tpl';
        $this->tpl_mainno = 'system';
        $this->tpl_subno = 'console';
        $this->tpl_maintitle = 'システム設定';
        $this->tpl_subtitle = 'コンソール - PHP の実行';
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
        $context['output_format'] = 'text/plain';
        
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
                'title' => $params->disp_name[$index],
                'value' => $params->getValue($key),
                'maxlength' => $params->length[$index],
                'error' => null,
            );
        }
        
        foreach ($errors as $key => $error) {
            $form[$key]['error'] = $error;
        }
        
        $form['transaction']['options'] = array(
            'none' => '使用しない',
            'commit' => 'コミット',
            'rollback' => 'ロールバック',
        );
        
        $form['output_format']['options'] = array(
            'text/plain' => 'テキスト',
            'text/html' => 'HTML',
            'application/json' => 'JSON',
        );
        
        return $form;
    }
    
    /**
     * @param Zenith_Eccube_PageContext $context
     * @return SC_FormParam_Ex
     */
    protected function buildFormParam(Zenith_Eccube_PageContext $context) {
        $params = new SC_FormParam_Ex();
        
        $params->addParam('コード', 'code', LLTEXT_LEN, '', array('EXIST_CHECK', 'MAX_LENGTH_CHECK'), $context['code']);
        $params->addParam('トランザクション', 'transaction', 10, '', array('EXIST_CHECK'), $context['transaction']);
        $params->addParam('出力フォーマット', 'output_format', 100, '', array('EXIST_CHECK', 'MAX_LENGTH_CHECK'), $context['output_format']);
        
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
        $this->template = TEMPLATE_ADMIN_REALDIR . 'system/plg_Devel_php_console_result.tpl';
        
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
                $tx = $params->getValue('transaction');
                switch ($tx) {
                    case 'commit':
                        $query->begin();
                        $tx_label = 'コミット';
                        break;
                        
                    case 'rollback':
                        $query->begin();
                        $tx_label = 'ロールバック';
                        break;
                
                    default:
                        $tx_label = 'なし';
                        break;
                }
                $this->transaction = $tx_label;
    
                $code = $params->getValue('code');
                $this->context['code'] = $code;
            
                $output = $this->executeCode($code);

                switch ($tx) {
                    case 'commit':
                        $query->commit();
                        break;
                        
                    case 'rollback':
                        $query->rollback();
                        break;
                        
                    default:
                        break;
                }
            } catch (Exception $e) {
                $this->error = $e->__toString();
            }
            
            $output_format =$params->getValue('output_format');
            $this->context['output_format'] = $output_format;
            $this->output_format = $output_format;
            $this->output = $this->formatOutput($output, $output_format);;

        } catch (Exception $e) {
            $query->rollback();
            
            throw $e;
        }
    }
    
    protected function executeCode($code) {
        ob_start();
        eval($code);
        return ob_get_clean();
    }
    
    private function formatOutput($output, $format) {
        switch ($format) {
            case 'application/json':
                $json = json_decode($output);
                if ($output !== null && $json === null) {
                    $formatted = '<p style="color: red;">Output was broken JSON<p>';
                    $formatted .= '<pre><code>' . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . '</code></pre>';
                    break;
                }

                $formatted = '<b>JSON</b>';
                $formatted .= self::formatJson($json);
                break;
                
            case 'text/html':
                $formatted = $output;
                break;
        
            default:
                $formatted = '<pre><code>' . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . '</code></pre>';
                break;
        }
        
        return $formatted;
    }
    
    private function formatJson($json) {
        $html = '';
        if (is_array($json)) {
            $html .= '<ol style="padding-left: 20px;">';
            foreach ($json as $item) {
                $html .= '<li>' . self::convertJsonToHtml($item) . '</li>';
            }
            $html .= '</ol>';
        } elseif (is_object($json)) {
            $html .= '<ul style="padding-left: 20px;">';
            foreach ($json as $name => $item) {
                $html .= '<li><strong>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</strong>: ' . self::convertJsonToHtml($item) . '</li>';
            }
            $html .= '</ul>';
        } elseif (is_int($json)) {
            $html .= htmlspecialchars($json, ENT_QUOTES, 'UTF-8') . ' <span style="color: dimgray;">(int)</span>';
        } elseif (is_float($json)) {
            $html .= htmlspecialchars($json, ENT_QUOTES, 'UTF-8') . ' <span style="color: dimgray;">(float)</span>';
        } else {
            $html .= '"' . htmlspecialchars($json, ENT_QUOTES, 'UTF-8') . '" <span style="color: dimgray;">(string)</span>';
        }
        
        return $html;
    }
}
