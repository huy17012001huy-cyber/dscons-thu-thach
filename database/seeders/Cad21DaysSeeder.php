<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\ChallengeTask;
use App\Models\Expedition;
use Illuminate\Database\Seeder;
use RuntimeException;

abstract class Cad21DaysSeeder extends Seeder
{
    final public function run(): void
    {
        $config = $this->config();
        $brand = app()->bound('brand') ? brand() : Brand::query()->findOrFail(1);
        app()->instance('brand', $brand);

        $source = Expedition::withoutGlobalScopes()
            ->where('brand_id', $brand->id)
            ->where('slug', '21-ngay-lam-tool-revit-voi-ai-agent')
            ->first();

        if (!$source) {
            throw new RuntimeException(
                'Không tìm thấy challenge Revit để đồng bộ giá và quyền truy cập. '
                .'Hãy chạy challenge:seed-revit-21 trước.'
            );
        }

        $expedition = Expedition::withoutGlobalScopes()->firstOrNew([
            'brand_id' => $brand->id,
            'slug' => $config['slug'],
        ]);

        $expedition->fill([
            'brand_id' => $brand->id,
            'title' => $config['title'],
            'description' => $config['description'],
            'boss_name' => $expedition->boss_name ?: $config['boss_name'],
            'difficulty' => $expedition->difficulty ?: $source->difficulty,
            'required_days' => 21,
            'max_members' => $source->max_members ?: 999,
            'created_by' => $expedition->created_by ?: $source->created_by,
            'leader_id' => $expedition->leader_id ?: $source->leader_id,
            'status' => $expedition->status ?: 'open',
            'price' => $source->price,
            'access_tier' => $source->access_tier,
            'deposit_aip' => $source->deposit_aip,
        ]);
        $expedition->save();

        foreach ($this->days() as $day) {
            $payload = $this->payload($day, $config);

            ChallengeTask::withoutGlobalScopes()->updateOrCreate(
                ['expedition_id' => $expedition->id, 'day_number' => $day['day']],
                [
                    'label' => $day['label'],
                    'title' => $day['title'],
                    'description' => $day['description'],
                    'sop_content' => $this->sopMarkdown($day, $payload, $config),
                    'video_url' => null,
                    'meeting_at' => null,
                    'evidence_type' => 'text',
                    'evidence_label' => implode(' ', $payload['evidence_requirements']),
                    'duration_hours' => 24,
                    'quiz_json' => null,
                    'instruction_payload' => $payload,
                ]
            );
        }

        $this->command?->info("Đã seed {$expedition->tasks()->count()} task cho {$expedition->slug}.");
    }

    abstract protected function config(): array;

    abstract protected function days(): array;

    protected function day(
        int $day,
        string $modality,
        string $track,
        string $title,
        string $label,
        int $minutes,
        string $description,
        array $objectives,
        array $prerequisites,
        array $sop,
        array $evidence,
        array $errors,
        string $outcome,
        array $questions
    ): array {
        return compact(
            'day', 'modality', 'track', 'title', 'label', 'minutes', 'description',
            'objectives', 'prerequisites', 'sop', 'evidence', 'errors', 'outcome', 'questions'
        );
    }

    protected function guidedDay(
        int $day,
        string $modality,
        string $track,
        string $title,
        string $label,
        int $minutes,
        string $description,
        array $objectives,
        array $prerequisites,
        array $actions,
        array $evidence,
        array $errors,
        string $outcome,
        array $questions
    ): array {
        return $this->day(
            $day,
            $modality,
            $track,
            $title,
            $label,
            $minutes,
            $description,
            $objectives,
            $prerequisites,
            array_merge([
                'Mở đúng workspace và đọc tài liệu liên quan trước khi làm.',
                'Dán prompt của ngày vào AI Agent; yêu cầu AI lập kế hoạch và liệt kê file sẽ đổi.',
                'Chờ học viên xác nhận kế hoạch trước khi AI sửa code hoặc tạo artifact.',
            ], $actions, [
                'Build/chạy theo đúng version và dùng model/file bản sao.',
                'Đối chiếu output với ứng dụng, lưu log và ghi rõ giới hạn.',
                'Nộp bằng chứng chạy thật cùng ba câu reflection về việc AI đã làm.',
            ]),
            $evidence,
            $errors,
            $outcome,
            $questions
        );
    }

