<?php

namespace tests\unit\models;

use app\models\User;

class UserTest extends \Codeception\Test\Unit
{
    public function testFindUserById()
    {
        verify($user = User::findIdentity(1))->notEmpty();
        verify($user->username)->equals('user');

        verify(User::findIdentity(999))->empty();
    }

    public function testFindUserByAccessToken()
    {
        verify(User::findIdentityByAccessToken('any-token'))->empty();
    }

    public function testFindUserByUsername()
    {
        verify($user = User::findByUsername('user'))->notEmpty();
        verify(User::findByUsername('not-user'))->empty();
    }

    /**
     * @depends testFindUserByUsername
     */
    public function testValidateUser()
    {
        $user = User::findByUsername('user');
        verify($user->validatePassword(getenv('DEMO_USER_PASSWORD')))->true();
        verify($user->validatePassword('wrong'))->false();
    }
}
