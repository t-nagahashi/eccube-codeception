<?php

use Codeception\Util\Fixtures;

/**
 * @group front
 * @group order
 * @group ef3
 */
class EF03OrderCest_COPY
{
    public function _before(\AcceptanceTester $I)
    {
    }

    public function _after(\AcceptanceTester $I)
    {
    }

    public function order_カート買い物を続ける(\AcceptanceTester $I)
    {
        $I->wantTo('EF0301-UC01-T01 カート 買い物を続ける');
        $createCustomer = Fixtures::get('createCustomer');
        $customer = $createCustomer();
        $I->loginAsMember($customer->getEmail(), 'password');

        // 商品詳細パーコレータ カートへ
        $I->amOnPage('products/detail/2');
        $I->buyThis(1);

        // 買い物を続ける
        $I->click('#main_middle #form_cart .total_box .btn_group p:nth-child(2) a');

        // トップページ
        $I->see('新着情報', '#contents_bottom #news_area h2');
    }

    public function order_カート削除(\AcceptanceTester $I)
    {
        $I->wantTo('EF0301-UC01-T02 カート 削除');
        $createCustomer = Fixtures::get('createCustomer');
        $customer = $createCustomer();
        $I->loginAsMember($customer->getEmail(), 'password');

        // 商品詳細パーコレータ カートへ
        $I->amOnPage('products/detail/2');
        $I->buyThis(1);

        // 削除
        $I->makeEmptyCart();
    }

    public function order_カート数量増やす(\AcceptanceTester $I)
    {
        $I->wantTo('EF0301-UC01-T03 カート 数量増やす');

        $createCustomer = Fixtures::get('createCustomer');
        $customer = $createCustomer();
        $I->loginAsMember($customer->getEmail(), 'password');

        // 商品詳細パーコレータ カートへ
        $I->amOnPage('products/detail/2');
        $I->buyThis(1);

        // 増加
        $I->click('#main_middle .cart_item .item_box:nth-child(1) .item_quantity ul li:nth-child(2) a');

        // 確認
        $I->see('2', '#main_middle .cart_item .item_box:nth-child(1) .item_quantity');

    }

    public function order_カート数量減らす(\AcceptanceTester $I)
    {
        $I->wantTo('EF0301-UC01-T04 カート 数量減らす');
        $createCustomer = Fixtures::get('createCustomer');
        $customer = $createCustomer();
        $I->loginAsMember($customer->getEmail(), 'password');

        // 商品詳細パーコレータ カートへ
        $I->amOnPage('products/detail/2');
        $I->buyThis(2);

        // 減らす
        $I->click('#main_middle .cart_item .item_box:nth-child(1) .item_quantity ul li:nth-child(1) a');

        // 確認
        $I->see('1', '#main_middle .cart_item .item_box:nth-child(1) .item_quantity');
    }

