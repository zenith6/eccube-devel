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
 * ダミーカテゴリの登録
 *
 * @package Devel
 * @author Seiji Nitta
 */
class plg_Devel_LC_Page_Admin_System_DummyCategory extends LC_Page_Admin_Ex {
    /**
     * @var Zenith_Eccube_PageContext
     */
    public $context;
    
    public function init() {
        parent::init();
        
        $this->tpl_mainpage = 'system/plg_Devel_dummy_category.tpl';
        $this->tpl_mainno = 'system';
        $this->tpl_subno = 'devel_dummy';
        $this->tpl_maintitle = 'システム設定';
        $this->tpl_subtitle = 'ダミーデータ - カテゴリ';
    }
    
    public function process() {
        parent::process();
        
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
        
        $context['number'] = 1;
        $context['parent'] = 0;
        $context['min_name_length'] = $context['max_name_length'] = 10;
        $context['name_suffix'] = $context['name_prefix'] = '';
        
        return $context;
    }
    
    protected function doEdit($params = null, $errors = array()) {
        if (!$params) {
            $params = $this->buildFormParam($this->context);
        }
        
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
        
        $options = array(
            0 => 'ルート'
        );
        $query = SC_Query_Ex::getSingletonInstance();
        $rows = $query->select('category_id, category_name, level, rank', 'dtb_category', 'del_flg = ? ORDER BY rank DESC', array(0));
        foreach ($rows as $row) {
            $options[$row['category_id']] = str_repeat('　', $row['level']) . $row['category_name'];
        }
        $form['parent']['options'] = $options;
        
        return $form;
    }
    
    /**
     * @param Zenith_Eccube_PageContext $this->context
     * @return SC_FormParam_Ex
     */
    
