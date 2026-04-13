<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Str;

class NewService
{
    public function getList(array $params)
    {
        $query = News::query();

        if (isset($params['title'])) {
            $query->where('title', 'like', '%' . $params['title'] . '%');
        }

        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        return $query->select(['id', 'image', 'title', 'status'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findById($id)
    {
        $news = News::findOrFail($id);
        return $news->only(['id', 'image', 'title', 'status', 'content']);
    }

    public function create($data)
    {
        $data['slug'] = Str::slug($data['title']);
        $news = News::create($data);
        return $news->only(['id', 'image', 'title', 'status']);
    }

    public function update($data, $id)
    {
        $news = News::findOrFail($id);
        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        $news->update($data);
        return $news->only(['id', 'image', 'title', 'status', 'content']);
    }

    public function delete($id)
    {
        $news = News::findOrFail($id);
        $news->delete();
        return $news->id;
    }
}