<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Seeder;

class ElearningCourseSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::updateOrCreate(
            ['title' => 'Xây dựng E-Learning Platform từ A-Z'],
            [
                'description' => 'Khóa học hướng dẫn bạn tạo ra một nền tảng e-learning hoàn chỉnh, từ thiết kế database, xây dựng backend, frontend cho đến triển khai và vận hành.',
                'pillar' => 'delivery',
                'difficulty' => 'advanced',
                'min_level' => 1,
                'xp_reward' => 200,
                'is_published' => true,
            ]
        );

        $modules = [
            [
                'title' => 'Module 1 — Nền tảng & Thiết kế',
                'lessons' => [
                    ['Giới thiệu E-Learning Platform', 'Tổng quan về thị trường e-learning và các tính năng cần có', 'lecture', 0],
                    ['Phân tích yêu cầu & User Flow', 'Xác định các vai trò (admin, giảng viên, học viên) và luồng sử dụng', 'lecture', 1],
                    ['Thiết kế Database Schema', 'Courses, Modules, Lessons, Enrollments, Progress tracking', 'practice', 2],
                    ['Wireframe & UI Design', 'Thiết kế giao diện cho trang khóa học, bài học, và dashboard', 'practice', 3],
                ],
            ],
            [
                'title' => 'Module 2 — Backend Development',
                'lessons' => [
                    ['Setup Project & Migration', 'Khởi tạo project Laravel, tạo migration và models', 'lecture', 0],
                    ['CRUD Khóa học & Bài học', 'Xây dựng admin panel quản lý courses, modules, lessons', 'practice', 1],
                    ['Hệ thống Enrollment & Progress', 'Tracking tiến độ học viên, đánh dấu hoàn thành', 'practice', 2],
                    ['Video Upload & Streaming', 'Tích hợp video player, hỗ trợ YouTube embed và upload trực tiếp', 'lecture', 3],
                ],
            ],
            [
                'title' => 'Module 3 — Frontend & UX',
                'lessons' => [
                    ['Layout Video Player + Playlist', 'Video bên trái, danh sách bài học bên phải', 'lecture', 0],
                    ['Responsive Design', 'Tối ưu cho mobile: list view khi nhỏ, split view khi lớn', 'practice', 1],
                    ['Gamification & XP System', 'Thêm XP, badges, progress bars để tăng engagement', 'lecture', 2],
                ],
            ],
            [
                'title' => 'Module 4 — Deploy & Vận hành',
                'lessons' => [
                    ['Deploy lên VPS', 'Cài đặt server, nginx, SSL, database production', 'lecture', 0],
                    ['Thanh toán & Membership', 'Tích hợp cổng thanh toán cho khóa học trả phí', 'practice', 1],
                    ['Analytics & Báo cáo', 'Dashboard admin: theo dõi enrollment, completion rate, revenue', 'lecture', 2],
                ],
            ],
        ];

        foreach ($modules as $idx => $moduleData) {
            $module = Module::updateOrCreate(
                ['course_id' => $course->id, 'title' => $moduleData['title']],
                ['order_index' => $idx]
            );

            foreach ($moduleData['lessons'] as [$title, $desc, $type, $order]) {
                Lesson::updateOrCreate(
                    ['module_id' => $module->id, 'title' => $title],
                    [
                        'description' => $desc,
                        'lesson_type' => $type,
                        'order_index' => $order,
                        'xp_reward' => 15,
                        'is_locked_by_default' => $order > 0,
                    ]
                );
            }
        }

        $this->command->info("✅ Created e-learning course with " . $course->modules()->count() . " modules");
    }
}
