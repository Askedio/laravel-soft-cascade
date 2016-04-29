<?php

namespace Askedio\SoftCascade\Tests;

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
        $user = \Askedio\SoftCascade\Tests\App\User::create([
            'name'     => 'admin',
            'email'    => 'admin@localhost.com'.rand(0, 10),
            'password' => bcrypt('password'),
        ])->profiles()->saveMany([
            new \Askedio\SoftCascade\Tests\App\Profiles(['phone' => '1231231234']),
        ]);

        // lazy
        \Askedio\SoftCascade\Tests\App\Profiles::first()->address()->create(['city' => 'Los Angeles']);

        return $user;
    }

    private function debugUser()
    {
        return \Askedio\SoftCascade\Tests\App\User::with([
          'profiles' => function ($query) {
              $query->withTrashed()->with(['address' => function ($query) {
                    $query->withTrashed();
              }]);
          },
        ])->withTrashed()->first();
    }

    public function testDelete()
    {
        $this->createUserRaw();

        \Askedio\SoftCascade\Tests\App\User::first()->delete();

        $this->missingFromDatabase('users', ['deleted_at' => null]);
        $this->missingFromDatabase('profiles', ['deleted_at' => null]);
        $this->missingFromDatabase('addresses', ['deleted_at' => null]);
    }

    public function testRestore()
    {
        $this->createUserRaw();

        \Askedio\SoftCascade\Tests\App\User::first()->delete();
        \Askedio\SoftCascade\Tests\App\User::withTrashed()->first()->restore();

        $this->seeInDatabase('users', ['deleted_at' => null]);
        $this->seeInDatabase('profiles', ['deleted_at' => null]);
        $this->seeInDatabase('addresses', ['deleted_at' => null]);
    }

    public function testNotCascadable()
    {
        (new \Askedio\SoftCascade\Listeners\Cascade())->cascade('notamodel', 'delete');
    }
}
