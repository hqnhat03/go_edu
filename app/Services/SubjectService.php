<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Http\Requests\Subject\CreateRequest;
use App\Http\Requests\Subject\UpdateRequest;
use App\Models\Subject;
use Illuminate\Database\QueryException;
use Str;

class SubjectService
{
    function listSubject(array $params)
    {
        $query = Subject::query();

        if (isset($params['name']) && $params['name'] !== '') {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }

        if (isset($params['status']) && $params['status'] !== '' && $params['status'] !== 'all') {
            $query->where('status', $params['status']);
        }

        if (isset($params['category']) && $params['category'] !== '' && $params['category'] !== 'all') {
            // Find the category name from the slug if it looks like a slug
            $categories = Subject::query()->select('category')->distinct()->pluck('category');
            $matchedCategory = $categories->first(function ($cat) use ($params) {
                return Str::slug($cat) === $params['category'];
            });

            if ($matchedCategory) {
                $query->where('category', $matchedCategory);
            } else {
                $query->where('category', $params['category']);
            }
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortOrder = $params['sort_order'] ?? 'desc';
        $allowedSortFields = ['id', 'name', 'category', 'status', 'created_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $params['per_page'] ?? 10;
        return $query->paginate($perPage);
    }



    function createSubject(array $data)
    {
        try {
            return Subject::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'category' => $data['category'],
                'status' => $data['status'],
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                throw new UserException('Tên môn học đã tồn tại');
            }
            throw $e;
        }
    }

    function updateSubject(array $data, $id)
    {
        $subject = Subject::findOrFail($id);
        try {
            $subject->update([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'category' => $data['category'],
                'status' => $data['status'],
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                throw new UserException('Tên môn học đã tồn tại');
            }
            throw $e;
        }
        return $subject;
    }

    function deleteSubject($id)
    {
        $subject = Subject::findOrFail($id);
        if ($subject->countCourses() > 0) {
            throw new UserException('Không thể xóa môn học đã có khóa học');
        }
        $subject->delete();
        return $subject->id;
    }

    function getCategory()
    {
        return Subject::query()->select('category')
            ->distinct()
            ->pluck('category')
            ->map(function ($category) {
                return [
                    'name' => $category,
                    'slug' => Str::slug($category),
                ];
            });
    }

    public function getPublishedSubject()
    {
        return Subject::select('id', 'name', 'category')
            ->where('status', 'published')
            ->get()
            ->map(function ($subject) {
                $subject->category = Str::slug($subject->category);
                return $subject;
            });
    }
}