    protected function buildFormParam(Zenith_Eccube_PageContext $context) {
        $params = new SC_FormParam_Ex();
        
        $params->addParam('生成件数', 'number', INT_LEN, '', array('EXIST_CHECK'), $context['number']);
        $params->addParam('親カテゴリ', 'parent', INT_LEN, '', array('EXIST_CHECK'), $this->context['parent']);
        $params->addParam('カテゴリ名のランダム部分の最少長', 'min_name_length', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK'), $context['min_name_length']);
        $params->addParam('カテゴリ名のランダム部分の最大長', 'max_name_length', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK'), $context['max_name_length']);
        $params->addParam('カテゴリ名の接頭辞', 'name_prefix', STEXT_LEN, '', array('MAX_LENGTH_CHECK'), $this->context['name_prefix']);
        $params->addParam('カテゴリ名の接尾辞', 'name_suffix', STEXT_LEN, '', array('MAX_LENGTH_CHECK'), $this->context['name_suffix']);
        
        return $params;
    }
    
    /**
     * @param SC_FormParam_Ex $params
     * @return array
     */
    
    protected function validateFormParam(SC_FormParam_Ex $params) {
        $errors = $params->checkError();
        
        $name = 'number';
        $value = $params->getValue($name);
        $title = $params->disp_name[array_search($name, $params->keyname)];
        $min = 1;
        if ($value == '') {
        } elseif ($value < $min) {
            $errors[$name] = sprintf('※ %s は %s 以上にして下さい。<br />', h($title), h(number_format($min)));
        }
        
        $name = 'min_name_length';
        $value = $params->getValue($name);
        $title = $params->disp_name[array_search($name, $params->keyname)];
        $min = 1;
        $max = $name_random_space = STEXT_LEN - (isset($errors['name_prefix']) ? 0
            : strlen($params->getValue('name_prefix'))) - (isset($errors['name_suffix']) ? 0
            : strlen($params->getValue('name_suffix')));
        if ($value == '') {
        } elseif ($value < $min) {
            $errors[$name] = sprintf('※ %s は %s 以上にして下さい。<br />', h($title), h(number_format($min)));
        } elseif ($value >= $max) {
            $errors[$name] = sprintf('※ %s は %s 以下にして下さい。<br />', h($title), h(number_format($max)));
        }
        
        $name = 'max_name_length';
        $value = $params->getValue($name);
        $title = $params->disp_name[array_search($name, $params->keyname)];
        $min = isset($errors['min_name_length']) ? 0 : $params->getValue('min_name_length');
        $max = $name_random_space;
        if ($value == '') {
        } elseif ($value < $min) {
            $errors[$name] = sprintf('※ %s は %s 以上にして下さい。<br />', h($title), h(number_format($min)));
        } elseif ($value > $max) {
            $errors[$name] = sprintf('※ %s は %s 以下にして下さい。<br />', h($title), h(number_format($max)));
        }
        
        return $errors;
    }
    
    protected function doExecute() {
        $params = $this->buildFormParam($this->context);
        $params->setParam($_POST);
        
        $errors = $this->validateFormParam($params);
        if ($errors) {
            $this->doEdit($params, $errors);
            return;
        }
        
        try {
            $query = SC_Query_Ex::getSingletonInstance();
            $query->begin();
            
            $this->prepareCache($query);
            
            $this->context['number'] = $params->getValue('number');
            $this->context['parent'] = $params->getValue('parent');
            $this->context['min_name_length'] = $params->getValue('min_name_length');
            $this->context['max_name_length'] = $params->getValue('max_name_length');
            $this->context['name_prefix'] = $params->getValue('name_prefix');
            $this->context['name_suffix'] = $params->getValue('name_suffix');
            
            $options = array(
                'parent'          => $this->context['parent'],
                'min_name_length' => $this->context['min_name_length'],
                'max_name_length' => $this->context['max_name_length'],
                'name_prefix'     => $this->context['name_prefix'],
                'name_suffix'     => $this->context['name_suffix'],
                'creator_id'      => $_SESSION['member_id'],
            );
            
            $number = $this->context['number'];
            for ($i = 0; $i < $number; $i++) {
                $this->insertDummyData($options, $query);
            }
            
            $db_helper = new SC_Helper_DB_Ex();
            $db_helper->sfCountCategory($query, true);
            
            $query->commit();
            
            $this->flash['result'] = array(
                'message' => sprintf('<p>ダミーカテゴリを %s 件登録しました。</p>', h(number_format($number))),
                'class'   => 'success',
            );
            
            $this->doEdit();
        } catch (Exception $e) {
            $query->rollback();
            
            throw $e;
        }
    }
    
    private $text;
    
    private function prepareCache(SC_Query_Ex $query) {
        $this->text = new Zenith_Dummy_TextGenerator();
    }
    
    /**
     * @param SC_Query_Ex $query
     */
    
    protected function insertDummyData(array $options, SC_Query_Ex $query) {
        $query->query('UPDATE dtb_category SET rank = rank + 1 WHERE rank >= IFNULL((SELECT rank FROM (SELECT rank FROM dtb_category WHERE category_id = ?) AS temp), 0)', array($options['parent']));
        
        $values = $exprs = $exprs_values = array();
        
        $category_id = $query->nextVal('dtb_category_category_id');
        $values['category_id'] = $category_id;
        
        $values['parent_category_id'] = $options['parent'];
        
        $options = array(
            'min_length' => $options['min_name_length'],
            'max_length' => $options['max_name_length'],
            'capitalize' => true,
            'multiple'   => false,
        );
        $name = $options['name_prefix'] . $this->text->generate($options) . $options['name_suffix'];
        $values['category_name'] = $name;
        
        $exprs['rank'] = 'IFNULL((SELECT rank - 1 FROM dtb_category WHERE category_id = ?), 1)';
        $exprs_values[] = $options['parent'];
        
        $exprs['level'] = 'IFNULL((SELECT level + 1 FROM dtb_category WHERE category_id = ?), 1)';
        $exprs_values[] = $options['parent'];
        
        $values['creator_id'] = $options['creator_id'];
        $values['del_flg'] = 0;
        $exprs['create_date'] = 'NOW()';
        $exprs['update_date'] = 'NOW()';
        
        $query->insert('dtb_category', $values, $exprs, $exprs_values);
    }
}
