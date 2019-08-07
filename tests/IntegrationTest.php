<?php

namespace Askedio\Tests;

use Askedio\SoftCascade\Exceptions\SoftCascadeLogicException;
use Askedio\Tests\App\BadRelation;
use Askedio\Tests\App\BadRelationAction;
use Askedio\Tests\App\BadRelationB;
use Askedio\Tests\App\Category;
use Askedio\Tests\App\Comment;
use Askedio\Tests\App\Languages;
use Askedio\Tests\App\Post;
use Askedio\Tests\App\Profiles;
use Askedio\Tests\App\RoleReader;
use Askedio\Tests\App\RoleWriter;
use Askedio\Tests\App\User;
use Askedio\Tests\App\Video;

/**
 *  TO-DO: Need better testing.
 *  Factories, Mocks, etc, but this does the job.
 */
class IntegrationTest extends TestCase
{
    /**
     * Setup Language before each test.
     */
    public function setUp(): void
    {
        parent::setUp();
        Languages::create([
            'language' => 'English',
        ]);
    }

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
        Profiles::first()->address()->create(['languages_id' => 1, 'city' => 'Los Angeles']);

        return $user;
    }

    private function createCommentRaw()
    {
        Post::create([
            'title'   => 'Post',
            'body'    => 'Post chulo',
        ])->comments()->saveMany([
            new Comment(['body' => 'comentario post']),
        ]);

        Video::create([
            'title'   => 'Video',
            'url'     => 'Video chulo',
        ])->comments()->saveMany([
            new Comment(['body' => 'comentario video']),
        ]);

        return $this;
    }

    private function createPostAndCategoriesRaw()
    {
        $categories = collect();
        for ($i = 0; $i < 5; $i++) {
            $categories->push(Category::create([
                'name' => 'Category '.$i,
            ]));
        }

        for ($i = 0; $i < 15; $i++) {
            $post = Post::create([
                'title' => 'Post '.$i,
                'body'  => 'Post chulo'.$i,
            ]);
            $categories->each(function ($category) use ($post) {
                $category->posts()->attach($post->id);
            });
        }

        return $this;
    }

    private function createRoleRaw()
    {
        RoleWriter::create([
            'writer_name' => 'Lisa',
            'id'          => 1,
        ])->user()->save(new User([
            'name'     => 'admin',
            'email'    => uniqid().'@localhost.com',
            'password' => bcrypt('password'),
        ]));

        RoleReader::create([
            'reader_name' => 'Frank',
            'id'          => 1,
        ])->user()->save(new User([
            'name'     => 'admin',
            'email'    => uniqid().'@localhost.com',
            'password' => bcrypt('password'),
        ]));

        return $this;
    }

    public function testBelongsToManyRelation()
    {
        $this->createPostAndCategoriesRaw();

        $categoryToDelete = Category::with('posts')->first();
        $categoryToDelete->delete();

        $this->assertSoftDeleted('categories', ['id' => $categoryToDelete->id]);
        $categoryToDelete->posts->each(function ($post) {
            $this->assertSoftDeleted('posts', ['id' => $post->id]);
        });
    }

    public function testPolymorphicManyRelation()
    {
        $this->createCommentRaw();

        Post::first()->delete();

        $this->assertDatabaseHas('videos', ['deleted_at' => null]);
        $this->assertDatabaseHas('comments', ['commentable_type' => 'Askedio\Tests\App\Video',  'deleted_at' => null]);
        $this->assertDatabaseMissing('posts', ['deleted_at' => null]);
        $this->assertDatabaseMissing('comments', ['commentable_type' => 'Askedio\Tests\App\Post', 'deleted_at' => null]);
    }

    public function testReportPolymorphicManyRelation()
    {
        $this->createCommentRaw();

        Post::first()->delete();
        Post::withTrashed()->first()->restore();

        $this->assertDatabaseHas('posts', ['deleted_at' => null]);
        $this->assertDatabaseHas('comments', ['commentable_type' => 'Askedio\Tests\App\Post', 'deleted_at' => null]);
    }

    public function testPolymorphicOneRelation()
    {
        $this->createRoleRaw();

        RoleWriter::first()->delete();

        $this->assertDatabaseMissing('writers', ['deleted_at' => null]);
        $this->assertDatabaseMissing('users', ['role_type' => 'Askedio\Tests\App\RoleWriter', 'deleted_at' => null]);
    }

    public function testRestorePolymorphicOneRelation()
    {
        $this->createRoleRaw();

        RoleWriter::first()->delete();
        RoleWriter::withTrashed()->first()->restore();

        $this->assertDatabaseHas('writers', ['deleted_at' => null]);
        $this->assertDatabaseHas('users', ['role_type' => 'Askedio\Tests\App\RoleWriter', 'deleted_at' => null]);
        $this->assertDatabaseHas('readers', ['deleted_at' => null]);
        $this->assertDatabaseHas('users', ['role_type' => 'Askedio\Tests\App\RoleReader', 'deleted_at' => null]);
    }

    public function testBadRelation()
    {
        $this->createUserRaw();

        $this->expectException(SoftCascadeLogicException::class);
        BadRelation::first()->delete();
    }

    public function testBadRelationB()
    {
        $this->createUserRaw();

        $this->expectException(SoftCascadeLogicException::class);
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

    public function testDeleteQueryBuilder()
    {
        $this->createUserRaw();

        User::whereIn('id', [1])->delete();

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

    public function testRestoreQueryBuilder()
    {
        $this->createUserRaw();

        User::whereIn('id', [1])->delete();
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

    public function testKeepDeletedDates()
    {
        $this->createPostAndCategoriesRaw();

        $post = Post::first();
        $post->deleted_at = '2011-01-01';
        $post->save();

        $this->assertSoftDeleted('posts', ['id' => $post->id]);

        $categoryToDelete = Category::with('posts')->first();
        $categoryToDelete->delete();

        $this->assertSoftDeleted('categories', ['id' => $categoryToDelete->id]);
        $categoryToDelete->posts->each(function ($post) {
            $this->assertSoftDeleted('posts', ['id' => $post->id]);
        });
        $posts = Post::withTrashed()->get();
        $this->assertNotEquals($posts->first()->deleted_at, $posts->last()->deleted_at);
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

    public function testRestrictedRelationWithoutRestrictedRows()
    {
        $language = Languages::first();
        $language->delete();

        $this->assertDatabaseMissing('languages', ['id' => $language->id, 'deleted_at' => null]);
    }

    public function testRestrictedRelation()
    {
        $this->createUserRaw();
        $this->expectException(SoftCascadeLogicException::class);
        Languages::first()->delete();
    }

    public function testInexistentRestrictedAction()
    {
        $this->createUserRaw();
        $this->expectException(SoftCascadeLogicException::class);
        BadRelationAction::first()->delete();
    }

    // public function testNotCascadable()
    // {
        /*
         * TO-DO: Need a 'test' here, not just code coverage.
         */
    //     (new \Askedio\SoftCascade\SoftCascade())->cascade('notamodel', 'delete');
    // }
}
