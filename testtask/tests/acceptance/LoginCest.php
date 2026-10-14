<?php

use yii\helpers\Url;

class LoginCest
{
    public function ensureThatLoginWorks(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/login'));
        $I->see('Вход', 'h1');

        $I->amGoingTo('try to login with correct credentials');
        $I->fillField('input[name="LoginForm[username]"]', 'user');
        $I->fillField('input[name="LoginForm[password]"]', getenv('DEMO_USER_PASSWORD'));
        $I->click('Войти');
        $I->wait(2);

        $I->expectTo('see user info');
        $I->see('Выход');
    }
}
