<?php

use Codeception\Util\Fixtures;

/**
 * @group front
 * @group order
 * @group ef3
 */
class EF03OrderMultiShippingCest
{
    private $customer;

    private $baseInfo;

    /**
     * @param AcceptanceTester $I
     */
    public function _before(\AcceptanceTester $I)
    {
        $I->logoutAsMember();
        $createCustomer = Fixtures::get('createCustomer');
        $this->customer = $createCustomer();
        $I->loginAsMember($this->customer->getEmail(), 'password');

        $this->baseInfo = Fixtures::get('baseinfo');

        // admin
        $I->loginAsAdmin();
        $shopPage = \Page\Admin\ShopSettingPage::go($I);
        $shopPage->changeMultiShipping('有効');
        $shopPage->登録();

        $productEditPage = \Page\Admin\ProductEditPage::go($I, 2);
        $productEditPage->changeProductType(2);
        $productEditPage->登録();
    }

    public function _after(\AcceptanceTester $I)
    {
        $shopPage = \Page\Admin\ShopSettingPage::go($I);
        $shopPage->changeMultiShipping('無効');
        $shopPage->登録();

        $productEditPage = \Page\Admin\ProductEditPage::go($I, 2);
        $productEditPage->changeProductType(1);
        $productEditPage->changeStatus();
        $productEditPage->登録();
    }

    /**
     * Test two different types of products in an order with multiple shipping.
     *
     * @param AcceptanceTester $i
     */
    public function order_MultiShipping_TwoType_OneAddress(\AcceptanceTester $i)
    {
        $i->wantTo('EF0305-UC05-T04 Multi shipping with other type');

        // 商品詳細パーコレータ カートへ
        $i->amOnPage('products/detail/2');
        $i->buyThis(1);

        $i->amOnPage('/products/detail/1');

        // 「カートに入れる」ボタンを押下する
        $i->selectOption(['id' => 'classcategory_id1'], 'プラチナ');
        $i->selectOption(['id' => 'classcategory_id2'], '150cm');
        $i->buyThis(3);

        // go to cart page
        $i->click('#main_middle .total_box .btn_group p a');

        // 確認
        $i->see('配送方法が異なる商品が含まれているため、お届け先は複数となります', '#main_middle #confirm_flow_box #confirm_flow_box__message p');
        $i->see('ご注文内容のご確認', '#main_middle .page-heading');
        $i->see('お客様情報', '#main_middle #shopping-form #confirm_main');
        $i->see('配送情報', '#main_middle #shopping-form #confirm_main');
        $i->see('お届け先', '#main_middle #shopping-form #confirm_main');
        $i->see('お支払方法', '#main_middle #shopping-form #confirm_main');
        $i->see('お問い合わせ欄', '#main_middle #shopping-form #confirm_main');
        $i->see('小計', '#main_middle #shopping-form #confirm_side');
        $i->see('手数料', '#main_middle #shopping-form #confirm_side');
        $i->see('送料', '#main_middle #shopping-form #confirm_side');
        $i->see('合計', '#main_middle #shopping-form #confirm_side');

        $i->resetEmails();

        // Check shipping
        // Two shipping
        $i->see('お届け先(1)', '#main_middle #shipping_confirm_box--0 h3');
        $i->see('お届け先(2)', '#main_middle #shipping_confirm_box--1 h3');

        // Go to multi shipping page
        $i->click('#main_middle #shopping_confirm #confirm_main a#shopping_confirm_box__button_edit_multiple');

        // Go to shopping confirm page
        $i->click('#main_middle #multiple_list__confirm_button #button__confirm');

        // Two shipping
        $i->see('お届け先(1)', '#main_middle #shipping_confirm_box--0 h3');
        $i->see('お届け先(2)', '#main_middle #shipping_confirm_box--1 h3');

        // 注文
        $i->click('#main_middle #shopping-form #confirm_side #order-button');
        $i->wait(1);

        // 確認
        $i->see('ご注文完了', '#main_middle h1.page-heading');
        // メール確認
        $i->seeEmailCount(2);
        foreach (array($this->customer->getEmail(), $this->baseInfo->getEmail01()) as $email) {
            $i->seeInLastEmailSubjectTo($email, 'ご注文ありがとうございます');
            $i->seeInLastEmailTo($email, $this->customer->getName01() . ' ' . $this->customer->getName02() . ' 様');
            $i->seeInLastEmailTo($email, 'お名前　：' . $this->customer->getName01() . ' ' . $this->customer->getName02() . ' 様');
            $i->seeInLastEmailTo($email, 'フリガナ：' . $this->customer->getKana01() . ' ' . $this->customer->getKana02() . ' 様');
            $i->seeInLastEmailTo($email, '郵便番号：〒' . $this->customer->getZip01() . '-' . $this->customer->getZip02());
            $i->seeInLastEmailTo($email, '住所　　：' . $this->customer->getPref()->getName() . $this->customer->getAddr01() . $this->customer->getAddr02());
            $i->seeInLastEmailTo($email, '電話番号：' . $this->customer->getTel01() . '-' . $this->customer->getTel02() . '-' . $this->customer->getTel03());
            $i->seeInLastEmailTo($email, 'メールアドレス：' . $this->customer->getEmail());
        }
        // topへ
        $i->click('#main_middle #deliveradd_input .btn_group p a');
        $i->see('新着情報', '#contents_bottom #news_area h2');
    }
}
