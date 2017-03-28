<?php

use Codeception\Util\Fixtures;
use Faker\Factory as Faker;

/**
 * @group front
 * @group mypage
 * @group ef5
 */
class EF05MypageCest
{
    public function mypage_お気に入り一覧(\AcceptanceTester $I)
    {
        $I->wantTo('EF0508-UC01-T01 Mypage お気に入り一覧');
        $createCustomer = Fixtures::get('createCustomer');
        $customer = $createCustomer();
        $I->setStock(2, 10);
        $I->loginAsMember($customer->getEmail(), 'password');

        // TOPページ>マイページ>ご注文履歴
        $I->amOnPage('/mypage');
        $I->click('お気に入り一覧');
        $I->wait(1);

        // 最初はなにも登録されていない
        $I->see('マイページ/お気に入り一覧', '#main_middle .page-heading');
        $I->see('お気に入りが登録されていません。', '#main_middle .container-fluid .intro');

        // お気に入り登録
        $I->amOnPage('/products/detail/2');
        $I->click('#favorite');

        $I->amOnPage('/mypage');
        $I->click('#main_middle .local_nav ul li:nth-child(2) a');
        $count = $I->grabTextFrom('#main_middle #favorite_lst__total_item_count');
        codecept_debug($count);
        $text = $I->grabTextFrom('#main_middle #item_list');
        codecept_debug($text);
        $I->see('パーコレーター', '#main_middle #item_list');

        // お気に入りを削除
        $I->click('#main_middle .container-fluid #item_list .btn_circle');
        $I->acceptPopup();
    }
}
