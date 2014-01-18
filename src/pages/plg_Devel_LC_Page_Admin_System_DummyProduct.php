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
 * ダミー商品の登録
 *
 * @package Devel
 * @author Seiji Nitta
 */
class plg_Devel_LC_Page_Admin_System_DummyProduct extends LC_Page_Admin_Ex {
    /**
     * @var Zenith_Eccube_PageContext
     */
    public $context;
    
    public function init() {
        parent::init();
        
        $this->tpl_mainpage = 'system/plg_Devel_dummy_product.tpl';
        $this->tpl_mainno = 'system';
        $this->tpl_subno = 'devel_dummy';
        $this->tpl_maintitle = 'システム設定';
        $this->tpl_subtitle = 'ダミーデータ - 商品';
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
        $context['max_name_length'] = $context['min_name_length'] = 10;
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

            $this->context['number'] = $params->getValue('number');
            $this->context['min_name_length'] = $params->getValue('min_name_length');
            $this->context['max_name_length'] = $params->getValue('max_name_length');
            $this->context['name_prefix'] = $params->getValue('name_prefix');
            $this->context['name_suffix'] = $params->getValue('name_suffix');
            
            $options = array(
                'min_name_length' => $this->context['min_name_length'],
                'max_name_length' => $this->context['max_name_length'],
                'name_prefix'     => $this->context['name_prefix'],
                'name_suffix'     => $this->context['name_suffix'],
                'creator_id'      => $_SESSION['member_id'],
            );
            
            $this->prepareCache($query);
            $number = $this->context['number'];
            for ($i = 0; $i < $number; $i++) {
                $this->insertDummyData($options, $query);
            }
            
            $db_helper = new SC_Helper_DB_Ex();
            $db_helper->sfCountCategory($query, true);
            $db_helper->sfCountMaker($query);

            $query->commit();

            $this->flash['success'] = array(
                'message' => sprintf('<p>ダミー商品を %s 件登録しました。</p>', h(number_format($number))),
                'class' => 'success',
            );
        
            $this->doEdit();
        } catch (Exception $e) {
            $query->rollback();
            
            throw $e;
        }
    }
    
    private $text;
    private $alphabets;
    private $classes;
    private $classeCategories;
    private $makerIds;
    private $statusIds;
    private $deliveryDateIds;
    private $productTypeIds;
    private $exts;
    private $dispIds;
    private $categoryIds;
    
    private function prepareCache(SC_Query_Ex $query) {
        $this->alphabets = new Zenith_Dummy_StringGenerator();
        
        $this->text = new Zenith_Dummy_TextGenerator();
        
        $this->makerIds = $query->getCol('maker_id', 'dtb_maker');
        
        $this->statusIds = $query->getCol('id', 'mtb_status');
        
        // $this->dispIds = $query->getCol('id', 'mtb_disp');
        $this->dispIds = array(1); // 公開限定
        
        $this->deliveryDateIds = $query->getCol('id', 'mtb_delivery_date');
        
        $this->productTypeIds = $query->getCol('id', 'mtb_product_type');
        
        $this->categoryIds = $query->getCol('category_id', 'dtb_category');

        $classeCategories = array();
        foreach ($query->select('classcategory_id, class_id', 'dtb_classcategory') as $row) {
            $classeCategories[$row['class_id']][] = $row['classcategory_id'];
        }
        $this->classes = array_keys($classeCategories);
        $this->classeCategories = $classeCategories;
        
        $this->exts = array('jpg', 'txt', 'png', 'gif', 'pdf', 'zip', 'tar.gz');
    }
    
    /**
     * @param SC_Query_Ex $query
     */
    protected function insertDummyData(array $options, SC_Query_Ex $query) {
        /*
         * 商品登録
         */
        $values = $exprs = $exprs_values = array();

        $product_id = $query->nextVal('dtb_products_product_id');
        $values['product_id'] = $product_id;
        
        $values['name'] = $this->text->generate(array('min_length' => 1, 'max_length' => STEXT_LEN, 'multiple' => false));

        $values['maker_id'] = $this->makerIds[array_rand($this->makerIds, 1)];

        $values['status'] = $this->dispIds[array_rand($this->dispIds, 1)];
        
        // メーカー URL
        $values['comment1'] = sprintf('http://www.example.com/products/%s', rawurldecode($product_id));
        
        // 検索ワード
        $values['comment3'] = $this->text->generate(array('min_length' => 0, 'max_length' => LLTEXT_LEN / 10));

        // 用途不明
        $values['comment2'] = null;
        $values['comment4'] = null;
        $values['comment5'] = null;
        $values['comment6'] = null;
        
        $values['note'] = $this->text->generate(array('min_length' => 0, 'max_length' => LLTEXT_LEN / 10));

        $values['main_comment'] = $this->text->generate(array('min_length' => 0, 'max_length' => LLTEXT_LEN / 10));
        $values['main_image'] = 'holder.js/260x260/text:' . rawurlencode($values['name']);
        $values['main_large_image'] = 'holder.js/500x500/text:' . rawurlencode($values['name']);
        
        $values['main_list_comment'] = $this->text->generate(array('min_length' => 0, 'max_length' => MTEXT_LEN));
        $values['main_list_image'] = 'holder.js/130x130/text:' . rawurlencode($values['name']);
        
        $sub_num = mt_rand(0, PRODUCTSUB_MAX);
        for ($i = 1; $i <= $sub_num; $i++) {
            $values["sub_title{$i}"] = $this->text->generate(array('min_length' => 1, 'max_length' => STEXT_LEN, 'multiple' => false));
            $values["sub_comment{$i}"] = $this->text->generate(array('min_length' => 0, 'max_length' => LLTEXT_LEN / 10));
            $values["sub_image{$i}"] = 'holder.js/200x200/text:' . rawurlencode($values['name']);
            $values["sub_large_image{$i}"] = 'holder.js/500x500/text:' . rawurlencode($values['name']);
        }

        $values['deliv_date_id'] = $this->deliveryDateIds[array_rand($this->deliveryDateIds, 1)];
        
        $values['creator_id'] = $options['creator_id'];
        $values['del_flg'] = 0;
        $exprs['create_date'] = 'NOW()';
        $exprs['update_date'] = 'NOW()';

        $query->insert('dtb_products', $values, $exprs, $exprs_values);
        
        /*
         * 商品ステータス
         */
        $status_id_keys = $this->statusIds
            ? (array)array_rand($this->statusIds, mt_rand(1, count($this->statusIds)))
            : array();
        foreach ($status_id_keys as $status_id_key) {
            $status_id = $this->statusIds[$status_id_key];
            $values = $exprs = $exprs_values = array();
            $values['product_status_id'] = $status_id;
            $values['product_id'] = $product_id;
            $values['creator_id'] = $options['creator_id'];
            $values['del_flg'] = 0;
            $exprs['create_date'] = 'NOW()';
            $exprs['update_date'] = 'NOW()';
            $query->insert('dtb_product_status', $values, $exprs, $exprs_values);
        }

        /*
         * 商品規格
         */
        $sale_limit = mt_rand(0, 10000);
        $deliv_fee = mt_rand(0, 1000);
        $point_rate = mt_rand(0, 100);
        $product_type_id = $this->productTypeIds[array_rand($this->productTypeIds, 1)];
        
        // 必須規格
        // classcategory_id1=0, classcategory_id2=0 は必ず登録する必要がある。
        // 追加規格がある場合は del_flg=1、ない場合は del_flg=0 とする事。
        $ccs = array(
            array(
                'cc1s' => array(0),
                'cc2s' => array(0),
                'base' => true,
            )
        );
        
        // 追加規格
        $has_class = $this->classes && mt_rand(0, 2);
        if ($has_class) {
            $cc_keys = (array)array_rand($this->classeCategories, mt_rand(1, 2));
            $c1 = array_shift($cc_keys);
            $c2 = array_shift($cc_keys);
            $cc1s = $c1 ? $this->classeCategories[$c1] : array();
            $cc2s = $c2 ? $this->classeCategories[$c2] : array(0);
            $ccs[] = array(
                'cc1s' => $cc1s,
                'cc2s' => $cc2s,
                'base' => false,
            );
        }
        foreach ($ccs as $cc) {
            $cc1s = $cc['cc1s'];
            $cc2s = $cc['cc2s'];
            $is_base = $cc['base'];
            foreach ($cc1s as $cc1) {
                foreach ($cc2s as $cc2) {
                    $values = $exprs = $exprs_values = array();
        
                    $product_class_id = $query->nextVal('dtb_products_class_product_class_id');
                    $values['product_class_id'] = $product_class_id;
                    
                    $values['product_id'] = $product_id;
        
                    $values['classcategory_id1'] = $cc1;
                    $values['classcategory_id2'] = $cc2;
                    
                    $values['product_type_id'] = $product_type_id;
                    $values['product_code'] = $product_code = 'product_' . $product_id . '_class_' . $cc1 . '_cat_' . $cc2;
                    
                    $values['stock'] = mt_rand(0, 10000);
                    $values['stock_unlimited'] = mt_rand(0, 1);
                    
                    $values['price01'] = $price01 = mt_rand(2, 999999);
                    $values['price02'] = mt_rand(1, $price01);
                    
                    $values['sale_limit'] = $sale_limit;
                    $values['deliv_fee'] = $deliv_fee;
                    $values['point_rate'] = $point_rate;
    
                    $values['down_filename'] = 'file_' . $product_class_id . '.' . $this->exts[array_rand($this->exts, 1)];
                    $values['down_realfilename'] = 'original_' . $values['down_filename'];
                    
                    $values['creator_id'] = $options['creator_id'];
                    $values['del_flg'] = (int)($is_base && $has_class);
                    $exprs['create_date'] = 'NOW()';
                    $exprs['update_date'] = 'NOW()';
        
                    $query->insert('dtb_products_class', $values, $exprs, $exprs_values);
                }
            }
        }
        
        /*
         * カテゴリ
         */
        $keys = $this->categoryIds
            ? (count($this->categoryIds) == 1 ? array(0) : (array)array_rand($this->categoryIds, mt_rand(1, 2)))
            : array();
        $categoryIds = array_intersect_key($this->categoryIds, array_flip($keys));
        $rank = 1;
        foreach ($categoryIds as $categoryId) {
            $values = $exprs = $exprs_values = array();

            $values['product_id'] = $product_id;
            $values['category_id'] = $categoryId;
            $values['rank'] = $rank++;

            $query->insert('dtb_product_categories', $values, $exprs, $exprs_values);
        }
        
        /*
         * 関連商品
         */
        $num = mt_rand(0, RECOMMEND_PRODUCT_MAX);
        $rows = $query->select('product_id', 'dtb_products', '? ORDER BY RANDOM() LIMIT ?', array(true, $num));
        foreach ($rows as $rank => $row) {
            $values = $exprs = $exprs_values = array();

            $values['product_id'] = $product_id;
            $values['recommend_product_id'] = $row['product_id'];
            $values['rank'] = $rank + 1;
            $values['comment'] = $this->text->generate(array('min_length' => 1, 'max_length' => LTEXT_LEN));
            $values['status'] = 1;
            $values['creator_id'] = $options['creator_id'];
            $exprs['create_date'] = 'NOW()';
            $exprs['update_date'] = 'NOW()';

            $query->insert('dtb_recommend_products', $values, $exprs, $exprs_values);
        }
    }
}
