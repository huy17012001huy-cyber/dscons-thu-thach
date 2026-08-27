<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\ChallengeTask;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Source of truth for the learner-friendly DSCons Revit challenge. */
class Revit21DaysSeeder extends Seeder
{
    private const SLUG = '21-ngay-lam-tool-revit-voi-ai-agent';

    public function run(): void
    {
        $brand = app()->bound('brand') ? brand() : Brand::query()->findOrFail(1);
        app()->instance('brand', $brand);

        $leader = User::query()->where('is_admin', true)->orderBy('id')->first();
        if (!$leader) {
            $leader = User::query()->firstOrCreate(
                ['email' => 'revit-mentor@dscons.test'],
                ['name' => 'DSCons Revit Mentor', 'username' => 'dscons-revit-mentor']
            );
            $leader->forceFill([
                'is_admin' => true,
                'email_verified_at' => now(),
                'class' => $leader->class ?: 'delivery_assassin',
            ])->save();
        }

        $expedition = Expedition::withoutGlobalScopes()->firstOrNew([
            'brand_id' => $brand->id,
            'slug' => self::SLUG,
        ]);
        $expedition->fill([
            'brand_id' => $brand->id,
            'title' => '21 Ngày Chinh Phục Tool Revit bằng AI Agent',
            'description' => 'Lộ trình thực chiến dành cho kỹ sư BIM/MEP: chọn một việc lặp lại trong Revit, giao AI Agent thực hiện phần kỹ thuật, rồi tự kiểm tra để tạo ra một tool chạy thật.',
            'boss_name' => $expedition->boss_name ?: 'DSCons Revit Mentor',
            'difficulty' => $expedition->difficulty ?: 'normal',
            'required_days' => 21,
            'max_members' => $expedition->max_members ?: 999,
            'created_by' => $expedition->created_by ?: $leader->id,
            'leader_id' => $expedition->leader_id ?: $leader->id,
            'status' => $expedition->status ?: 'open',
        ]);
        $expedition->save();

        foreach ($this->days() as $day) {
            $payload = $this->payload($day);
            $task = ChallengeTask::withoutGlobalScopes()->firstOrNew([
                'expedition_id' => $expedition->id,
                'day_number' => $day['day'],
            ]);
            $task->fill([
                'expedition_id' => $expedition->id,
                'day_number' => $day['day'],
                'label' => $day['label'],
                'title' => $day['title'],
                'description' => $day['description'],
                'sop_content' => $this->sopMarkdown($day, $payload),
                // Existing admin-entered links/schedules are intentionally preserved.
                'evidence_type' => 'text',
                'evidence_label' => implode(' ', $payload['evidence_requirements']),
                'duration_hours' => 24,
                'instruction_payload' => $payload,
            ]);
            $task->save();
        }

        $this->command?->info("Đã seed {$expedition->tasks()->count()} task cho {$expedition->slug}.");
    }