    public function order_ログインユーザ購入(\AcceptanceTester $I)
    {
        $I->wantTo('EF0302-UC01-T01 ログインユーザ購入');
        $I->logoutAsMember();
        $createCustomer = Fixtures::get('createCustomer');
        $customer = $createCustomer();
        $BaseInfo = Fixtures::get('baseinfo');

        // 商品詳細パーコレータ カートへ
        $I->amOnPage('products/detail/2');
        $I->buyThis(1);

        // レジへ
        $I->click('#main_middle .total_box .btn_group p a');
        $I->see('ログイン', '#main_middle .page-heading');

        // ログイン
        $I->submitForm('#main_middle form', [
            'login_email' => $customer->getEmail(),
            'login_pass' => 'password'
        ]);

        // 確認
        $I->see('ご注文内容のご確認', '#main_middle .page-heading');
        $I->see('お客様情報', '#main_middle #shopping-form #confirm_main');
        $I->see('配送情報', '#main_middle #shopping-form #confirm_main');
        $I->see('お届け先', '#main_middle #shopping-form #confirm_main');
        $I->see('お支払方法', '#main_middle #shopping-form #confirm_main');
        $I->see('お問い合わせ欄', '#main_middle #shopping-form #confirm_main');
        $I->see('小計', '#main_middle #shopping-form #confirm_side');
        $I->see('手数料', '#main_middle #shopping-form #confirm_side');
        $I->see('送料', '#main_middle #shopping-form #confirm_side');
        $I->see('合計', '#main_middle #shopping-form #confirm_side');

        $I->resetEmails();

        // 注文
        $I->click('#main_middle #shopping-form #order-button');
        $I->wait(1);

        // 確認
        $I->see('ご注文完了', '#main_middle .page-heading');

        // メール確認
        $I->seeEmailCount(2);
        foreach (array($customer->getEmail(), $BaseInfo->getEmail01()) as $email) {
            // TODO 注文した商品の内容もチェックしたい
            $I->seeInLastEmailSubjectTo($email, 'ご注文ありがとうございます');
            $I->seeInLastEmailTo($email, $customer->getName01().' '.$customer->getName02().' 様');
            $I->seeInLastEmailTo($email, 'お名前　：'.$customer->getName01().' '.$customer->getName02().' 様');
            $I->seeInLastEmailTo($email, 'フリガナ：'.$customer->getKana01().' '.$customer->getKana02().' 様');
            $I->seeInLastEmailTo($email, '郵便番号：〒'.$customer->getZip01().'-'.$customer->getZip02());
            $I->seeInLastEmailTo($email, '住所　　：'.$customer->getPref()->getName().$customer->getAddr01().$customer->getAddr02());
            $I->seeInLastEmailTo($email, '電話番号：'.$customer->getTel01().'-'.$customer->getTel02().'-'.$customer->getTel03());
            $I->seeInLastEmailTo($email, 'メールアドレス：'.$customer->getEmail());
        }

        // topへ
        $I->click('#main_middle #deliveradd_input .btn_group p a');
        $I->see('新着情報', '#contents_bottom #news_area h2');
    }

