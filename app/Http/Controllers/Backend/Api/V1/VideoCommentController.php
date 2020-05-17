<?php

/*
 * This file is part of the Qsnh/meedu.
 *
 * (c) XiaoTeng <616896861@qq.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Http\Controllers\Backend\Api\V1;

use App\Models\VideoComment;
use Illuminate\Http\Request;
use App\Services\Course\Models\Video;
use App\Services\Course\Models\Course;

class VideoCommentController extends BaseController
{
    public function index(Request $request)
    {
        $courseId = $request->input('course_id');
        $videoId = $request->input('video_id');
        $comments = VideoComment::with(['user', 'video.course'])
            ->when($courseId, function ($query) use ($courseId) {
                $videoIds = Video::query()->select(['id'])->where('course_id', $courseId)->get()->pluck('id');
                $query->whereIn('video_id', $videoIds);
            })
            ->when($videoId, function ($query) use ($videoId) {
                $query->where('video_id', $videoId);
            })
            ->orderByDesc('id')
            ->paginate($request->input('size', 20));

        $courses = Course::query()->select(['id', 'title'])->get();
        $videos = Video::query()->select(['id', 'title', 'course_id'])->get()->groupBy('course_id');

        return $this->successData([
            'data' => $comments,
            'courses' => $courses,
            'videos' => $videos,
        ]);
    }

    public function destroy($id)
    {
        VideoComment::destroy($id);

        return $this->success();
    }
}