    /** @return array<int, array<string, mixed>> */
    private function days(): array
    {
        $rows = [
            [1, 'live', 'common', 'Chọn và cài AI Agent để bắt đầu', 'Khởi động', 90, 'Bạn sẽ chọn một AI Agent, tải đúng phần mềm, đăng nhập và kiểm tra nó đọc được workspace Bộ Kit.', 'AI Agent hoạt động và bạn biết chính xác nơi bắt đầu.', 'Chọn Codex, Claude Code hoặc Google Antigravity; không cần cài cả ba.', 'AI Agent đã mở được workspace và trả lời đúng mục tiêu khóa học.'],
            [2, 'practice', 'common', 'Giao việc cho AI theo từng bước', 'Cách làm việc', 35, 'Bạn học cách nói rõ mục tiêu, yêu cầu AI lập kế hoạch trước và chỉ bắt đầu sau khi bạn đồng ý.', 'Có một kế hoạch làm tool mà bạn hiểu và có thể kiểm tra.', 'Dán SOP vào AI, yêu cầu đọc trước, nêu file sẽ đụng tới và chờ bạn xác nhận.', 'AI đưa ra kế hoạch trước khi thực hiện, không sửa mù.'],
            [3, 'practice', 'common', 'Kiểm tra máy và phiên bản Revit', 'Kiểm tra môi trường', 40, 'Bạn kiểm tra Revit, .NET, Build Tools, Git và phiên bản đang dùng để tránh cài nhầm.', 'Biết môi trường của mình đang đạt hay còn lỗi nào.', 'Chạy kiểm tra chỉ đọc; nếu có lỗi, gửi đúng phần log cần thiết cho AI.', 'Báo cáo có PASS/FAIL và phiên bản Revit thật.'],
            [4, 'practice', 'common', 'Đọc model Revit mà không sửa model', 'Khảo sát model', 45, 'AI sẽ đọc model mẫu qua MCP hoặc cơ chế kết nối của Bộ Kit. Ngày này chỉ khảo sát, không thay đổi dữ liệu.', 'AI đếm được Duct, Pipe và Equipment để bạn đối chiếu.', 'Mở model mẫu, bật chế độ chỉ đọc và dừng ngay nếu AI yêu cầu thao tác ghi.', 'Số lượng AI trả về khớp Schedule hoặc phép đếm trong Revit.'],
            [5, 'practice', 'common', 'Xem thông tin của phần tử MEP', 'Thông tin phần tử', 45, 'Bạn yêu cầu AI xem Category, Family, Type, Level, System, Size, Mark và Comments của phần tử.', 'Có bảng thông tin dễ đọc của 5–10 phần tử.', 'Chọn phần tử, yêu cầu AI ghi “chưa có” khi thiếu thông tin và đối chiếu từng dòng.', 'Ít nhất 5 thông tin được kiểm tra lại trong Properties hoặc Schedule.'],
            [6, 'practice', 'common', 'Lọc đúng phần tử cần tìm', 'Lọc dữ liệu', 50, 'Bạn biến một câu hỏi công việc thành điều kiện lọc, ví dụ Duct lớn hơn 300 mm ở Level 2 thuộc Supply Air.', 'Có kết quả lọc đúng và giải thích được vì sao phần tử được chọn.', 'Nói câu hỏi bằng ngôn ngữ công việc; để AI chuyển thành điều kiện; đối chiếu độc lập.', 'Kết quả không nhầm Category, Level, hệ thống hoặc đơn vị.'],
            [7, 'practice', 'common', 'Chọn bài toán tool của riêng bạn', 'Chọn bài toán', 60, 'Bạn chọn một việc lặp lại trong công việc MEP và mô tả nó bằng biểu mẫu trên website, không cần tạo file kỹ thuật.', 'Có bài toán đủ nhỏ để làm MVP trong hai tuần.', 'Mô tả việc làm tay, đầu vào, kết quả mong muốn, người dùng và điều không làm trong khóa.', 'Bài toán có đầu vào, đầu ra và cách đo thành công; đăng bài xin góp ý lên Bảng tin.'],
            [8, 'live', 'common', 'Chốt phạm vi với mentor', 'Livestream', 120, 'Mentor cùng bạn thu nhỏ đề tài, kiểm tra tính khả thi và viết lại yêu cầu để AI có thể triển khai.', 'Đề tài được xác nhận và có tiêu chí “tool chạy được”.', 'Trình bày vấn đề → đầu vào → thao tác → kết quả → giới hạn; ghi lại góp ý và cập nhật biểu mẫu.', 'Mentor xác nhận một nhánh và một bản demo cụ thể.'],
            [9, 'practice', 'common', 'Tạo nút tool đầu tiên trong Revit', 'Nền tảng tool', 60, 'AI tạo phần nền tảng, nút trên Ribbon và cửa sổ giới thiệu. Bạn chỉ cần build, cài và bấm thử.', 'Add-in xuất hiện trong Revit với tên của bạn.', 'Khai báo đúng phiên bản Revit, xem danh sách thay đổi, build rồi cài trên model mẫu.', 'Ribbon và cửa sổ giới thiệu mở được, không báo lỗi.'],
            [10, 'practice', 'counting', 'Chọn và đếm phần tử', 'Chức năng lõi', 60, 'Bạn xây chức năng đầu tiên có ích: chọn Category, lọc phạm vi và trả về số lượng.', 'Số lượng tool trả về khớp Revit.', 'Yêu cầu AI làm chức năng chỉ đọc; chạy trên model mẫu và so với Schedule hoặc Filter.', 'Có ảnh nút chạy, kết quả và cách đối chiếu.'],
            [11, 'practice', 'parameter', 'Làm phiên bản đầu tiên của tool', 'Xây chức năng', 75, 'Theo nhánh đã chọn, AI làm phần lọc/đếm, gán thông số, chuẩn bị tag hoặc xuất dữ liệu.', 'Một chức năng chính chạy được trên vài phần tử thử nghiệm.', 'Backup model, chạy trước với 3–5 phần tử, kiểm tra trước/sau và ghi phần tử không xử lý được.', 'Có kết quả thật, backup và log rõ số thành công/thất bại.'],
            [12, 'practice', 'capstone', 'Hoàn thiện chức năng chính và báo lỗi rõ ràng', 'MVP', 75, 'Bạn đưa tool qua cả trường hợp bình thường và dữ liệu thiếu để AI bổ sung thông báo dễ hiểu.', 'MVP không dừng toàn bộ chỉ vì một phần tử có vấn đề.', 'Thử input rỗng, thiếu Parameter, trùng Tag hoặc dữ liệu sai; gửi log lỗi cho AI và chạy lại.', 'Có kết quả đúng cùng thông báo xử lý được/không xử lý được.'],
            [13, 'practice', 'wpf', 'Làm cửa sổ tool dễ sử dụng', 'Giao diện', 60, 'AI giúp bạn làm giao diện WPF có nút chính, loading, thành công, lỗi và hủy; màu sắc theo DSCons.', 'Người dùng biết phải bấm gì và đang xảy ra điều gì.', 'Giữ logic đã chạy; kiểm tra mở, đóng, loading, thành công, lỗi và trạng thái không có dữ liệu.', 'Giao diện hoạt động thật, không chỉ là hình minh họa.'],
            [14, 'practice', 'common', 'Sửa một lỗi bằng AI có kiểm soát', 'Xử lý lỗi', 60, 'Bạn học cách lấy đúng phần lỗi, yêu cầu AI giải thích nguyên nhân rồi mới sửa.', 'Sửa được một lỗi và chứng minh lỗi không quay lại.', 'Dùng lỗi an toàn ở bản sao, gửi đoạn log liên quan, xem file thay đổi và chạy lại bài test cũ.', 'Nêu được nguyên nhân, file sửa và kết quả kiểm tra sau sửa.'],
            [15, 'live', 'common', 'Review MVP và xin góp ý cộng đồng', 'Livestream', 120, 'Mentor kiểm tra Ribbon, chức năng, giao diện và phạm vi trước khi bạn đóng gói.', 'Có quyết định rõ: được đóng gói hoặc cần sửa lỗi nào trước.', 'Demo theo format mở Revit → bấm tool → nhập → chạy → kết quả → lỗi; đăng bản MVP lên Bảng tin.', 'MVP chạy thật, có góp ý và danh sách việc cần sửa.'],
            [16, 'practice', 'report', 'Thêm một nâng cấp có giá trị', 'Nâng cấp', 75, 'Bạn chọn một nâng cấp sát công việc: lọc theo vị trí, batch operation, geometry hoặc báo cáo.', 'Nâng cấp giải quyết đúng vấn đề đã chốt, không mở rộng lan man.', 'Viết trước một tiêu chí kiểm tra, nhờ AI cập nhật kế hoạch, thử dữ liệu bình thường và dữ liệu biên.', 'Có output dùng được và test case pass/fail.'],
            [17, 'practice', 'common', 'Bảo vệ model và người dùng', 'An toàn', 45, 'Bạn thêm backup, xác nhận trước thay đổi hàng loạt, log thao tác và cảnh báo giới hạn.', 'Tool chạy thử an toàn và nói rõ khi nào không nên dùng.', 'Yêu cầu AI rà soát thao tác ghi, test trên bản sao, kiểm tra log không chứa dữ liệu mật.', 'Có cảnh báo trước thao tác nguy hiểm và hướng dẫn khôi phục.'],
            [18, 'practice', 'common', 'Đóng gói bản tool có thể cài lại', 'Đóng gói', 60, 'AI gom các thành phần cần thiết thành bộ cài/ZIP; bạn thử cài lại như một người dùng mới.', 'Bộ cài mở được Ribbon trên môi trường sạch.', 'Build Release, kiểm tra dependency, cài từ thư mục dist và không đưa secret vào bộ cài.', 'Có bộ cài, danh sách thành phần và ảnh cài thành công.'],
            [19, 'practice', 'common', 'Viết hướng dẫn sử dụng ngắn', 'Tài liệu', 45, 'AI soạn bản nháp hướng dẫn; bạn kiểm tra lại từng bước bằng chính bộ cài.', 'Người khác có thể cài và chạy mà không cần hỏi lại.', 'Kiểm tra yêu cầu, cách cài, cách chạy, kết quả, lỗi thường gặp và giới hạn; sửa mọi chỗ AI đoán sai.', 'Có hướng dẫn đã thử thật và ảnh kết quả.'],
            [20, 'practice', 'common', 'Kiểm thử như một người dùng thật', 'UAT', 75, 'Bạn thử tool trên model mô phỏng công việc với dữ liệu đúng, thiếu và thao tác sai.', 'Tool đạt ít nhất ba tình huống và có giới hạn rõ ràng.', 'Chạy từ bộ cài; ghi pass/fail, gửi lỗi cho AI, cập nhật bản build rồi test lại.', 'Có bảng test và ảnh chụp của ba tình huống.'],
            [21, 'live', 'common', 'Showcase tool và tổng kết', 'Grand Finale', 120, 'Bạn trình bày vấn đề ban đầu, cách dùng AI, demo tool thật, kết quả trước/sau và giới hạn.', 'Có tool chạy được, bộ cài, hướng dẫn và video tổng kết.', 'Demo 3 phút vấn đề, 3 phút tool, 2 phút hỏi đáp; quay video nếu vắng livestream và đăng thành quả lên Bảng tin.', 'Demo chạy thật, bộ cài cài lại được, video và bài tổng kết đầy đủ.'],
        ];

        return array_map(fn (array $row) => $this->day($row), $rows);
    }