    protected function payload(array $day, array $config): array
    {
        $reviewMode = $day['modality'] === 'live' ? 'live' : ($day['day'] >= 7 ? 'mentor' : 'auto');
        $appName = $config['app_name'];
        $evidence = $this->simpleEvidence($day, $config);

        return [
            'modality' => $day['modality'],
            'estimated_minutes' => $day['minutes'],
            'learning_objectives' => array_slice($day['objectives'], 0, 2),
            'prerequisites' => array_slice($day['prerequisites'], 0, 1),
            'ai_actions' => [
                "Đọc workspace, hiểu mục tiêu và đề xuất cách làm trong {$appName}.",
                'Thực hiện phần code/tài liệu, build và báo lại file đã thay đổi.',
            ],
            'student_actions' => [
                "Mở đúng workspace và bản sao trong {$appName}; đọc kế hoạch trước khi chạy.",
                "Kiểm tra kết quả thật trong {$appName} và chụp minh chứng dễ đối chiếu.",
            ],
            'student_does_not_need' => [
                'Không cần tự viết C# bằng tay; AI Agent thực hiện phần code theo kế hoạch.',
                'Không cần upload model hoặc dữ liệu dự án mật lên AI.',
            ],
            'sop_steps' => array_values(array_slice($day['sop'], 0, 4)),
            'verification_checklist' => $evidence,
            'homework' => [
                'title' => 'Bài nộp ngày '.$day['day'].': '.$day['title'],
                'instructions' => $evidence,
                'deadline_hours' => 24,
            ],
            'evidence_requirements' => $evidence,
            'rubric' => [
                ['key' => 'evidence_completeness', 'label' => 'Đủ bằng chứng bắt buộc', 'points' => 30],
                ['key' => 'correctness', 'label' => 'Kết quả đúng/đối chiếu được', 'points' => 40],
                ['key' => 'personalization', 'label' => 'Có cá nhân hóa theo học viên', 'points' => 15],
                ['key' => 'verification', 'label' => 'Mô tả cách kiểm chứng', 'points' => 15],
            ],
            'milestone_rubric' => [
                ['key' => 'real_functionality', 'label' => 'Chức năng chạy thật', 'points' => 30],
                ['key' => 'model_correctness', 'label' => 'Đúng trên model', 'points' => 25],
                ['key' => 'invalid_input', 'label' => 'Xử lý input lỗi', 'points' => 15],
                ['key' => 'reinstallable', 'label' => 'Cài và chạy lại được', 'points' => 15],
                ['key' => 'ai_understanding', 'label' => 'Hiểu quy trình AI', 'points' => 15],
            ],
            'common_errors' => $day['errors'],
            'review_mode' => $reviewMode,
            'pass_score' => 70,
            'track' => $day['track'],
            'why' => 'AI làm phần nặng; học viên hiểu mục tiêu và kiểm tra kết quả thật.',
            'required_outcome' => $day['outcome'],
            'ai_prompt' => $this->prompt($day, $config),
            'error_prompts' => [
                'Prompt sửa build: “Đọc đúng log lỗi, giải thích nguyên nhân, chỉ sửa file liên quan, build lại và nêu test regression.”',
                "Prompt sửa API: “Kiểm tra version/API thật của {$appName} trước khi sửa; không đoán method; nêu fallback nếu capability không hỗ trợ.”",
                'Prompt giải thích: “Giải thích phần code vừa sửa bằng ngôn ngữ kỹ sư BIM/MEP và chỉ ra cách tôi kiểm chứng trong phần mềm.”',
            ],
            'safety_constraints' => [
                'Không xóa file hoặc sửa model gốc.',
                'Tạo/copy backup trước batch operation hoặc thao tác ghi.',
                'Đọc file hiện có và lập kế hoạch trước khi sửa.',
                'Không đưa model/dữ liệu mật của công ty lên AI Agent.',
            ],
            'mentor_questions' => $day['questions'],
            'share_to_feed' => $this->shareInstruction($day['day']),
        ];
    }