    public function order_ゲスト購入(\AcceptanceTester $I)
    {
        $I->wantTo('EF0302-UC02-T01 ゲスト購入');
        $I->logoutAsMember();

        $faker = Fixtures::get('faker');
        $new_email = microtime(true).'.'.$faker->safeEmail;
        $BaseInfo = Fixtures::get('baseinfo');

        // 商品詳細パーコレータ カートへ
        $I->amOnPage('products/detail/2');
        $I->buyThis(1);

        // レジへ
        $I->click('#main_middle .total_box .btn_group p a');
        $I->see('ログイン', '#main_middle .page-heading');

        // ゲスト購入
        $I->click('#main_middle #login_box div:nth-child(2) .btn_area a');
        $I->see('お客様情報の入力', '#main_middle .page-heading');

        $I->submitForm("#main_middle form",[
            'nonmember[name][name01]' => '姓03',
            'nonmember[name][name02]' => '名03',
            'nonmember[kana][kana01]' => 'セイ',
            'nonmember[kana][kana02]' => 'メイ',
            'nonmember[zip][zip01]' => '530',
            'nonmember[zip][zip02]' => '0001',
            'nonmember[address][pref]' => 27,
            'nonmember[address][addr01]' => '大阪市北区',
            'nonmember[address][addr02]' => '梅田2-4-9 ブリーゼタワー13F',
            'nonmember[tel][tel01]' => '111',
            'nonmember[tel][tel02]' => '111',
            'nonmember[tel][tel03]' => '111',
            'nonmember[email][first]' => $new_email,
            'nonmember[email][second]' => $new_email,
        ]);

        // 確認
        $I->see('ご注文内容のご確認', '#main_middle .page-heading');
        $I->see('お客様情報', '#main_middle #shopping-form #confirm_main');
        $I->see('配送情報', '#main_middle #shopping-form #confirm_main');
        $I->see('お届け先', '#main_middle #shopping-form #confirm_main');
        $I->see('お支払方法', '#main_middle #shopping-form #confirm_main');
        $I->see('お問い合わせ欄', '#main_middle #shopping-form #confirm_main');
        $I->see('小計', '#main_middle #shopping-form #confirm_side');
        $I->see('手数料', '#main_middle #shopping-form #confirm_side');
        $I->see('送料', '#main_middle #shopping-form #confirm_side');
        $I->see('合計', '#main_middle #shopping-form #confirm_side');

        $I->resetEmails();
        // 注文
        $I->click('#main_middle #shopping-form #confirm_side .total_amount p:nth-child(2) button');
        $I->wait(1);

        // 確認
        $I->see('ご注文完了', '#main_middle .page-heading');
        $I->seeEmailCount(2);
        foreach (array($new_email, $BaseInfo->getEmail01()) as $email) {
            // TODO 注文した商品の内容もチェックしたい
            $I->seeInLastEmailSubjectTo($email, 'ご注文ありがとうございます');
            $I->seeInLastEmailTo($email, '姓03 名03 様');
            $I->seeInLastEmailTo($email, 'お名前　：姓03 名03 様');
            $I->seeInLastEmailTo($email, 'フリガナ：セイ メイ 様');
            $I->seeInLastEmailTo($email, '郵便番号：〒530-0001');
            $I->seeInLastEmailTo($email, '住所　　：大阪府大阪市北区梅田2-4-9 ブリーゼタワー13F');
            $I->seeInLastEmailTo($email, '電話番号：111-111-111');
            $I->seeInLastEmailTo($email, 'メールアドレス：'.$new_email);
        }
        // topへ
        $I->click('#main_middle #deliveradd_input .btn_group p a');
        $I->see('新着情報', '#contents_bottom #news_area h2');
    }

