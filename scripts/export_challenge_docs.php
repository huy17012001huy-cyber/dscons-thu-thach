<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$challenges = [
    '21-ngay-lam-tool-revit-voi-ai-agent' => 'revit-21-days',
    '21-ngay-chinh-phuc-tool-autocad-bang-ai-agent' => 'autocad-21-days',
    '21-ngay-chinh-phuc-tool-navisworks-bang-ai-agent' => 'navisworks-21-days',
];

foreach ($challenges as $slug => $directory) {
    $challenge = App\Models\Expedition::withoutGlobalScopes()->where('slug', $slug)->firstOrFail();
    $tasks = $challenge->tasks()->withoutGlobalScopes()->orderBy('day_number')->get();
    $root = base_path('docs/challenges/'.$directory);

    foreach ($tasks as $task) {
        $payload = is_array($task->instruction_payload) ? $task->instruction_payload : [];
        if ($payload === []) {
            continue;
        }

        $bullets = static fn (array $items): string => collect($items)
            ->map(fn ($item) => '- '.trim((string) $item))
            ->implode("\n");
        $steps = collect($payload['sop_steps'] ?? [])
            ->values()
            ->map(fn ($step, $index) => ($index + 1).'. '.trim((string) $step))
            ->implode("\n");
        $evidence = $bullets($payload['evidence_requirements'] ?? []);
        $errors = $bullets(array_slice($payload['common_errors'] ?? [], 0, 2));
        $live = ($payload['modality'] ?? '') === 'live' ? 'Livestream' : 'Video ngắn';
        $share = $payload['share_to_feed'] ?? null;

        $content = "# Ngày {$task->day_number} — {$task->title}\n\n"
            ."**Hình thức:** {$live} · **Thời lượng:** {$payload['estimated_minutes']} phút\n\n"
            ."## Hôm nay làm gì?\n\n{$task->description}\n\n"
            ."## Kết quả cần đạt\n\n{$payload['required_outcome']}\n\n"
            ."## AI làm gì?\n\n{$bullets($payload['ai_actions'] ?? [])}\n\n"
            ."## Học viên kiểm tra gì?\n\n{$bullets($payload['student_actions'] ?? [])}\n\n"
            ."## SOP duy nhất\n\n{$steps}\n\n"
            ."## Minh chứng cần nộp\n\n{$evidence}\n\n"
            .($share ? "**Đăng lên Bảng tin:** {$share}\n\n" : '')
            ."## Điều kiện Đạt\n\nĐạt từ {$payload['pass_score']}/100, có ảnh kết quả thật và không có lỗi nghiêm trọng. Nếu chưa đạt, sửa đúng lý do mentor ghi rồi nộp lại; không bị trừ XP.\n\n"
            ."## Lỗi thường gặp\n\n{$errors}\n";

        file_put_contents($root.'/day-'.str_pad((string) $task->day_number, 2, '0', STR_PAD_LEFT).'.md', $content);
    }
}

fwrite(STDOUT, "Exported challenge curriculum docs.\n");
