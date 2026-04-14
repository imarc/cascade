<?php

declare(strict_types=1);

namespace Imarc\Cascade\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Imarc\Cascade\Tests\Fixtures\Category;
use Imarc\Cascade\Tests\Fixtures\Comment;
use Imarc\Cascade\Tests\Fixtures\HardComment;
use Imarc\Cascade\Tests\Fixtures\Image;
use Imarc\Cascade\Tests\Fixtures\Post;
use Imarc\Cascade\Tests\Fixtures\Tag;

class CascadesSoftDeletesTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_deleting_parent_soft_deletes_has_many_children(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $comment = Comment::create(['post_id' => $post->id, 'body' => 'A']);

        $post->delete();

        $this->assertSoftDeleted($post);
        $this->assertSoftDeleted($comment);
        $this->assertSame(1, Comment::withTrashed()->count());
    }

    public function test_restoring_parent_restores_trashed_children_for_relation(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $comment = Comment::create(['post_id' => $post->id, 'body' => 'A']);
        $post->delete();

        $post->restore();

        $this->assertFalse($post->fresh()->trashed());
        $this->assertFalse($comment->fresh()->trashed());
    }

    public function test_nested_cascade_soft_deletes(): void
    {
        $category = Category::create(['name' => 'News']);
        $post = Post::create(['category_id' => $category->id, 'title' => 'P']);
        $comment = Comment::create(['post_id' => $post->id, 'body' => 'C']);

        $category->delete();

        $this->assertSoftDeleted($category);
        $this->assertSoftDeleted($post->fresh());
        $this->assertSoftDeleted($comment->fresh());
    }

    public function test_nested_restore(): void
    {
        $category = Category::create(['name' => 'News']);
        $post = Post::create(['category_id' => $category->id, 'title' => 'P']);
        $comment = Comment::create(['post_id' => $post->id, 'body' => 'C']);
        $category->delete();

        $category->restore();

        $this->assertFalse($category->fresh()->trashed());
        $this->assertFalse($post->fresh()->trashed());
        $this->assertFalse($comment->fresh()->trashed());
    }

    public function test_skips_children_without_soft_deletes(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $hard = HardComment::create(['post_id' => $post->id, 'body' => 'H']);

        $post->delete();

        $this->assertSoftDeleted($post);
        $this->assertDatabaseHas('hard_comments', ['id' => $hard->id]);
    }

    public function test_belongs_to_many_relation_is_skipped(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $tag = Tag::create(['name' => 't']);
        $post->tags()->attach($tag->id);

        $post->delete();

        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
        $this->assertDatabaseHas('post_tag', ['post_id' => $post->id, 'tag_id' => $tag->id]);
    }

    public function test_morph_many_soft_deletes(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $image = Image::create(['imageable_type' => Post::class, 'imageable_id' => $post->id, 'path' => '/a.jpg']);

        $post->delete();

        $this->assertSoftDeleted($image->fresh());
    }

    public function test_force_delete_cascades_force_delete_to_soft_deletable_children(): void
    {
        $post = Post::create(['title' => 'Hello']);
        $comment = Comment::create(['post_id' => $post->id, 'body' => 'A']);

        $post->forceDelete();

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }
}
