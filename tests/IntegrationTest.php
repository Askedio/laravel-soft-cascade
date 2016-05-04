<?php

namespace Askedio\Tests;

/**
 *  Not the best tests in the world.
 */
class IntegrationTest extends BaseTestCase
{
    /**
     * TO-DO: Factories are nicer. Mocks too, but this does the job.
     */
    private function createUserRaw()
    {
        $user = \Askedio\Tests\App\User::create([
            'name'     => 'admin',
            'email'    => 'admin@localhost.com'.rand(0, 10),
            'password' => bcrypt('password'),
        ])->profiles()->saveMany([
            new \Askedio\Tests\App\Profiles(['phone' => '1231231234']),
        ]);

        // lazy
        \Askedio\Tests\App\Profiles::first()->address()->create(['city' => 'Los Angeles']);

        return $user;
    }

    public function testDelete()
    {
        $this->createUserRaw();

        \Askedio\Tests\App\User::first()->delete();

        $this->missingFromDatabase('users', ['deleted_at' => null]);
        $this->missingFromDatabase('profiles', ['deleted_at' => null]);
        $this->missingFromDatabase('addresses', ['deleted_at' => null]);
    }

    public function testRestore()
    {
        $this->createUserRaw();

        \Askedio\Tests\App\User::first()->delete();
        \Askedio\Tests\App\User::withTrashed()->first()->restore();

        $this->seeInDatabase('users', ['deleted_at' => null]);
        $this->seeInDatabase('profiles', ['deleted_at' => null]);
        $this->seeInDatabase('addresses', ['deleted_at' => null]);
    }

    public function testNotCascadable()
    {
        (new \Askedio\SoftCascade\SoftCascade)->cascade('notamodel', 'delete');
    }
}
