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
                    'sop_content' => $this->sopMarkdown($day, $payload),
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

        return [
            'modality' => $day['modality'],
            'estimated_minutes' => $day['minutes'],
            'learning_objectives' => $day['objectives'],
            'prerequisites' => $day['prerequisites'],
            'ai_actions' => [
                "Đọc tài liệu và workspace {$appName} trước khi hành động.",
                'Lập kế hoạch, liệt kê file sẽ thay đổi và chờ học viên xác nhận.',
                "Thực hiện/build theo phiên bản {$appName} đã khai báo; không đoán API.",
                'Báo cáo file đã đổi, lệnh đã chạy, lỗi còn lại và cách kiểm thử.',
            ],
            'student_actions' => [
                "Mở đúng workspace và model bản sao trong {$appName}.",
                'Đọc kế hoạch, trả lời câu hỏi của AI và xác nhận phạm vi.',
                "Chạy kết quả thật trong {$appName}, cá nhân hóa tên tool và lưu bằng chứng.",
                'Viết 3 câu: yêu cầu AI làm gì, AI sửa file nào, kiểm chứng ra sao.',
            ],
            'student_does_not_need' => [
                'Không cần tự viết C# bằng tay; AI Agent thực hiện phần code theo kế hoạch.',
                'Không cần upload model hoặc dữ liệu dự án mật lên AI.',
            ],
            'sop_steps' => $day['sop'],
            'verification_checklist' => array_merge($day['evidence'], [
                'Bằng chứng là kết quả chạy thật, không chỉ là ảnh code.',
                'Có tên/version cá nhân hóa khi bài yêu cầu.',
                'Đã kiểm tra trên bản sao và ghi rõ giới hạn/capability chưa được test.',
            ]),
            'homework' => [
                'title' => 'Bài nộp ngày '.$day['day'].': '.$day['title'],
                'instructions' => $day['evidence'],
                'deadline_hours' => 24,
            ],
            'evidence_requirements' => $day['evidence'],
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
            'why' => "Kỹ thuật này giúp kỹ sư BIM/MEP giảm thao tác lặp lại trong {$appName} nhưng vẫn kiểm soát model, output và rủi ro.",
            'required_outcome' => $day['outcome'],
            'minimum_knowledge' => "Biết mở {$appName}, đọc model và kiểm tra kết quả; phần code do AI Agent thực hiện theo kế hoạch đã duyệt.",
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
        ];
    }

    protected function prompt(array $day, array $config): string
    {
        $appName = $config['app_name'];
        $versionLine = $config['version_line'];

        return "BỐI CẢNH\n"
            ."- Ứng dụng/version: {$versionLine}\n"
            ."- Đường dẫn bộ kit: KIT_ROOT do tôi cung cấp trên máy này\n"
            ."- Tên học viên: tôi sẽ cung cấp khi cần cá nhân hóa\n"
            ."- Tên tool: theo TOOL_BRIEF.md\n"
            ."- Workspace: thư mục hiện tại\n\n"
            ."MỤC TIÊU\n{$day['outcome']}\n\n"
            ."RÀNG BUỘC AN TOÀN\n"
            ."1. Không xóa file hoặc sửa model gốc.\n"
            ."2. Phải tạo/copy backup trước thao tác ghi hoặc batch operation.\n"
            ."3. Phải đọc file hiện có trước khi sửa.\n"
            ."4. Lập kế hoạch, liệt kê file sẽ đổi và chờ tôi xác nhận.\n"
            ."5. Không giả định {$appName} API nếu chưa kiểm tra version thật.\n"
            ."6. Không đưa dữ liệu dự án mật lên AI.\n\n"
            ."YÊU CẦU AI\n"
            ."1. Phân tích workspace và tài liệu liên quan.\n"
            ."2. Đề xuất kế hoạch, file ảnh hưởng, lệnh build và cách rollback.\n"
            ."3. Chờ tôi xác nhận kế hoạch.\n"
            . "4. Tạo/sửa code hoặc tài liệu theo mục tiêu ngày {$day['day']}.\n"
            ."5. Build Release hoặc chạy kiểm tra phù hợp.\n"
            ."6. Giải thích file đã đổi và lỗi còn tồn tại.\n"
            ."7. Viết checklist kiểm thử trong {$appName}.\n\n"
            ."ĐẦU RA BẮT BUỘC\n"
            ."- File đã thay đổi.\n- Lệnh build/chạy.\n- Cách cài và rollback.\n"
            ."- Cách kiểm tra kết quả trong {$appName}.\n- Các giới hạn hiện tại.\n\n"
            ."Học viên không cần tự viết C#; học viên phải chạy, cá nhân hóa, kiểm chứng và nộp bằng chứng thật.";
    }

    protected function sopMarkdown(array $day, array $payload): string
    {
        $steps = collect($payload['sop_steps'])->values()->map(fn ($step, $i) => ($i + 1).'. '.$step)->implode("\n");
        $checks = collect($payload['verification_checklist'])->map(fn ($item) => '- [ ] '.$item)->implode("\n");
        $evidence = collect($payload['evidence_requirements'])->map(fn ($item) => '- '.$item)->implode("\n");
        $errors = collect($day['errors'])->map(fn ($item) => '- '.$item)->implode("\n");

        return "## {$day['title']}\n\n"
            ."### Hôm nay học gì?\n{$day['description']}\n\n"
            ."### Kết quả bắt buộc\n{$day['outcome']}\n\n"
            ."### SOP thực hiện\n{$steps}\n\n"
            ."### Checklist học viên tự kiểm tra\n{$checks}\n\n"
            ."### Bài tập và bằng chứng cần nộp\n{$evidence}\n\n"
            ."### Prompt copy vào AI Agent\n{$payload['ai_prompt']}\n\n"
            ."### Lỗi thường gặp\n{$errors}\n\n"
            ."### Chấm đạt\nĐạt từ {$payload['pass_score']}/100, đủ bằng chứng, kiểm chứng được và không có Critical Fail. Nếu chưa đạt, sửa đúng lý do mentor ghi rồi nộp lại; không bị trừ XP vì nộp lại.";
    }
}