    /** @return array<int, string> */
    protected function simpleEvidence(array $day, array $config): array
    {
        $videoDays = [7, 14, 21];
        $productDays = [9, 10, 11, 12, 13, 16, 18, 19, 20, 21];
        $evidence = ['Ảnh màn hình kết quả của ngày '.$day['day'].'.'];

        if (in_array($day['day'], $videoDays, true)) {
            $evidence[] = 'Video ngắn chứng minh thao tác chính chạy thật.';
        }
        if (in_array($day['day'], $productDays, true)) {
            $evidence[] = 'Ảnh sản phẩm đã đăng lên Bảng tin cộng đồng.';
        }

        if ($config['app_name'] === 'Navisworks' && $day['day'] === 11) {
            $evidence[] = 'Ảnh capability đã kiểm tra; không kết luận tính năng chưa chạy thật.';
        }

        return $evidence;
    }

    protected function shareInstruction(int $day): ?string
    {
        return in_array($day, [9, 10, 11, 12, 13, 16, 18, 19, 20, 21], true)
            ? 'Đăng ảnh sản phẩm hoặc kết quả chạy thật, kèm 2–3 câu bạn đã dùng AI như thế nào.'
            : null;
    }

    protected function prompt(array $day, array $config): string
    {
        $appName = $config['app_name'];
        $versionLine = $config['version_line'];

        return "BỐI CẢNH\n"
            ."- Ứng dụng/version: {$versionLine}\n"
            ."- Kit: KIT_ROOT do tôi cung cấp trên máy này\n"
            ."- Mục tiêu ngày {$day['day']}: {$day['outcome']}\n\n"
            ."HÃY LÀM THEO THỨ TỰ\n"
            ."1. Đọc workspace và file liên quan.\n"
            ."2. Nói ngắn gọn kế hoạch, file sẽ đổi và cách kiểm tra; chờ tôi xác nhận.\n"
            ."3. Thực hiện mục tiêu, build/chạy theo version thật.\n"
            ."4. Báo lại file đã đổi, kết quả và lỗi còn lại.\n\n"
            ."AN TOÀN\n"
            ."- Không xóa file, không sửa bản vẽ/model gốc và không đưa dữ liệu mật lên AI.\n"
            ."- Backup trước thao tác ghi; không đoán {$appName} API khi chưa kiểm tra version.\n\n"
            ."Tôi không cần tự viết C#, nhưng tôi sẽ mở {$appName}, kiểm tra kết quả và chụp bằng chứng.";
    }

    protected function sopMarkdown(array $day, array $payload, array $config): string
    {
        $steps = collect($payload['sop_steps'])->values()->map(fn ($step, $i) => ($i + 1).'. '.$step)->implode("\n");
        $checks = collect($payload['verification_checklist'])->map(fn ($item) => '- [ ] '.$item)->implode("\n");
        $evidence = collect($payload['evidence_requirements'])->map(fn ($item) => '- '.$item)->implode("\n");
        $errors = collect($day['errors'])->map(fn ($item) => '- '.$item)->implode("\n");

        $versionNote = $config['app_name'] === 'Navisworks'
            ? "\n\n### Giới hạn cần nhớ\nManage 2024 là mốc runtime đã kiểm tra. Simulate không dùng cho Clash. Không tạo nút giả cho capability chưa có bằng chứng runtime."
            : '';

        return "## {$day['title']}\n\n"
            ."### Hôm nay học gì?\n{$day['description']}\n\n"
            ."### Kết quả bắt buộc\n{$day['outcome']}\n\n"
            ."### SOP thực hiện\n{$steps}\n\n"
            ."### Checklist học viên tự kiểm tra\n{$checks}\n\n"
            ."### Bài tập và bằng chứng cần nộp\n{$evidence}\n\n"
            ."### Prompt copy vào AI Agent\n{$payload['ai_prompt']}\n\n"
            ."### Lỗi thường gặp\n{$errors}\n\n"
            ."### Chấm đạt\nĐạt từ {$payload['pass_score']}/100, có ảnh kết quả thật và không có Critical Fail. Nếu chưa đạt, sửa đúng lý do rồi nộp lại; không bị trừ XP.".$versionNote;
    }
}