    /** @param array<int, mixed> $row */
    private function day(array $row): array
    {
        [$day, $modality, $track, $title, $label, $minutes, $description, $outcome, $focus, $check] = $row;
        $feed = in_array($day, [1, 7, 15, 21], true)
            ? match ($day) {
                1 => 'Sau khi hoàn tất phần cài đặt, đăng một bài giới thiệu ngắn: tên, AI Agent đã chọn và mục tiêu 21 ngày.',
                7 => 'Đăng bài toán tool bạn muốn giải quyết và một câu hỏi xin góp ý từ cộng đồng.',
                15 => 'Đăng ảnh MVP đang chạy, điều đã làm được và điều bạn muốn được góp ý.',
                default => 'Đăng thành quả cuối khóa, video demo, bộ cài và một giới hạn bạn đã phát hiện.',
            }
            : null;

        return compact('day', 'modality', 'track', 'title', 'label', 'minutes', 'description', 'outcome', 'focus', 'check', 'feed');
    }

    private function payload(array $day): array
    {
        $isLive = $day['modality'] === 'live';
        $isLong = in_array($day['day'], [7, 12, 15, 18, 20, 21], true);
        $evidence = [
            'Ảnh màn hình kết quả thật của ngày '.$day['day'].'.',
            $day['check'],
            'Ba câu ngắn: Tôi yêu cầu AI làm gì? AI đã làm gì? Tôi kiểm tra kết quả thế nào?',
        ];
        if ($day['day'] === 21) {
            $evidence[] = 'Video tổng kết 3–6 phút hoặc phần trình bày trực tiếp trong livestream.';
        }

        return [
            'modality' => $day['modality'],
            'estimated_minutes' => $day['minutes'],
            'learning_objectives' => [$day['focus'], 'Giải thích được kết quả bằng ngôn ngữ công việc của kỹ sư Cơ Điện.'],
            'prerequisites' => [$day['day'] === 1 ? 'Có máy Windows và quyền cài phần mềm được phép sử dụng.' : 'Hoàn thành bài trước và dùng model mẫu hoặc bản sao đã backup.'],
            'ai_actions' => ['Đọc mục tiêu và những gì đang có trước khi thực hiện.', 'Đề xuất kế hoạch, file cần thay đổi, lệnh build và cách kiểm tra.', 'Thực hiện phần kỹ thuật sau khi học viên xác nhận và báo lại kết quả.'],
            'student_actions' => ['Chọn đúng phiên bản Revit và cung cấp thông tin công việc cho AI.', 'Đọc kế hoạch, xác nhận trước thay đổi và không đưa dữ liệu mật lên AI.', 'Mở Revit bằng model mẫu/bản sao, chạy thử và đối chiếu kết quả.'],
            'student_does_not_need' => ['Không cần tự viết C#, WPF hoặc API bằng tay.', 'Không cần tạo file Markdown hay hiểu cấu trúc project để nộp bài.'],
            'sop_steps' => array_merge([
                'Mở công cụ hoặc model được ghi trong bài và dùng bản sao an toàn.',
                'Copy toàn bộ prompt bên dưới, dán vào AI Agent và điền các phần trong ngoặc vuông.',
                'Khi AI hỏi lại, trả lời phiên bản Revit, mục tiêu và model kiểm thử; nếu chưa biết thì nói rõ “chưa biết”, không đoán.',
                'Đọc kế hoạch AI: mục tiêu, việc sẽ làm, file sẽ sửa, lệnh chạy và cách quay lại. Chỉ xác nhận khi bạn hiểu.',
            ], $this->specificSteps($day['day']), [
                'Sau khi xác nhận, build/chạy theo hướng dẫn, rồi mở Revit để kiểm tra kết quả thật.',
                'Chụp bằng chứng, trả lời ba câu reflection và nộp bài trên website. Nếu lỗi, gửi đoạn log từ ERROR/exception đến phần summary.',
            ]),
            'verification_checklist' => ['Tôi đã dùng đúng phiên bản và model kiểm thử.', 'Kết quả có thể đối chiếu bằng Properties, Schedule, Filter hoặc thao tác thực tế.', 'Không sửa model gốc và không nộp ảnh code thay cho kết quả.'],
            'homework' => ['title' => 'Bài thực hành ngày '.$day['day'].': '.$day['title'], 'instructions' => $evidence, 'deadline_hours' => 24],
            'evidence_requirements' => $evidence,
            'rubric' => [['key' => 'evidence_completeness', 'label' => 'Đủ bằng chứng', 'points' => 30], ['key' => 'correctness', 'label' => 'Kết quả đúng và đối chiếu được', 'points' => 40], ['key' => 'personalization', 'label' => 'Có cá nhân hóa theo công việc', 'points' => 15], ['key' => 'verification', 'label' => 'Mô tả được cách kiểm chứng', 'points' => 15]],
            'milestone_rubric' => [['key' => 'real_functionality', 'label' => 'Chức năng chạy thật', 'points' => 30], ['key' => 'correctness', 'label' => 'Kết quả đúng trên model', 'points' => 25], ['key' => 'error_handling', 'label' => 'Có xử lý input lỗi', 'points' => 15], ['key' => 'reinstallable', 'label' => 'Cài và chạy lại được', 'points' => 15], ['key' => 'ai_understanding', 'label' => 'Hiểu quy trình AI', 'points' => 15]],
            'common_errors' => ['Chỉ gửi ảnh code hoặc câu trả lời của AI mà chưa chạy trong Revit.', 'Dùng sai phiên bản, sai model hoặc quên kiểm tra kết quả độc lập.', 'Cho AI sửa quá nhiều thứ cùng lúc hoặc sửa model gốc.'],
            'review_mode' => $isLive ? 'live' : ($day['day'] >= 7 ? 'mentor' : 'auto'),
            'pass_score' => 70,
            'track' => $day['track'],
            'content_mode' => $isLong ? 'landing' : 'inline',
            'landing_slug' => $isLong ? 'ngay-'.$day['day'] : null,
            'feed_activity' => $day['feed'],
            'why' => 'AI làm phần kỹ thuật nhanh hơn; học viên vẫn là người quyết định mục tiêu và kiểm tra kết quả thật.',
            'required_outcome' => $day['outcome'],
            'ai_prompt' => $this->prompt($day),
            'error_prompts' => ['Sửa lỗi build: Đọc đúng phần log liên quan, giải thích nguyên nhân, chỉ sửa file cần thiết, build lại và chạy test cũ.', 'Sửa lỗi Revit: Kiểm tra đúng phiên bản và API đang có trước khi sửa; không đoán method.', 'Giải thích: Nói bằng ngôn ngữ kỹ sư MEP tôi cần kiểm tra gì và kiểm tra ở đâu trong Revit.'],
            'safety_constraints' => ['Không xóa file hoặc sửa model gốc.', 'Backup trước thao tác thay đổi dữ liệu.', 'Đọc và lập kế hoạch trước khi sửa.', 'Không đưa model/dữ liệu mật, token hoặc thông tin công ty lên AI Agent.'],
        ];
    }

