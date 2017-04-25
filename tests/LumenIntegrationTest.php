<?php

namespace Askedio\Tests;

use Askedio\Tests\App\BadRelation;
use Askedio\Tests\App\BadRelationB;
use Askedio\Tests\App\Profiles;
use Askedio\Tests\App\User;

/**
 *  TO-DO: Need better testing.
 *  Factories, Mocks, etc, but this does the job.
 */
class LumenIntegrationTest extends LumenBaseTestCase
{
    private function createUserRaw()
    {
        $user = User::create([
            'name'     => 'admin',
            'email'    => uniqid().'@localhost.com',
            'password' => bcrypt('password'),
        ])->profiles()->saveMany([
            new Profiles(['phone' => '1231231234']),
        ]);

        // lazy
        Profiles::first()->address()->create(['city' => 'Los Angeles']);

        return $user;
    }

    public function testBadRelation()
    {
        $this->createUserRaw();

        $this->setExpectedException(\LogicException::class);
        BadRelation::first()->delete();
    }

    public function testBadRelationB()
    {
        $this->createUserRaw();

        $this->setExpectedException(\LogicException::class);
        BadRelationB::first()->delete();
    }

    public function testDelete()
    {
        $this->createUserRaw();

        User::first()->delete();

        $this->assertDatabaseMissing('users', ['deleted_at' => null]);
        $this->assertDatabaseMissing('profiles', ['deleted_at' => null]);
        $this->assertDatabaseMissing('addresses', ['deleted_at' => null]);
    }

    public function testRestore()
    {
        $this->createUserRaw();

        User::first()->delete();
        User::withTrashed()->first()->restore();

        $this->assertDatabaseHas('users', ['deleted_at' => null]);
        $this->assertDatabaseHas('profiles', ['deleted_at' => null]);
        $this->assertDatabaseHas('addresses', ['deleted_at' => null]);
    }

    public function testMultipleDelete()
    {
        $this->createUserRaw();
        $this->createUserRaw();

        User::first()->delete();

        $this->assertEquals(2, User::withTrashed()->count());
        $this->assertEquals(1, User::count());

        $this->assertEquals(2, Profiles::withTrashed()->count());
        $this->assertEquals(1, Profiles::count());
    }

    public function testMultipleRestore()
    {
        $this->createUserRaw();
        $this->createUserRaw();

        User::first()->delete();
        User::withTrashed()->first()->restore();

        $this->assertEquals(2, User::withTrashed()->count());
        $this->assertEquals(2, User::count());

        $this->assertEquals(2, Profiles::withTrashed()->count());
        $this->assertEquals(2, Profiles::count());

        User::first()->restore();
    }

    public function testNotCascadable()
    {
        /*
         * TO-DO: Need a 'test' here, not just code coverage.
         */
        (new \Askedio\SoftCascade\SoftCascade())->cascade('notamodel', 'delete');
    }
}
