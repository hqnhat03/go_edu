<?php

namespace App\Services;

use App\Exceptions\UserException;
use App\Helpers\StringHelper;
use App\Http\Requests\Level\CreateRequest;
use App\Http\Requests\Level\UpdateRequest;
use App\Models\Level;
use Illuminate\Database\QueryException;
use Str;

class LevelService
{
    public function listLevel(array $params)
    {
        $query = Level::query();

        if (isset($params['level'])) {
            $query->where('levels.level', 'like', '%' . $params['level'] . '%');
        }

        if (isset($params['status'])) {
            $query->where('levels.status', $params['status']);
        }

        if (isset($params['education_level'])) {
            $query->where('levels.education_level', $params['education_level']);
        }

        return $query->get();
    }

    public function createLevel(array $data)
    {
        try {
            $level = StringHelper::uppercaseFirst($data["level"]);
            $slug = Str::slug($data["level"]);
            $level = Level::create([
                "level" => $level,
                "slug" => $slug,
                "status" => $data["status"],
                "education_level" => $data["education_level"],
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                throw new UserException("Trình độ đào tạo đã tồn tại");
            }
            throw $e;
        }
        return $level;
    }

    public function updateLevel($data, $id)
    {
        try {
            $level = Level::findOrFail($id);
            $level_name = StringHelper::uppercaseFirst($data["level"]);
            $slug = Str::slug($data["level"]);
            $level->update([
                "level" => $level_name,
                "slug" => $slug,
                "status" => $data["status"],
                "education_level" => $data["education_level"],
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                throw new UserException("Trình độ đào tạo đã tồn tại");
            }
            throw $e;
        }

        return $level;
    }

    public function deleteLevel($id)
    {
        $level = Level::findOrFail($id);
        if ($level->countCourses() > 0) {
            throw new UserException("Không thể xóa trình độ đào tạo vì đã có khóa học");
        }
        $level->delete();
        return $level->id;
    }
}