    private function prompt(array $day): string
    {
        return "BỐI CẢNH\n- Tôi là kỹ sư Cơ Điện đang học ngày {$day['day']}/21.\n- Phiên bản Revit: [tôi sẽ điền]\n- Workspace: [đường dẫn workspace]\n- Model kiểm thử: model mẫu hoặc bản sao đã backup\n\nMỤC TIÊU HÔM NAY\n{$day['focus']}\n\nHÃY LÀM THEO THỨ TỰ\n1. Đọc workspace và các hướng dẫn liên quan trước.\n2. Nói ngắn gọn bạn hiểu mục tiêu gì, sẽ làm những bước nào, sửa file nào, build bằng lệnh nào và tôi kiểm tra kết quả ra sao. Chờ tôi xác nhận.\n3. Sau khi tôi xác nhận, thực hiện phần kỹ thuật theo đúng phiên bản Revit; không tự mở rộng phạm vi.\n4. Build/chạy thử và báo lại file đã đổi, kết quả, log lỗi còn lại và cách rollback.\n\nAN TOÀN\n- Không xóa file, không sửa model gốc, luôn backup trước thay đổi.\n- Không đoán Revit API hoặc package khi chưa kiểm tra phiên bản thật.\n- Không đưa dữ liệu mật, token hoặc thông tin công ty lên AI.\n\nTôi không cần tự viết C#/WPF. Tôi sẽ mở Revit, kiểm tra kết quả và nộp ảnh bằng chứng thật.";
    }

