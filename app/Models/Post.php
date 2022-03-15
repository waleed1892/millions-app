<?php

namespace App\Models;

use App\Notifications\PostCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

class Post extends Model
{
    use HasFactory;

    protected $guarded = [];


    public $incrementing = false;

    public function likes()
    {
        return $this->belongsToMany(User::class, 'posts_likes', 'post_id', 'user_id')->withPivot(['created_at']);
    }

    public function recentlyLikedBy()
    {
        return $this->likes()->orderBy('pivot_created_at', 'desc')->limit(5)->select('name');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    protected static function booted()
    {
        static::created(function ($post) {
            $users = User::where('id', '!=', auth()->id())->get();
            Notification::send($users, new PostCreated($post));
        });
    }
}