    public function order_ゲスト購入情報変更(\AcceptanceTester $I)
    {
        $I->wantTo('EF0305-UC02-T01 ゲスト購入 情報変更'); // EF0305-UC04-T01も一緒にテスト
        $I->logoutAsMember();

        $faker = Fixtures::get('faker');
        $new_email = microtime(true).'.'.$faker->safeEmail;
        $BaseInfo = Fixtures::get('baseinfo');

        // 商品詳細パーコレータ カートへ
        $I->amOnPage('products/detail/2');
        $I->buyThis(1);

        // レジへ
        $I->click('#main_middle .total_box .btn_group p a');
        $I->see('ログイン', '#main_middle .page-heading');

        // ゲスト購入
        $I->click('#main_middle #login_box div:nth-child(2) .btn_area a');
        $I->see('お客様情報の入力', '#main_middle .page-heading');

        $I->submitForm("#main_middle form",[
            'nonmember[name][name01]' => '姓03',
            'nonmember[name][name02]' => '名03',
            'nonmember[kana][kana01]' => 'セイ',
            'nonmember[kana][kana02]' => 'メイ',
            'nonmember[zip][zip01]' => '530',
            'nonmember[zip][zip02]' => '0001',
            'nonmember[address][pref]' => 27,
            'nonmember[address][addr01]' => '大阪市北区',
            'nonmember[address][addr02]' => '梅田2-4-9 ブリーゼタワー13F',
            'nonmember[tel][tel01]' => '111',
            'nonmember[tel][tel02]' => '111',
            'nonmember[tel][tel03]' => '111',
            'nonmember[email][first]' => $new_email,
            'nonmember[email][second]' => $new_email,
        ]);

        // 確認
        $I->see('ご注文内容のご確認', '#main_middle .page-heading');
        $I->see('お客様情報', '#main_middle #shopping-form #confirm_main');
        $I->see('配送情報', '#main_middle #shopping-form #confirm_main');
        $I->see('お届け先', '#main_middle #shopping-form #confirm_main');
        $I->see('お支払方法', '#main_middle #shopping-form #confirm_main');
        $I->see('お問い合わせ欄', '#main_middle #shopping-form #confirm_main');
        $I->see('小計', '#main_middle #shopping-form #confirm_side');
        $I->see('手数料', '#main_middle #shopping-form #confirm_side');
        $I->see('送料', '#main_middle #shopping-form #confirm_side');
        $I->see('合計', '#main_middle #shopping-form #confirm_side');

        // お客様情報変更
        $I->click('#main_middle #shopping-form #confirm_main #customer');
        $I->wait(10);
        $I->fillField(['id' => 'edit0'], '姓0301');
        $I->click('#main_middle #shopping-form #confirm_main #customer-ok button');
        $I->wait(10);
        $I->see('姓0301', '#main_middle #shopping-form #confirm_main .address');

        // 配送情報
        $I->click('#main_middle #shopping-form #confirm_main .btn-shipping-edit');
        $I->see('お届け先の変更', '#main_middle .page-heading');
        $I->fillField(['id' => 'shopping_shipping_name_name01'], '姓0302');
        $I->click('#main_middle form .btn_group p:nth-child(1) button');
        $I->see('姓0302', '#main_middle #shopping-form #confirm_main .address');

        $I->resetEmails();
        // 注文
        $I->click('#main_middle #shopping-form #confirm_side .total_amount p:nth-child(2) button');
        $I->wait(1);

        // 確認
        $I->see('ご注文完了', '#main_middle .page-heading');

        $I->seeEmailCount(2);
        foreach (array($new_email, $BaseInfo->getEmail01()) as $email) {
            // TODO 注文した商品の内容もチェックしたい
            $I->seeInLastEmailSubjectTo($email, 'ご注文ありがとうございます');
            $I->seeInLastEmailTo($email, '姓0301 名03 様');
            $I->seeInLastEmailTo($email, 'お名前　：姓0302 名03 様', '変更後のお届け先');
            $I->seeInLastEmailTo($email, '郵便番号：〒530-0001');
            $I->seeInLastEmailTo($email, '住所　　：大阪府大阪市北区梅田2-4-9 ブリーゼタワー13F');
            $I->seeInLastEmailTo($email, '電話番号：111-111-111');
            $I->seeInLastEmailTo($email, 'メールアドレス：'.$new_email);
        }

        // topへ
        $I->click('#main_middle #deliveradd_input .btn_group p a');
        $I->see('新着情報', '#contents_bottom #news_area h2');
    }

    public function order_ShippingPaymentNotMatchTest(\AcceptanceTester $I)
    {
        $I->wantTo('EF0302-UC01-T01 ログインユーザ購入');
        $I->loginAsAdmin();

        // Go to Shop Setting page.
        $I->amOnPage('/admin/setting/shop');
        $I->fillField(['id' => 'shop_master_option_multiple_shipping_1'], 1);

        // Click submit button
        $I->click('#point_form #aside_column button');
        $I->see('基本情報を保存しました。', '#main_middle .page-heading');

        //change パーコレーター  product type
        $I->amOnPage('/admin/product/product/2/edit');
        $I->fillField(['id' => 'admin_product_class_product_type'], 2);
        $I->click('#common_button_box__insert_button button');

        //Change payment method
        $I->amOnPage('/admin/setting/shop/delivery/2/edit');
        //set 郵便振替 is enable and other is not enable
        $I->fillField(['id' => 'delivery_payments_1'], 1);
        $I->fillField(['id' => 'delivery_payments_2'], 0);
        $I->fillField(['id' => 'delivery_payments_3'], 0);
        $I->fillField(['id' => 'delivery_payments_4'], 0);
        $I->click('#common_button_box__insert_button button');

        //Delivery set payment method
        $I->amOnPage('/admin/setting/shop/delivery/1/edit');

        //set 現金書留 is enable and other is not enable
        $I->fillField(['id' => 'delivery_payments_1'], 0);
        $I->fillField(['id' => 'delivery_payments_2'], 1);
        $I->fillField(['id' => 'delivery_payments_3'], 0);
        $I->fillField(['id' => 'delivery_payments_4'], 0);
        $I->click('#common_button_box__insert_button button');

        // 商品詳細パーコレータ カートへ
        $I->amOnPage('products/detail/2');
        $I->buyThis(1);

        //buy ディナーフォーク so can not use buyThis
        $I->amOnPage('products/detail/1');
        $I->fillField(['id' => "quantity"], 1);
        $I->fillField(['id' => "classcategory_id1"], 1);
        $I->fillField(['id' => "classcategory_id2"], 1);
        $I->click('#form1 .btn_area button');

        // See error
        $I->see('この商品は同時に購入することはできません。', '#cart_box__message--1 .errormsg');
    }