    /** @return array<int, string> */
    private function specificSteps(int $day): array
    {
        return match ($day) {
            1 => ['Chọn đúng một AI Agent: Codex, Claude Code hoặc Google Antigravity; tải từ trang chính thức.', 'Mở thư mục Bộ Kit, yêu cầu AI chỉ đọc và nói lại mục tiêu khóa học; chưa cho AI sửa gì.', 'Nếu cài đặt lỗi, chụp tên lỗi và phiên bản Windows thay vì tự thử các lệnh không rõ tác dụng.'],
            2 => ['Nói cho AI một mục tiêu bằng câu đời thường: “Tôi muốn làm tool để…”.', 'Yêu cầu AI ghi rõ việc sẽ làm, việc không làm, cách build và cách quay lại nếu sai.', 'Chỉ nhắn “đồng ý thực hiện” sau khi bạn đọc được kế hoạch và thấy đúng mục tiêu.'],
            3 => ['Yêu cầu AI đọc hướng dẫn Kit trước khi chạy kiểm tra môi trường.', 'Ghi lại phiên bản Revit thật đang dùng; không chọn version theo phỏng đoán.', 'Nếu có FAIL, gửi riêng phần lỗi và summary cho AI; không gửi token, mật khẩu hoặc file công ty.'],
            4 => ['Mở Revit bằng model mẫu/bản sao và để AI xác nhận tên model đang mở.', 'Yêu cầu AI đếm Duct, Pipe và Equipment ở chế độ chỉ đọc.', 'Đối chiếu ít nhất một con số bằng Schedule hoặc Filter trong Revit.'],
            5 => ['Chọn 5–10 phần tử cùng một nhóm để dễ kiểm tra.', 'Yêu cầu AI trả bảng có Category, Level, System, Size và thông tin còn lại; thiếu thì ghi “chưa có”.', 'Mở Properties/Schedule để kiểm tra tối thiểu 5 giá trị, không chỉ tin câu trả lời AI.'],
            6 => ['Viết một câu hỏi thật của công việc, có đối tượng, điều kiện và nơi cần tìm.', 'Để AI lặp lại điều kiện bằng lời trước khi chạy lọc, đặc biệt là đơn vị kích thước.', 'So kết quả với Filter hoặc Schedule độc lập và ghi nhận nếu lệch.'],
            7 => ['Điền form “Bài toán tool của tôi” bằng việc đang làm tay, đầu vào, đầu ra và người dùng.', 'Chọn một nhánh: đếm/lọc, gán Parameter, Auto-Tag hoặc báo cáo.', 'Loại bỏ những việc lớn như license server, làm mọi thứ cho dự án hoặc xử lý mọi category.'],
            8 => ['Chuẩn bị phần trình bày 3 phút: vấn đề, đầu vào, thao tác, đầu ra và giới hạn.', 'Ghi đúng góp ý mentor vào form bài toán, không cần chỉnh file kỹ thuật.', 'Dán yêu cầu đã chốt vào AI nhưng chỉ cho AI lập kế hoạch, chưa tạo code.'],
            9 => ['Khai báo đúng phiên bản Revit cho AI trước khi tạo nền tảng tool.', 'Cho AI tạo Ribbon/nút giới thiệu, build theo đúng hướng dẫn Kit và báo lại cách cài.', 'Mở Revit, xác nhận tab/nút có tên tool của bạn và bấm cửa sổ giới thiệu.'],
            10 => ['Chọn một Category và phạm vi đếm đơn giản trước khi thêm nhiều điều kiện.', 'Yêu cầu AI làm thao tác chỉ đọc; nếu AI đề nghị thay đổi model, dừng và hỏi lại.', 'So số đếm với Schedule hoặc Filter; chụp cả tool và màn hình đối chiếu.'],
            11 => ['Tạo bản sao model trước khi thử bất kỳ thao tác thay đổi dữ liệu nào.', 'Chạy thử trên 3–5 phần tử, xem trước/sau bằng Properties hoặc file xuất.', 'Yêu cầu AI báo số thành công, số không xử lý được và lý do.'],
            12 => ['Chuẩn bị ít nhất ba tình huống: bình thường, không có dữ liệu và input sai.', 'Yêu cầu AI thêm guard/summary để người dùng biết cần sửa gì.', 'Chạy từng tình huống; không kết luận đạt chỉ vì happy path chạy được.'],
            13 => ['Giữ nguyên chức năng đã chạy; chỉ yêu cầu AI chỉnh giao diện và binding cần thiết.', 'Kiểm tra nút chính, loading, thành công, lỗi, không dữ liệu, đóng và hủy.', 'Đảm bảo nhãn nút nói rõ hành động thay vì chỉ dùng icon.'],
            14 => ['Chọn một lỗi an toàn ở bản sao hoặc một lỗi thật đã có, không cố tình làm hỏng model.', 'Gửi phần ERROR/exception cùng vài dòng trước sau cho AI.', 'Sau khi AI sửa, chạy lại đúng tình huống trước đó để chứng minh không hồi quy.'],
            15 => ['Chuẩn bị demo: mở Revit, bấm tool, nhập/chọn dữ liệu, xem kết quả và thử một lỗi.', 'Ghi feedback thành “phải sửa”, “nên sửa”, “để sau”.', 'Đăng MVP cùng một câu hỏi cụ thể để cộng đồng có thể góp ý đúng chỗ.'],
            16 => ['Chọn một nâng cấp duy nhất có liên quan trực tiếp đến bài toán đã chốt.', 'Viết trước kết quả mong đợi khi dữ liệu bình thường và khi dữ liệu biên.', 'Chỉ cho AI triển khai nâng cấp sau khi kế hoạch không làm lan phạm vi.'],
            17 => ['Yêu cầu AI chỉ ra thao tác nào có thể thay đổi dữ liệu và nơi cần xác nhận người dùng.', 'Thử tool trên bản sao với backup và kiểm tra log không chứa dữ liệu mật.', 'Thêm hướng dẫn ngắn về backup, dừng thao tác và khôi phục khi kết quả không đúng.'],
            18 => ['Yêu cầu AI liệt kê DLL, manifest, dependency, icon và hướng dẫn cài cần có.', 'Build Release sạch rồi tạo thư mục dist/ZIP; không lấy file từ bản Debug để nộp.', 'Cài vào nơi test sạch và mở Revit để xác nhận Ribbon xuất hiện.'],
            19 => ['Để AI soạn nháp hướng dẫn từ tool hiện tại, sau đó tự làm theo như người mới.', 'Sửa chỗ thiếu yêu cầu cài, bước bấm, kết quả mong đợi hoặc tình huống lỗi.', 'Đảm bảo tên tool, phiên bản và bộ cài trong hướng dẫn trùng nhau.'],
            20 => ['Chọn model mô phỏng hoặc đã ẩn dữ liệu nhạy cảm; chạy từ bộ cài đã đóng gói.', 'Ghi bảng test cho happy path, dữ liệu thiếu và thao tác sai.', 'Nếu sửa sau UAT, build lại dist và chạy lại case lỗi đã phát hiện.'],
            21 => ['Chuẩn bị 3 phút vấn đề, 3 phút demo và 2 phút trả lời; demo trên Revit thật.', 'Quay video nếu không dự livestream; video phải có tool chạy, không dùng slideshow.', 'Đăng bài tổng kết có kết quả trước/sau, link bộ cài/hướng dẫn và giới hạn của tool.'],
        };
    }

