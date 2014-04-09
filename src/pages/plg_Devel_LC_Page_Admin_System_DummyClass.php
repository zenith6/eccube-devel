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
 * ダミー規格の登録
 *
 * @package Devel
 * @author Seiji Nitta
 */
class plg_Devel_LC_Page_Admin_System_DummyClass extends LC_Page_Admin_Ex {
    /**
     * @var Zenith_Eccube_PageContext
     */
    public $context;

    public function init() {
        parent::init();

        $this->tpl_mainpage = 'system/plg_Devel_dummy_class.tpl';
        $this->tpl_mainno = 'system';
        $this->tpl_subno = 'devel_dummy';
        $this->tpl_maintitle = 'システム設定';
        $this->tpl_subtitle = 'ダミーデータ - 規格';
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
        $context['min_category'] = 1;
        $context['max_category'] = 3;
        $context['min_name_length'] = 10;
        $context['max_name_length'] = 10;
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

        return $form;
    }

    /**
     * @param Zenith_Eccube_PageContext $this->context
     * @return SC_FormParam_Ex
     */
    protected function buildFormParam(Zenith_Eccube_PageContext $context) {
        $params = new SC_FormParam_Ex();

        $params->addParam('生成件数', 'number', INT_LEN, '', array('EXIST_CHECK'), $context['number']);
        $params->addParam('各規格毎の最少分類件数', 'min_category', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK'), $context['min_category']);
        $params->addParam('各規格毎の最大分類件数', 'max_category', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK'), $context['max_category']);
        $params->addParam('規格名のランダム文字列の最少長', 'min_name_length', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK'), $context['min_name_length']);
        $params->addParam('規格名のランダム文字列の最大長', 'max_name_length', INT_LEN, 'n', array('EXIST_CHECK', 'NUM_CHECK'), $context['max_name_length']);
        $params->addParam('規格名の接頭辞', 'name_prefix', STEXT_LEN, '', array('MAX_LENGTH_CHECK'), $this->context['name_prefix']);
        $params->addParam('規格名の接尾辞', 'name_suffix', STEXT_LEN, '', array('MAX_LENGTH_CHECK'), $this->context['name_suffix']);

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

        $name = 'min_category';
        $value = $params->getValue($name);
        $title = $params->disp_name[array_search($name, $params->keyname)];
        $min = 1;
        if ($value == '') {
        } elseif ($value < $min) {
            $errors[$name] = sprintf('※ %s は %s 以上にして下さい。<br />', h($title), h(number_format($min)));
        }

        $name = 'max_category';
        $value = $params->getValue($name);
        $title = $params->disp_name[array_search($name, $params->keyname)];
        $min = isset($errors['min_category']) ? 0 : $params->getValue('min_category');
        if ($value == '') {
        } elseif ($value < $min) {
            $errors[$name] = sprintf('※ %s は %s 以上にして下さい。<br />', h($title), h(number_format($min)));
        }

        $name = 'min_name_length';
        $value = $params->getValue($name);
        $title = $params->disp_name[array_search($name, $params->keyname)];
        $min = 1;
        $max = $name_random_space = STEXT_LEN
            - (isset($errors['name_prefix']) ? 0 : strlen($params->getValue('name_prefix')))
            - (isset($errors['name_suffix']) ? 0 : strlen($params->getValue('name_suffix')));
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
            $this->context['min_name_length'] = $params->getValue('min_name_length');
            $this->context['max_name_length'] = $params->getValue('max_name_length');
            $this->context['min_category'] = $params->getValue('min_category');
            $this->context['max_category'] = $params->getValue('max_category');
            $this->context['name_prefix'] = $params->getValue('name_prefix');
            $this->context['name_suffix'] = $params->getValue('name_suffix');

            $options = array(
                'min_name_length' => $this->context['min_name_length'],
                'max_name_length' => $this->context['max_name_length'],
                'min_category'    => $this->context['min_category'],
                'max_category'    => $this->context['max_category'],
                'name_prefix'     => $this->context['name_prefix'],
                'name_suffix'     => $this->context['name_suffix'],
                'creator_id'      => $_SESSION['member_id'],
            );

            $number = $this->context['number'];
            for ($i = 0; $i < $number; $i++) {
                $this->insertDummyData($options, $query);
            }

            $query->commit();

            $this->flash['result'] = array(
                'message' => sprintf('<p>ダミー規格を %s 件登録しました。</p>', h(number_format($number))),
                'class' => 'success',
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
        // 規格登録
        $values = array(
            'creator_id' => $options['creator_id'],
            'del_flg'    => 0,
        );

        $exprs = array(
            'rank'        => '(SELECT MAX(rank) + 1 FROM dtb_class)',
            'create_date' => 'NOW()',
            'update_date' => 'NOW()',
        );

        $class_id = $query->nextVal('dtb_class_class_id');
        $values['class_id'] = $class_id;

        $name_options = array(
            'min_length' => $options['min_name_length'],
            'max_length' => $options['max_name_length'],
            'capitalize' => true,
            'multiple'   => false,
        );
        $name = $options['name_prefix'] . $this->text->generate($options) . $options['name_suffix'];
        $values['name'] = $name;

        $query->insert('dtb_class', $values, $exprs);

        // 分類登録
        $category_num = rand($options['min_category'], $options['max_category']);
        for ($i = 0; $i < $category_num; $i++) {
            $values = $exprs = $exprs_values = array();

            $classcategory_id = $query->nextVal('dtb_classcategory_classcategory_id');
            $values['classcategory_id'] = $classcategory_id;

            $values['class_id'] = $class_id;

            $name_length = rand($options['min_name_length'], $options['max_name_length']);
            $name = $options['name_prefix'] . SC_Utils_Ex::sfGetRandomString($name_length) . $name_options['name_suffix'];
            $values['name'] = $name;

            $exprs['rank'] = 'IFNULL((SELECT MAX(rank) + 1 FROM dtb_classcategory WHERE class_id = ?), 1)';
            $exprs_values[] = $class_id;

            $values['creator_id'] = $creator_id;
            $values['del_flg'] = 0;
            $exprs['create_date'] = 'NOW()';
            $exprs['update_date'] = 'NOW()';

            $query->insert('dtb_classcategory', $values, $exprs, $exprs_values);
        }
    }
}
