<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Plannr\Laravel\FastRefreshDatabase\Traits\FastRefreshDatabase;
use Tests\TestCase;

class PostsTest extends TestCase
{
    use FastRefreshDatabase;

    public function test_posts_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/posts')
            ->assertStatus(200);
    }

    public function test_user_must_be_logged_in_to_access_posts_page(): void
    {
        $this->get('/posts')
            ->assertRedirectToRoute('login');
    }

    public function test_post_model_belongs_to_category_model(): void
    {
        $category = Category::factory()->create();
        Post::factory()->for($category)->create();

        $this->assertTrue(Post::with('category')->first()->category->name == $category->name);
    }

    public function test_post_model_belongs_to_user_model(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        Post::factory()->for($category)->for($user)->create();

        $this->assertTrue(Post::with('user')->first()->user->name == $user->name);
    }

    public function test_post_text_truncated_accessor_functions(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->for($category)->create();

        $this->assertTrue(strlen($post->post_text_truncated) == 153);
    }

    public function test_isAuthor_scope_functions(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        Post::factory(10)->for($category)->create();
        $post = Post::factory()->for($category)->for($user)->create();

        $this->actingAs($user);
        $this->assertTrue(Post::isAuthor()->get()->count() == 1);
        $this->assertTrue(Post::isAuthor()->first()->id == $post->id);
    }

    public function test_search_scope_functions(): void
    {
        $category = Category::factory()->create();
        Post::factory(10)->for($category)->create();
        $post1 = Post::factory()->for($category)->create(['title' => 'Test Post Title']);
        $post2 = Post::factory()->for($category)->create(['post_text' => 'Test Post Text']);

        // Searching with post title
        $this->assertTrue(Post::search('Test Post Title')->get()->count() == 1);
        $this->assertTrue(Post::search('Test Post Title')->first()->id == $post1->id);
        // Searching with post text
        $this->assertTrue(Post::search('Test Post Text')->get()->count() == 1);
        $this->assertTrue(Post::search('Test Post Text')->first()->id == $post2->id);
    }

    public function test_filterByCategories_scope_functions(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $category3 = Category::factory()->create();
        $category4 = Category::factory()->create();
        $post1 = Post::factory()->for($category1)->create();
        $post2 = Post::factory()->for($category2)->create();
        $post3 = Post::factory()->for($category3)->create();
        $post4 = Post::factory()->for($category4)->create();

        $filteredPostOneCategory = Post::filterByCategories([$category2->id])->get();
        $filteredPostThreeCategories = Post::filterByCategories([$category1->id, $category3->id, $category4->id])->get();

        // No filter
        $this->assertTrue(Post::filterByCategories([])->get()->count() == 4);
        // Filter by one category
        $this->assertTrue($filteredPostOneCategory->count() == 1);
        $this->assertTrue($filteredPostOneCategory->values()[0]->id == $post2->id);
        // Filter by more than one category
        $this->assertTrue($filteredPostThreeCategories->count() == 3);
        $this->assertTrue($filteredPostThreeCategories->every(fn ($value) => $value->id != $post2->id));
        $this->assertTrue($filteredPostThreeCategories->every(
            fn ($value) => $value->id == $post1->id || $value->id == $post3->id || $value->id == $post4->id
        ));
    }
}