    public function order_ShippingPaymentMatchTest(\AcceptanceTester $I)
    {
        $I->wantTo('EF0302-UC01-T01 ログインユーザ購入');
        $I->loginAsAdmin();
        $faker = Fixtures::get('faker');
        $new_email = microtime(true).'.'.$faker->safeEmail;
        $BaseInfo = Fixtures::get('baseinfo');


        // Go to Shop Setting page.
        $I->amOnPage('/admin/setting/shop');
        $I->fillField(['id' => 'shop_master_option_multiple_shipping_1'], 1);

        // Click submit button
        $I->click('#point_form #aside_column button');
        $I->see('基本情報を保存しました。', '#main_middle .page-heading');

        //change パーコレーター  product type
        $I->amOnPage('/admin/product/product/2/edit');
        $I->fillField(['id' => 'admin_product_class_product_type'], 2);
        $I->click('#common_button_box__insert_button button');

        //Change payment method
        $I->amOnPage('/admin/setting/shop/delivery/2/edit');
        //set 郵便振替 is enable and other is not enable
        $I->fillField(['id' => 'delivery_payments_1'], 1);
        $I->fillField(['id' => 'delivery_payments_2'], 0);
        $I->fillField(['id' => 'delivery_payments_3'], 0);
        $I->fillField(['id' => 'delivery_payments_4'], 0);
        $I->click('#common_button_box__insert_button button');

        //Delivery set payment method
        $I->amOnPage('/admin/setting/shop/delivery/1/edit');

        //set 現金書留 is enable and other is not enable
        $I->fillField(['id' => 'delivery_payments_1'], 1);
        $I->fillField(['id' => 'delivery_payments_2'], 1);
        $I->fillField(['id' => 'delivery_payments_3'], 0);
        $I->fillField(['id' => 'delivery_payments_4'], 0);
        $I->click('#common_button_box__insert_button button');

        $createCustomer = Fixtures::get('createCustomer');
        $customer = $createCustomer();
        $I->loginAsMember($customer->getEmail(), 'password');

        // 商品詳細パーコレータ カートへ
        $I->amOnPage('products/detail/2');
        $I->buyThis(1);

        //buy ディナーフォーク so can not use buyThis
        $I->amOnPage('products/detail/1');
        $I->fillField(['id' => "quantity"], 1);
        $I->fillField(['id' => "classcategory_id1"], 1);
        $I->fillField(['id' => "classcategory_id2"], 1);
        $I->click('#form1 .btn_area button');

        // レジへ
        $I->click('#main_middle .total_box .btn_group p a');

        // 確認
        $I->see('郵便振替', '#shopping_payment_1');
        $I->see('ご注文内容のご確認', '#main_middle .page-heading');
        $I->see('お客様情報', '#main_middle #shopping-form #confirm_main');
        $I->see('配送情報', '#main_middle #shopping-form #confirm_main');
        $I->see('お届け先', '#main_middle #shopping-form #confirm_main');
        $I->see('お支払方法', '#main_middle #shopping-form #confirm_main');
        $I->see('お問い合わせ欄', '#main_middle #shopping-form #confirm_main');
        $I->see('小計', '#main_middle #shopping-form #confirm_side');
        $I->see('手数料', '#main_middle #shopping-form #confirm_side');
        $I->see('送料', '#main_middle #shopping-form #confirm_side');
        $I->see('合計', '#main_middle #shopping-form #confirm_side');

        // お客様情報変更
        $I->click('#main_middle #shopping-form #confirm_main #customer');
        $I->wait(10);
        $I->fillField(['id' => 'edit0'], '姓0301');
        $I->click('#main_middle #shopping-form #confirm_main #customer-ok button');
        $I->wait(10);
        $I->see('姓0301', '#main_middle #shopping-form #confirm_main .address');

        // 配送情報
        $I->click('#main_middle #shopping-form #confirm_main .btn-shipping-edit');
        $I->see('お届け先の変更', '#main_middle .page-heading');
        $I->fillField(['id' => 'shopping_shipping_name_name01'], '姓0302');
        $I->click('#main_middle form .btn_group p:nth-child(1) button');
        $I->see('姓0302', '#main_middle #shopping-form #confirm_main .address');

        $I->resetEmails();
        // 注文
        $I->click('#main_middle #shopping-form #confirm_side .total_amount p:nth-child(2) button');
        $I->wait(1);

        // 確認
        $I->see('ご注文完了', '#main_middle .page-heading');

        $I->seeEmailCount(2);
        foreach (array($new_email, $BaseInfo->getEmail01()) as $email) {
            // TODO 注文した商品の内容もチェックしたい
            $I->seeInLastEmailSubjectTo($email, 'ご注文ありがとうございます');
            $I->seeInLastEmailTo($email, '姓0301 名03 様');
            $I->seeInLastEmailTo($email, 'お名前　：姓0302 名03 様', '変更後のお届け先');
            $I->seeInLastEmailTo($email, '郵便番号：〒530-0001');
            $I->seeInLastEmailTo($email, '住所　　：大阪府大阪市北区梅田2-4-9 ブリーゼタワー13F');
            $I->seeInLastEmailTo($email, '電話番号：111-111-111');
            $I->seeInLastEmailTo($email, 'メールアドレス：'.$new_email);
        }

        // topへ
        $I->click('#main_middle #deliveradd_input .btn_group p a');
        $I->see('新着情報', '#contents_bottom #news_area h2');
    }