    private function sopMarkdown(array $day, array $payload): string
    {
        $steps = collect($payload['sop_steps'])->map(fn ($step, $i) => ($i + 1).'. '.$step)->implode("\n");
        $checks = collect($payload['verification_checklist'])->map(fn ($item) => '- [ ] '.$item)->implode("\n");
        $evidence = collect($payload['evidence_requirements'])->map(fn ($item) => '- '.$item)->implode("\n");

        return "## {$day['title']}\n\n### Hôm nay học gì?\n{$day['description']}\n\n### Kết quả bắt buộc\n{$day['outcome']}\n\n### AI sẽ làm gì?\n- {$day['focus']}\n- Báo lại file đã thay đổi, lệnh build và lỗi còn tồn tại.\n\n### SOP thực hiện\n{$steps}\n\n### Checklist học viên tự kiểm tra\n{$checks}\n\n### Bài tập và bằng chứng cần nộp\n{$evidence}\n\n### Prompt copy vào AI Agent\n```text\n{$payload['ai_prompt']}\n```\n\n### Lỗi thường gặp\n".collect($payload['common_errors'])->map(fn ($item) => '- '.$item)->implode("\n")."\n\n### Chấm đạt\nĐạt từ {$payload['pass_score']}/100, đủ bằng chứng, kết quả kiểm chứng được và không có Critical Fail. Nếu chưa đạt, sửa đúng lý do mentor ghi rồi nộp lại; không bị trừ XP vì nộp lại.";
    }
}
