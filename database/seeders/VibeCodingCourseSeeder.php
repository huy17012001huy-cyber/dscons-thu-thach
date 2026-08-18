<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Seeder;

class VibeCodingCourseSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::create([
            'title' => 'Vibe Coding — Lập Trình Bằng AI Từ Zero',
            'description' => 'Khóa học dành cho người không biết code. Dùng AI agent để tạo website, app, tool — chỉ cần mô tả bằng tiếng Việt. 10 bài thực hành từ cơ bản đến nâng cao.',
            'pillar' => 'traffic',
            'difficulty' => 'basic',
            'min_level' => 1,
            'xp_reward' => 500,
            'price' => 0,
            'is_published' => true,
        ]);

        // Module 1: Nền tảng
        $m1 = Module::create(['course_id' => $course->id, 'title' => 'Nền tảng Vibe Coding', 'order_index' => 0]);

        Lesson::create([
            'module_id' => $m1->id,
            'title' => 'Vibe Coding là gì? Tại sao ai cũng nên biết',
            'description' => 'Hiểu vibe coding, tại sao nó thay đổi cách mọi người tạo phần mềm, và bạn cần chuẩn bị gì.',
            'lesson_type' => 'lecture',
            'video_url' => 'https://www.youtube.com/watch?v=IuGGJBFPIZQ',
            'xp_reward' => 25,
            'duration_minutes' => 15,
            'order_index' => 0,
            'is_locked_by_default' => false,
        ]);

        Lesson::create([
            'module_id' => $m1->id,
            'title' => 'Cài đặt Cursor — AI Code Editor',
            'description' => 'Hướng dẫn tải, cài đặt và cấu hình Cursor IDE — công cụ chính để vibe coding.',
            'lesson_type' => 'lecture',
            'video_url' => 'https://www.youtube.com/watch?v=yk9lXobJ95E',
            'xp_reward' => 25,
            'duration_minutes' => 12,
            'order_index' => 1,
            'is_locked_by_default' => true,
        ]);

        Lesson::create([
            'module_id' => $m1->id,
            'title' => 'Prompt Engineering cho Coding — Cách ra lệnh cho AI',
            'description' => 'Cách viết prompt rõ ràng để AI hiểu đúng ý bạn. Sai prompt = sai code.',
            'lesson_type' => 'lecture',
            'video_url' => 'https://www.youtube.com/watch?v=bWW2P-29XkE',
            'xp_reward' => 30,
            'duration_minutes' => 18,
            'order_index' => 2,
            'is_locked_by_default' => true,
        ]);

        // Module 2: Thực hành
        $m2 = Module::create(['course_id' => $course->id, 'title' => 'Thực hành — Tạo dự án thật', 'order_index' => 1]);

        Lesson::create([
            'module_id' => $m2->id,
            'title' => 'Tạo Landing Page đầu tiên với AI',
            'description' => 'Dùng AI tạo landing page bán hàng hoàn chỉnh trong 15 phút — không cần biết HTML.',
            'lesson_type' => 'practice',
            'video_url' => 'https://www.youtube.com/watch?v=F_kX0MfbBSQ',
            'xp_reward' => 40,
            'duration_minutes' => 20,
            'order_index' => 0,
            'is_locked_by_default' => true,
        ]);

        Lesson::create([
            'module_id' => $m2->id,
            'title' => 'Build một To-Do App bằng Cursor',
            'description' => 'Tạo app quản lý công việc đầy đủ CRUD — học cách AI viết logic cho bạn.',
            'lesson_type' => 'practice',
            'video_url' => 'https://www.youtube.com/watch?v=1CC8kSYsb-Q',
            'xp_reward' => 40,
            'duration_minutes' => 25,
            'order_index' => 1,
            'is_locked_by_default' => true,
        ]);

        Lesson::create([
            'module_id' => $m2->id,
            'title' => 'Tạo Dashboard Analytics với AI',
            'description' => 'Build dashboard đẹp với biểu đồ, số liệu — chỉ cần mô tả layout bạn muốn.',
            'lesson_type' => 'practice',
            'video_url' => 'https://www.youtube.com/watch?v=gC5TJoaaXbk',
            'xp_reward' => 40,
            'duration_minutes' => 22,
            'order_index' => 2,
            'is_locked_by_default' => true,
        ]);

        Lesson::create([
            'module_id' => $m2->id,
            'title' => 'Build Full-Stack App với Bolt.new',
            'description' => 'Dùng Bolt.new để tạo full-stack app (frontend + backend + database) hoàn toàn bằng prompt.',
            'lesson_type' => 'practice',
            'video_url' => 'https://www.youtube.com/watch?v=nN3ypmFMhbk',
            'xp_reward' => 50,
            'duration_minutes' => 30,
            'order_index' => 3,
            'is_locked_by_default' => true,
        ]);

        // Module 3: Nâng cao
        $m3 = Module::create(['course_id' => $course->id, 'title' => 'Nâng cao — Deploy & Kiếm tiền', 'order_index' => 2]);

        Lesson::create([
            'module_id' => $m3->id,
            'title' => 'Deploy website lên Internet miễn phí',
            'description' => 'Đưa sản phẩm AI vừa tạo lên mạng cho mọi người dùng — Vercel, Netlify, GitHub Pages.',
            'lesson_type' => 'lecture',
            'video_url' => 'https://www.youtube.com/watch?v=sGBdp9r2GSg',
            'xp_reward' => 40,
            'duration_minutes' => 18,
            'order_index' => 0,
            'is_locked_by_default' => true,
        ]);

        Lesson::create([
            'module_id' => $m3->id,
            'title' => 'Tích hợp thanh toán & thu tiền',
            'description' => 'Thêm form thu tiền vào sản phẩm — SePay QR, Stripe, hoặc Google Forms đơn giản.',
            'lesson_type' => 'lecture',
            'video_url' => 'https://www.youtube.com/watch?v=OJFxPvOP0Ew',
            'xp_reward' => 40,
            'duration_minutes' => 20,
            'order_index' => 1,
            'is_locked_by_default' => true,
        ]);

        Lesson::create([
            'module_id' => $m3->id,
            'title' => 'Vibe Coding Workflow — Quy trình làm dự án thật',
            'description' => 'Tổng kết: quy trình từ ý tưởng → prompt → code → deploy → thu tiền. Áp dụng cho mọi dự án.',
            'lesson_type' => 'lecture',
            'video_url' => 'https://www.youtube.com/watch?v=LGkPYU_e2no',
            'xp_reward' => 50,
            'duration_minutes' => 25,
            'order_index' => 2,
            'is_locked_by_default' => true,
        ]);
    }
}