    public function order_ShippingPayment(\AcceptanceTester $I)
    {
        $I->wantTo('EF0302-UC01-T01 ログインユーザ購入');
        $I->loginAsAdmin();
        $faker = Fixtures::get('faker');
        $new_email = microtime(true).'.'.$faker->safeEmail;
        $BaseInfo = Fixtures::get('baseinfo');

        // Go to Shop Setting page.
        $I->amOnPage('/admin/setting/shop');
        $I->fillField(['id' => 'shop_master_option_multiple_shipping_1'], 1);

        // Click submit button
        $I->click('#point_form #aside_column button');
        $I->see('基本情報を保存しました。', '#main_middle .page-heading');

        //Change payment method
        $I->amOnPage('/admin/setting/shop/delivery/2/edit');
        //set 郵便振替 is enable and other is not enable
        $I->fillField(['id' => 'delivery_payments_1'], 1);
        $I->fillField(['id' => 'delivery_payments_2'], 0);
        $I->fillField(['id' => 'delivery_payments_3'], 0);
        $I->fillField(['id' => 'delivery_payments_4'], 0);
        $I->click('#common_button_box__insert_button button');

        //Delivery set payment method
        $I->amOnPage('/admin/setting/shop/delivery/1/edit');

        //set 現金書留 is enable and other is not enable
        $I->fillField(['id' => 'delivery_payments_1'], 1);
        $I->fillField(['id' => 'delivery_payments_2'], 1);
        $I->fillField(['id' => 'delivery_payments_3'], 0);
        $I->fillField(['id' => 'delivery_payments_4'], 0);
        $I->click('#common_button_box__insert_button button');

        $createCustomer = Fixtures::get('createCustomer');
        $customer = $createCustomer();
        $I->loginAsMember($customer->getEmail(), 'password');

        // 商品詳細パーコレータ カートへ
        $I->amOnPage('products/detail/2');
        $I->buyThis(1);

        //buy ディナーフォーク so can not use buyThis
        $I->amOnPage('products/detail/1');
        $I->fillField(['id' => "quantity"], 1);
        $I->fillField(['id' => "classcategory_id1"], 1);
        $I->fillField(['id' => "classcategory_id2"], 1);
        $I->click('#form1 .btn_area button');

        // レジへ
        $I->click('#main_middle .total_box .btn_group p a');

        // 確認
        $I->see('郵便振替', '#shopping_payment_1');
        $I->see('ご注文内容のご確認', '#main_middle .page-heading');
        $I->see('お客様情報', '#main_middle #shopping-form #confirm_main');
        $I->see('配送情報', '#main_middle #shopping-form #confirm_main');
        $I->see('お届け先', '#main_middle #shopping-form #confirm_main');
        $I->see('お支払方法', '#main_middle #shopping-form #confirm_main');
        $I->see('お問い合わせ欄', '#main_middle #shopping-form #confirm_main');
        $I->see('小計', '#main_middle #shopping-form #confirm_side');
        $I->see('手数料', '#main_middle #shopping-form #confirm_side');
        $I->see('送料', '#main_middle #shopping-form #confirm_side');
        $I->see('合計', '#main_middle #shopping-form #confirm_side');

        // お客様情報変更
        $I->click('#main_middle #shopping-form #confirm_main #customer');
        $I->wait(10);
        $I->fillField(['id' => 'edit0'], '姓0301');
        $I->click('#main_middle #shopping-form #confirm_main #customer-ok button');
        $I->wait(10);
        $I->see('姓0301', '#main_middle #shopping-form #confirm_main .address');

        // 配送情報
        $I->click('#main_middle #shopping-form #confirm_main .btn-shipping-edit');
        $I->see('お届け先の変更', '#main_middle .page-heading');
        $I->fillField(['id' => 'shopping_shipping_name_name01'], '姓0302');
        $I->click('#main_middle form .btn_group p:nth-child(1) button');
        $I->see('姓0302', '#main_middle #shopping-form #confirm_main .address');

        $I->resetEmails();
        // 注文
        $I->click('#main_middle #shopping-form #confirm_side .total_amount p:nth-child(2) button');
        $I->wait(1);

        // 確認
        $I->see('ご注文完了', '#main_middle .page-heading');

        $I->seeEmailCount(2);
        foreach (array($new_email, $BaseInfo->getEmail01()) as $email) {
            // TODO 注文した商品の内容もチェックしたい
            $I->seeInLastEmailSubjectTo($email, 'ご注文ありがとうございます');
            $I->seeInLastEmailTo($email, '姓0301 名03 様');
            $I->seeInLastEmailTo($email, 'お名前　：姓0302 名03 様', '変更後のお届け先');
            $I->seeInLastEmailTo($email, '郵便番号：〒530-0001');
            $I->seeInLastEmailTo($email, '住所　　：大阪府大阪市北区梅田2-4-9 ブリーゼタワー13F');
            $I->seeInLastEmailTo($email, '電話番号：111-111-111');
            $I->seeInLastEmailTo($email, 'メールアドレス：'.$new_email);
        }

        // topへ
        $I->click('#main_middle #deliveradd_input .btn_group p a');
        $I->see('新着情報', '#contents_bottom #news_area h2');
    }

}