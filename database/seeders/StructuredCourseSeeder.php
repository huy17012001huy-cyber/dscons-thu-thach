<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonTask;
use App\Models\Module;
use Illuminate\Database\Seeder;

class StructuredCourseSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::create([
            'title' => 'Nền tảng tư duy AI Agent',
            'description' => 'Tư duy đúng, chiến lược đúng, cách làm đúng về Nhân sự AI (AI Agent). Khóa học giúp bạn hiểu và vận hành AI Agent hiệu quả trong công việc kinh doanh.',
            'pillar' => 'offer',
            'difficulty' => 'basic',
            'min_level' => 1,
            'xp_reward' => 500,
            'aip_reward' => 100,
            'is_published' => true,
        ]);

        // Module 1: Tuần 1
        $m1 = Module::create(['course_id' => $course->id, 'title' => 'Tuần 1 — Nền tảng tư duy AI', 'order_index' => 0]);

        $lessons1 = [
            ['title' => 'Tập ra lệnh cho Coding Agent làm việc', 'tasks' => [
                ['title' => 'Yêu cầu bài số 1 là gì?', 'description' => 'Agent phải giúp học viên viết code tạo ra một trang web Profile Card thật đẹp.'],
                ['title' => 'Agent đã sử dụng công nghệ gì để hoàn thành nhiệm vụ?', 'description' => 'Mô tả tech-stack mà Agent đã chọn.'],
                ['title' => 'Tính năng chụp hình được thực hiện cách nào?', 'description' => 'Giải thích cách Agent implement tính năng screenshot.'],
            ]],
            ['title' => 'Tích luỹ kiến thức trong quá trình làm việc với Agent', 'tasks' => [
                ['title' => 'Bạn đã học được gì từ Agent trong bài 1?', 'description' => 'Chia sẻ 3 điều bạn học được.'],
                ['title' => 'Làm sao để Agent nhớ context tốt hơn?', 'description' => 'Thử nghiệm và chia sẻ kết quả.'],
            ]],
            ['title' => 'Hiểu thấu năng lực của Agent', 'tasks' => [
                ['title' => 'Agent có thể làm gì mà bạn không ngờ tới?', 'description' => 'Thử 3 task khác nhau và ghi nhận.'],
                ['title' => 'Giới hạn nào của Agent bạn phát hiện?', 'description' => 'Liệt kê những gì Agent chưa làm tốt.'],
            ]],
            ['title' => 'Phân biệt AI và AI Agent', 'tasks' => [
                ['title' => 'Sự khác biệt chính giữa AI và AI Agent?', 'description' => 'Giải thích bằng ví dụ cụ thể.'],
                ['title' => 'Khi nào nên dùng AI thường, khi nào nên dùng Agent?', 'description' => 'Đưa ra 3 scenario thực tế.'],
            ]],
            ['title' => 'Vòng lặp Agent', 'tasks' => [
                ['title' => 'Mô tả vòng lặp Agent Loop', 'description' => 'Vẽ hoặc mô tả quá trình Agent lặp lại để hoàn thành task.'],
                ['title' => 'Thử nghiệm vòng lặp với một task phức tạp', 'description' => 'Giao cho Agent một task cần nhiều bước, quan sát và ghi nhận.'],
            ]],
        ];

        $prevLesson = null;
        foreach ($lessons1 as $i => $data) {
            $lesson = Lesson::create([
                'module_id' => $m1->id,
                'title' => $data['title'],
                'lesson_type' => 'lecture',
                'xp_reward' => 25,
                'duration_minutes' => rand(15, 45),
                'order_index' => $i,
                'is_locked_by_default' => $i > 0,
            ]);

            foreach ($data['tasks'] as $j => $task) {
                LessonTask::create([
                    'lesson_id' => $lesson->id,
                    'title' => $task['title'],
                    'description' => $task['description'],
                    'type' => 'text',
                    'order_index' => $j,
                    'is_required' => true,
                ]);
            }

            $prevLesson = $lesson;
        }

        // Practice lesson
        $practice = Lesson::create([
            'module_id' => $m1->id,
            'title' => 'Thực hành cộng tác với Agent',
            'lesson_type' => 'practice',
            'xp_reward' => 50,
            'order_index' => count($lessons1),
            'is_locked_by_default' => true,
        ]);

        $challenges = [
            'Làm web tặng bạn bè',
            'Bí mật trong Prompt',
            'Giải mã nội dung',
            'Hiểm hoạ tiềm ẩn',
            'Sức mạnh của Endpoint',
            'Hiểu về bảo mật',
            'Tạo Chatbot AI',
        ];

        foreach ($challenges as $k => $title) {
            LessonTask::create([
                'lesson_id' => $practice->id,
                'title' => $title,
                'description' => 'Hoàn thành thử thách #' . ($k + 1) . ' cùng Agent.',
                'type' => 'link',
                'order_index' => $k,
                'is_required' => true,
            ]);
        }

        // Module 2: Tuần 2
        $m2 = Module::create(['course_id' => $course->id, 'title' => 'Tuần 2 — Ứng dụng thực chiến', 'order_index' => 1]);

        $lessons2 = [
            'Trang bị công cụ cho AI Agent',
            'Xây dựng workflow tự động',
            'Agent trong Marketing',
            'Agent trong vận hành doanh nghiệp',
        ];

        foreach ($lessons2 as $i => $title) {
            $lesson = Lesson::create([
                'module_id' => $m2->id,
                'title' => $title,
                'lesson_type' => 'lecture',
                'xp_reward' => 30,
                'duration_minutes' => rand(20, 40),
                'order_index' => $i,
                'is_locked_by_default' => $i > 0,
            ]);

            LessonTask::create([
                'lesson_id' => $lesson->id,
                'title' => 'Bài tập thực hành: ' . $title,
                'description' => 'Áp dụng kiến thức bài học vào một case study thực tế.',
                'type' => 'text',
                'order_index' => 0,
                'is_required' => true,
            ]);
        }

        echo "✅ Created structured course: {$course->title}\n";
        echo "   Modules: 2, Lessons: " . ($course->modules()->count()) . "\n";
        echo "   Tasks: " . LessonTask::whereIn('lesson_id', Lesson::whereIn('module_id', $course->modules()->pluck('id'))->pluck('id'))->count() . "\n";
    }
}
