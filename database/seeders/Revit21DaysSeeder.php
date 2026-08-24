<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\ChallengeTask;
use App\Models\Expedition;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Idempotent source of truth for the DSCons Revit curriculum.
 *
 * Run explicitly with: php artisan db:seed --class=Revit21DaysSeeder
 * It updates the existing slug and never deletes legacy tasks or evidence.
 */
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
            'description' => 'Lộ trình thực chiến dành cho kỹ sư BIM/MEP: dùng AI Agent để khảo sát Revit, xây add-in, kiểm thử, đóng gói và chứng minh kết quả trên model của chính mình.',
            'boss_name' => $expedition->boss_name ?: 'DSCons Revit Mentor',
            'difficulty' => $expedition->difficulty ?: 'normal',
            'required_days' => 21,
            'max_members' => $expedition->max_members ?: 999,
            'created_by' => $expedition->created_by ?: $leader?->id,
            'leader_id' => $expedition->leader_id ?: $leader?->id,
            'status' => $expedition->status ?: 'open',
        ]);
        $expedition->save();

        foreach ($this->days() as $day) {
            $payload = $this->payload($day);
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

    /** @return array<int, array<string, mixed>> */
    private function days(): array
    {
        $commonPrerequisites = [
            'Có Revit 2020–2026 và một model mẫu không chứa dữ liệu mật.',
            'Có workspace Bộ Kit DSCons và quyền chạy PowerShell/Build Tools.',
        ];

        return [
            $this->day(1, 'live', 'common', 'Khởi động workspace và chạy prompt đầu tiên', 'Khai hoả', 90,
                'Buổi kickoff giúp học viên cài/kiểm tra AI Agent, mở đúng Bộ Kit và biết đầu ra cuối khóa.',
                ['Xác định được add-in/công cụ muốn hoàn thành sau 21 ngày.', 'Mở được workspace bằng Codex, Antigravity hoặc agent tương đương.', 'AI đọc được cấu trúc thư mục và giải thích quy trình làm việc.'],
                $commonPrerequisites,
                ['Đăng nhập AI Agent bằng tài khoản được phép dùng cho dữ liệu không mật.', 'Mở thư mục workspace Bộ Kit DSCons, không mở thư mục dự án mật của công ty.', 'Yêu cầu AI chỉ đọc cây thư mục, không sửa file.', 'Chạy prompt kiểm tra đầu tiên và lưu câu trả lời vào AI_WORKFLOW.md.', 'Trong livestream, hỏi mentor khi workspace hoặc quyền chạy lệnh bị lỗi.'],
                ['Ảnh workspace có tên thư mục và tên AI Agent.', 'Video 30 giây AI nhận diện workspace và nói lại đầu ra khóa học.', 'Ba câu reflection: tôi yêu cầu gì, AI đã làm gì, tôi kiểm chứng thế nào.'],
                ['Không gửi model hoặc file công ty lên AI.', 'Không cho phép AI xóa, di chuyển hoặc ghi đè file ngoài workspace.', 'Không coi câu trả lời của AI là bằng chứng tool đã chạy trong Revit.'],
                'Tạo workspace đầu tiên an toàn và có thể lặp lại',
                ['Sử dụng Codex/Antigravity ở đâu?', 'File nào là nguồn hướng dẫn?', 'Bước nào chỉ đọc và bước nào có thể sửa?']),
            $this->day(2, 'video', 'common', 'Lập kế hoạch trước khi cho AI viết code', 'Cách làm việc với Agent', 45,
                'Học viên biết AI Agent có thể đọc/sửa/build trong workspace, nhưng phải lập kế hoạch trước và chờ xác nhận.',
                ['Phân biệt chatbot trả lời với agent thao tác trên file.', 'Tạo AI_WORKFLOW.md có mục tiêu, kế hoạch và danh sách file dự kiến sửa.', 'Biết yêu cầu AI giải thích trước khi code.'],
                ['Hoàn thành ngày 1 và có workspace sạch.'],
                ['Mở workspace và đọc AGENTS.md/SETUP.md nếu có.', 'Dán prompt lập kế hoạch, yêu cầu AI chưa được sửa file.', 'Đọc kế hoạch: mục tiêu, giả định, file ảnh hưởng, lệnh build và cách rollback.', 'Nếu AI hỏi lại, trả lời phiên bản Revit, đường dẫn workspace và capstone dự kiến; không đoán API.', 'Chỉ sau khi đồng ý kế hoạch mới cho AI tạo file.'],
                ['File AI_WORKFLOW.md có mục tiêu, kế hoạch, file dự kiến sửa và lệnh kiểm thử.', 'Ảnh đoạn chat thể hiện AI lập kế hoạch trước khi code.'],
                ['Bấm “làm luôn” khi chưa đọc file.', 'Cho AI sửa cả model hoặc thư mục không liên quan.', 'Không lưu lại giả định về version Revit.'],
                'Lập kế hoạch có phê duyệt trước khi code',
                ['AI sẽ sửa file nào?', 'Có file nào không được sửa?', 'Lệnh nào chứng minh build thành công?']),
            $this->day(3, 'video', 'common', 'Chạy preflight môi trường Revit', 'Preflight', 45,
                'Kiểm tra môi trường trước khi debug để phân biệt lỗi máy, lỗi version và lỗi code.',
                ['Đọc được báo cáo Windows/Revit/.NET/Build Tools/Git.', 'Biết ghi nhận lỗi chưa xử lý thay vì giấu lỗi.', 'Có environment-report.json hoặc ảnh PASS.'],
                ['Có workspace và quyền chạy script cục bộ.'],
                ['Mở PowerShell tại root workspace.', 'Dán prompt yêu cầu AI đọc script preflight trước khi chạy.', 'Cho AI chạy lệnh read-only; lưu output vào environment-report.json.', 'Kiểm tra Revit version và .NET target có khớp Bộ Kit.', 'Nếu FAIL, gửi cho AI đúng phần log từ dòng ERROR đến dòng summary; không gửi token hay đường dẫn nhạy cảm.'],
                ['Báo cáo có trạng thái PASS/FAIL cho từng thành phần.', 'Ít nhất một ảnh cửa sổ PowerShell và version Revit.', 'Danh sách lỗi đã sửa hoặc lý do chưa sửa.'],
                ['Chỉ chụp màn hình, không lưu report.', 'Dùng sai terminal hoặc chạy script từ thư mục con.', 'Bỏ qua FAIL vì nghĩ ngày sau AI sẽ tự sửa.'],
                'Môi trường build có thể kiểm tra bằng một lệnh rõ ràng',
                ['Revit version đang dùng?', 'Target framework là gì?', 'Nếu build FAIL, log nào cần gửi AI?']),
            $this->day(4, 'video', 'common', 'Khảo sát model Revit ở chế độ chỉ đọc', 'Đọc dữ liệu Revit', 45,
                'Kết nối Revit MCP/cơ chế đọc dữ liệu của Bộ Kit để AI nhìn thấy model thật nhưng chưa được sửa model.',
                ['AI truy vấn được document đang mở.', 'Đếm được Duct, Pipe và Equipment để đối chiếu.', 'Biết bật read-only và dừng nếu agent yêu cầu Transaction.'],
                ['Revit mở một model mẫu; đã backup model trước khi thử nghiệm.'],
                ['Mở Revit và model mẫu.', 'Khởi động MCP/connector theo SETUP.md của Bộ Kit.', 'Dán prompt khảo sát, nhấn mạnh không tạo Transaction.', 'Đối chiếu số lượng AI trả về với Schedule hoặc Filter trong Revit.', 'Lưu câu hỏi, câu trả lời và sai lệch nếu có vào day-04-notes.md.'],
                ['Ảnh AI đọc được tên document/model không mật.', 'Kết quả số lượng Duct/Pipe/Equipment.', 'Ảnh Schedule hoặc Properties dùng để đối chiếu.'],
                ['AI trả số liệu nhưng không nói nguồn.', 'Mở sai model.', 'Để connector có quyền ghi model khi bài chỉ yêu cầu đọc.'],
                'Truy vấn được model thật mà không làm thay đổi model',
                ['Document nào đang mở?', 'Kết quả nào đối chiếu được trong Revit?', 'Có Transaction hay không?']),
            $this->day(5, 'video', 'common', 'Đọc Parameter của phần tử MEP', 'Parameter', 45,
                'Biết yêu cầu AI xuất dữ liệu có ngữ cảnh: Category, Family, Type, Level, System, Size, Mark và Comments.',
                ['Chọn được 5–10 phần tử đại diện.', 'Xuất bảng Parameter có tên và giá trị.', 'Đối chiếu được ít nhất 5 giá trị với Properties/Schedule.'],
                ['Hoàn thành ngày 4 và có phần tử Duct/Pipe/Equipment trong model.'],
                ['Chọn một Category và 5–10 phần tử.', 'Dán prompt yêu cầu AI trả bảng có ElementId, Category, Family, Type, Level và các Parameter MEP.', 'Nếu Parameter không tồn tại, yêu cầu AI ghi “missing” thay vì đoán.', 'Đối chiếu từng dòng trong Revit Properties.', 'Lưu bảng CSV/Markdown và ghi lại một Parameter bị thiếu nếu có.'],
                ['Bảng 5–10 phần tử có ít nhất 5 Parameter đúng tên/giá trị.', 'Ảnh đối chiếu Properties hoặc Schedule.', 'Ba câu reflection bắt buộc.'],
                ['Nhầm Type với Family.', 'Dùng giá trị hiển thị đã format thay cho giá trị thật.', 'Bỏ qua Parameter không tồn tại.'],
                'Đọc Parameter có thể truy nguyên về ElementId',
                ['ElementId nào?', 'Parameter thuộc instance hay type?', 'Giá trị có đối chiếu được ở đâu?']),
            $this->day(6, 'video', 'common', 'Viết truy vấn lọc nâng cao và giải thích điều kiện', 'Query', 50,
                'Lọc theo Category, Level, System, kích thước và điều kiện kết hợp; học viên phải hiểu logic lọc do AI tạo.',
                ['Tạo một truy vấn có ít nhất ba điều kiện.', 'Giải thích được từng điều kiện bằng ngôn ngữ kỹ sư.', 'Đối chiếu kết quả không lấy nhầm Category/Level.'],
                ['Hoàn thành ngày 5.'],
                ['Chọn một câu hỏi thực tế, ví dụ Duct > 300 mm ở Level 2 thuộc Supply Air.', 'Yêu cầu AI viết điều kiện và giải thích bằng bảng.', 'Chạy truy vấn read-only trước.', 'Đếm kết quả độc lập bằng Schedule/Filter trong Revit.', 'Lưu câu truy vấn, output và sai lệch vào day-06-query.md.'],
                ['Một truy vấn nâng cao có input, điều kiện, output.', 'Ảnh kết quả lọc và cách đối chiếu.', 'Đoạn giải thích 3–5 câu của học viên.'],
                ['Dùng “chứa” thay cho so sánh kích thước.', 'Không xác định đơn vị mm/feet.', 'Không kiểm tra Level hoặc System thực tế.'],
                'Một truy vấn có thể giải thích và kiểm chứng, không phải câu thần chú',
                ['Điều kiện nào là AND/OR?', 'Đơn vị của kích thước?', 'Đối chiếu với công cụ Revit nào?']),
            $this->day(7, 'assignment', 'common', 'Chọn capstone và viết TOOL_BRIEF.md', 'Chọn bài toán', 60,
                'Chuyển một vấn đề lặp lại của kỹ sư BIM/MEP thành bài toán đủ nhỏ để xây MVP trong hai tuần.',
                ['Chọn một trong 4 nhánh: đếm/lọc, gán Parameter, Auto-Tag, báo cáo.', 'Mô tả input, thao tác, output và người dùng.', 'Có tiêu chí thành công đo được.'],
                ['Hoàn thành các truy vấn ngày 4–6.'],
                ['Chọn đúng một workflow lặp lại trong công việc.', 'Mở mẫu TOOL_BRIEF.md.', 'Dán prompt yêu cầu AI phỏng vấn ngược và phát hiện phạm vi quá lớn.', 'Điền input, output, Category, điều kiện an toàn, tiêu chí thành công và ngoài phạm vi.', 'Không chọn “làm mọi thứ cho dự án”; capstone phải demo được trên model mẫu.'],
                ['TOOL_BRIEF.md có vấn đề, input, thao tác, output, người dùng và success metric.', 'Một sơ đồ 3 bước hoặc ảnh workflow hiện tại.', 'Nêu rõ dữ liệu mật nào không đưa vào AI.'],
                ['Phạm vi không đo được.', 'Chọn nhiều nhánh cùng lúc.', 'Đưa yêu cầu license server hoặc triển khai doanh nghiệp vào MVP.'],
                'Một Tool Brief có đầu vào và đầu ra quan sát được',
                ['Bài toán mất bao lâu khi làm tay?', 'Output đo bằng gì?', 'Nếu chỉ có 2 tuần, bỏ tính năng nào?']),
            $this->day(8, 'live', 'common', 'Review và chốt capstone với mentor', 'Livestream chốt đề tài', 120,
                'Mentor loại bỏ đề tài quá lớn, chốt nhánh kỹ thuật và viết prompt riêng cho từng học viên.',
                ['Trình bày Tool Brief trong 3 phút.', 'Nhận phạm vi đã chỉnh và tiêu chí “tool chạy được”.', 'Có prompt riêng sau review.'],
                ['Đã nộp TOOL_BRIEF.md ngày 7.'],
                ['Chuẩn bị demo workflow hiện tại và một model mẫu không mật.', 'Trình bày theo format: vấn đề → input → thao tác → output → giới hạn.', 'Ghi lại các câu hỏi mentor và cập nhật Tool Brief ngay sau livestream.', 'Dán prompt capstone mới vào AI nhưng chỉ yêu cầu lập kế hoạch.', 'Chờ mentor xác nhận trước khi tạo scaffold.'],
                ['Video/trình bày 3 phút hoặc bản ghi theo format.', 'TOOL_BRIEF.md sau chỉnh sửa.', 'Ảnh/tệp prompt riêng và nhận xét mentor.'],
                ['Không có output đo được.', 'Đổi đề tài sau livestream mà không cập nhật brief.', 'Bắt đầu code trước khi mentor xác nhận.'],
                'Đề tài được mentor xác nhận và sẵn sàng xây MVP',
                ['Tính năng cốt lõi là gì?', 'Demo thành công trông như thế nào?', 'Test trên bản sao nào?']),
            $this->day(9, 'video', 'common', 'Tạo add-in nền tảng, Ribbon và About Me', 'Add-in scaffold', 60,
                'AI tạo scaffold C#/.addin và Ribbon; học viên không viết tay nhưng phải biết build, cài và xác nhận tên cá nhân.',
                ['Add-in load được trong Revit.', 'Ribbon có tên cá nhân/tool.', 'Nút About Me mở được cửa sổ không lỗi.'],
                ['Tool Brief đã được mentor xác nhận; preflight ngày 3 PASS.'],
                ['Mở solution hiện có hoặc tạo solution trong workspace.', 'Dán prompt scaffold với Revit version chính xác.', 'AI phải liệt kê file trước khi sửa và chờ xác nhận.', 'Chạy build Debug/Release theo README.', 'Copy .addin/DLL vào thư mục add-in đúng version và mở Revit.', 'Bấm Ribbon/About Me và chụp kết quả.'],
                ['Ảnh Ribbon cá nhân và About Me.', 'Log build thành công.', 'Mô tả cách cài lại từ đầu.'],
                ['Sai target Revit.', 'DLL thiếu dependency.', 'Tên cá nhân chỉ nằm trong code, không hiện trong giao diện.'],
                'Add-in load ổn định và có điểm nhận diện cá nhân',
                ['File manifest ở đâu?', 'DLL nào được load?', 'Nếu Revit không thấy tab, xem log nào?']),
            $this->day(10, 'video', 'counting', 'Lọc và đếm phần tử bằng FilteredElementCollector', 'Command lõi', 60,
                'Xây command lõi dùng chung: lọc Category/Level/View và hiển thị số lượng có thể đối chiếu với Schedule.',
                ['Nút chạy được trên model mẫu.', 'Kết quả đếm đúng và ghi rõ phạm vi.', 'Có xử lý model không có phần tử.'],
                ['Ngày 9 load được add-in.'],
                ['Mở bản sao model.', 'Dán prompt yêu cầu AI dùng FilteredElementCollector, không hard-code ElementId.', 'AI phải giải thích collector, filter và transaction; command đếm không được mở Transaction.', 'Build Release, cài lại DLL và chạy nút.', 'Đối chiếu với Schedule/Filter thủ công và ghi chênh lệch.'],
                ['Video 15–30 giây bấm nút và hiển thị kết quả.', 'Ảnh Schedule hoặc phép đếm đối chiếu.', 'Ba câu reflection về collector.'],
                ['Đếm cả type thay vì instance.', 'Lọc sai view/level.', 'Tạo Transaction cho thao tác chỉ đọc.'],
                'Kết quả đếm đúng trên model mẫu và lặp lại được',
                ['Collector lấy loại nào?', 'Vì sao không cần Transaction?', 'Kết quả đối chiếu ở đâu?']),
            $this->day(11, 'video', 'parameter', 'Thực hiện logic theo nhánh capstone và Transaction an toàn', 'Xây chức năng', 75,
                'Bắt đầu logic có thay đổi hoặc xuất dữ liệu. AI viết phần code; học viên kiểm tra backup, Transaction và kết quả trước/sau.',
                ['Có một chức năng nhánh chạy được trên bản sao model.', 'Có log số phần tử xử lý/thất bại.', 'Không làm hỏng file gốc.'],
                ['Ngày 8 đã chốt nhánh.'],
                ['Sao lưu model và mở bản sao.', 'Dán prompt capstone với input/output đã chốt.', 'Yêu cầu AI bọc thay đổi trong Transaction và xử lý Parameter read-only/missing.', 'Chạy thử với 3–5 phần tử trước.', 'Kiểm tra trước/sau bằng Properties hoặc file CSV/XLSX.', 'Ghi lại element không xử lý được và lý do.'],
                ['Ảnh trước/sau hoặc file export.', 'Log AI giải thích Transaction.', 'Danh sách trường hợp lỗi đã xử lý.'],
                ['Sửa trực tiếp model gốc.', 'Không rollback khi lỗi giữa chừng.', 'Ép kiểu Parameter sai hoặc bỏ qua read-only.'],
                'Một thao tác nhánh có backup, transaction và log kết quả',
                ['Có backup ở đâu?', 'Transaction bắt đầu/kết thúc thế nào?', 'Phần tử lỗi được ghi ra sao?']),
            $this->day(12, 'video', 'tagging', 'Hoàn thiện command chính và xử lý dữ liệu không hoàn hảo', 'Capstone MVP', 75,
                'Hoàn thiện Auto-Tag, Set Parameter, báo cáo hoặc lọc/đếm; đặc biệt xử lý thiếu Parameter, trùng Tag và phần tử lỗi.',
                ['Chạy end-to-end trên model mẫu.', 'Có thông báo thành công/thất bại rõ ràng.', 'Không dừng toàn bộ batch chỉ vì một phần tử lỗi.'],
                ['Ngày 11 có kết quả thử nhỏ.'],
                ['Liệt kê 3 trường hợp xấu cần test: missing Parameter, duplicate Tag, empty selection hoặc invalid input.', 'Dán prompt yêu cầu AI thêm guard clause và summary.', 'Chạy happy path rồi chạy từng trường hợp xấu.', 'Kiểm tra output và log theo ElementId.', 'Cập nhật TOOL_BRIEF.md với giới hạn thực tế.'],
                ['Video end-to-end trên model mẫu.', 'Bảng trường hợp lỗi và kết quả.', 'Ảnh thông báo rõ khi không xử lý được.'],
                ['Tool báo thành công dù không có phần tử.', 'Duplicate Tag làm command crash.', 'Catch exception nhưng không ghi log nguyên nhân.'],
                'MVP xử lý được happy path và báo lỗi có thể hành động',
                ['Input rỗng thì sao?', 'Một phần tử lỗi có làm dừng cả batch không?', 'Người dùng biết sửa lỗi từ thông báo nào?']),
            $this->day(13, 'video', 'common', 'Thiết kế cửa sổ WPF dùng được trong Revit', 'WPF', 60,
                'Tạo UI WPF có bố cục rõ, màu DSCons, loading, thành công/thất bại; không che Revit và có trạng thái đóng/mở ổn định.',
                ['WPF mở/đóng không lỗi.', 'Nút chính, trạng thái đang chạy và lỗi hiển thị rõ.', 'Màu/nhãn được cá nhân hóa theo DSCons.'],
                ['Command ngày 12 chạy được trước khi làm UI.'],
                ['Mở XAML và code-behind do AI tạo trong workspace.', 'Dán prompt yêu cầu AI giữ logic hiện có, chỉ thay UI và binding cần thiết.', 'Build và mở cửa sổ từ Ribbon.', 'Thử loading, success, error, empty state và đóng cửa sổ.', 'Chụp ảnh ở độ phân giải desktop thông thường.'],
                ['Ảnh giao diện WPF.', 'Checklist 5 trạng thái UI.', 'Video mở/đóng cửa sổ không lỗi.'],
                ['UI giả nút nhưng không gọi command.', 'Không có loading khiến người dùng bấm lặp.', 'Dialog che Revit hoặc không đóng bằng Esc/Close.'],
                'Giao diện giúp kỹ sư biết đang chạy gì và kết quả là gì',
                ['Nút này gọi method nào?', 'Loading kết thúc khi nào?', 'Lỗi có hướng dẫn bước tiếp theo không?']),
            $this->day(14, 'video', 'common', 'Debug build, Revit API và Hot Reload có kiểm soát', 'Debug', 60,
                'Học quy trình đọc lỗi, phân loại nguyên nhân và đưa đúng log cho AI; không copy lỗi mù quáng.',
                ['Sửa được một lỗi có chủ ý.', 'Nêu được nguyên nhân, file sửa và cách kiểm tra lại.', 'Biết phân biệt namespace, version, API và Transaction error.'],
                ['Có MVP WPF/command ngày 13.'],
                ['Tạo một lỗi an toàn ở bản sao branch hoặc dùng lỗi có sẵn.', 'Copy từ dòng exception/build error đến stack trace liên quan.', 'Dán prompt debug yêu cầu AI giải thích trước khi sửa.', 'AI liệt kê file thay đổi và build lại.', 'Chạy lại đúng test case cũ để chứng minh lỗi đã hết.', 'Ghi before/after vào bug-log.md.'],
                ['Video sửa một lỗi có chủ ý.', 'Đoạn log trước/sau.', 'Ba câu: nguyên nhân, sửa ở file nào, kiểm tra lại ra sao.'],
                ['Gửi toàn bộ log có secret.', 'Sửa nhiều file không liên quan.', 'Kết luận “đã fix” mà chưa mở Revit test lại.'],
                'Một lỗi có nguyên nhân và bằng chứng regression test',
                ['Lỗi thuộc môi trường hay code?', 'Log tối thiểu cần gửi?', 'Test nào chứng minh không hồi quy?']),
            $this->day(15, 'live', 'common', 'Review MVP trực tiếp với mentor', 'Livestream review MVP', 120,
                'Mentor kiểm tra Ribbon, command chính, WPF và phạm vi capstone trước khi cho phép đóng gói.',
                ['Demo MVP trong 3 phút theo format cố định.', 'Nhận danh sách lỗi bắt buộc sửa.', 'Được xác nhận đủ điều kiện bước sang packaging hoặc biết cần resubmit.'],
                ['Ngày 14 có bug-log và MVP chạy được.'],
                ['Chuẩn bị model bản sao, build Release và checklist demo.', 'Demo: mở Revit → Ribbon → input → tool chạy → output → trường hợp lỗi.', 'Ghi lại feedback mentor theo mức critical/important/nice-to-have.', 'Sau live, cập nhật TOOL_BRIEF.md và bug-log.md.', 'Chỉ sửa critical/important trước ngày 16.'],
                ['Video demo 3 phút.', 'Checklist mentor hoặc biên bản review.', 'MVP build chạy được trên Revit.'],
                ['Demo bằng ảnh/video cũ.', 'Không có input lỗi.', 'Đề nghị đóng gói khi command chính chưa chạy.'],
                'MVP chạy thật và có quyết định go/no-go rõ ràng',
                ['Tính năng chính đã chạy chưa?', 'Critical bug nào còn?', 'Có thể cài lại trên máy sạch không?']),
            $this->day(16, 'video', 'report', 'Thêm logic nâng cao có giá trị thực tế', 'Nâng cao', 75,
                'Chọn đúng một nâng cấp: Geometry/BoundingBox, lọc theo vị trí, batch operation hoặc báo cáo xuất file.',
                ['Tính năng nâng cao giải quyết vấn đề trong Tool Brief.', 'Có test model gần thực tế.', 'Output có thể sử dụng lại, không chỉ là giao diện.'],
                ['MVP đã qua review ngày 15.'],
                ['Chọn một nâng cấp và viết acceptance test trước.', 'Dán prompt yêu cầu AI cập nhật plan, không làm lan phạm vi.', 'Build trên branch/copy và chạy test dữ liệu bình thường + biên.', 'Kiểm tra đơn vị, tolerance geometry và encoding file nếu export.', 'Cập nhật manual sơ bộ với tính năng mới.'],
                ['Video trên model gần thực tế.', 'File báo cáo hoặc output cuối.', 'Acceptance test pass/fail.'],
                ['Nâng cấp không liên quan brief.', 'Geometry thiếu tolerance.', 'Xuất file có đường dẫn máy cá nhân hoặc dữ liệu mật.'],
                'Tính năng nâng cao có test case và giá trị sử dụng',
                ['Nâng cấp giải quyết câu nào trong brief?', 'Input biên là gì?', 'Output có thể mở ở đâu?']),
            $this->day(17, 'video', 'common', 'Đặt giới hạn an toàn và trial/demo', 'An toàn', 45,
                'Bảo vệ model và người dùng: backup, xác nhận trước khi sửa, log thao tác và trial demo đơn giản; không xây license server.',
                ['Tool có cảnh báo trước thay đổi hàng loạt.', 'Có hướng dẫn backup và log.', 'Trial/expiry demo không phá workflow và không thu thập dữ liệu nhạy cảm.'],
                ['MVP đã có logic chính.'],
                ['Dán prompt yêu cầu AI audit Transaction, destructive action và dữ liệu log.', 'Thêm confirmation trước batch operation.', 'Test trên bản sao model với backup.', 'Nếu có trial, dùng ngày hết hạn cấu hình cục bộ; không hard-code secret.', 'Kiểm tra log không chứa email/token/model mật.'],
                ['Checklist an toàn.', 'Ảnh cảnh báo/trial nếu có.', 'Video test trên bản sao model.'],
                ['Không backup.', 'Ghi nguyên model vào log.', 'Đưa license server thành điều kiện đạt MVP.'],
                'Tool an toàn khi chạy thử và có giới hạn minh bạch',
                ['Có thao tác nào không thể undo?', 'Backup ở đâu?', 'Log có lộ dữ liệu mật không?']),
            $this->day(18, 'video', 'common', 'Build Release và đóng gói bộ cài', 'Packaging', 60,
                'Gom DLL, .addin, dependency, icon và cấu hình thành ZIP/installer có thể cài lại.',
                ['Build Release sạch.', 'Bộ cài có README và cấu trúc rõ.', 'Cài lại được từ dist trên workspace/máy sạch.'],
                ['Ngày 15 go; ngày 17 checklist an toàn đạt.'],
                ['Yêu cầu AI lập danh sách artifact và dependency.', 'Xóa bin/obj cũ rồi build Release.', 'Tạo dist với DLL, .addin, dependency, icon, README và uninstall/rollback.', 'Cài từ ZIP vào môi trường sạch hoặc thư mục test.', 'Mở Revit và xác nhận Ribbon xuất hiện.', 'Không commit secret/config cá nhân vào ZIP.'],
                ['File ZIP/EXE trong dist.', 'Ảnh/video cài lại.', 'Danh sách file trong bộ cài và version Revit hỗ trợ.'],
                ['Thiếu dependency.', 'Manifest trỏ sai đường dẫn.', 'Bộ cài chỉ chạy trên máy người tạo.'],
                'Người khác có thể cài và mở add-in từ bộ cài đã nộp',
                ['dist chứa gì?', 'Cài lại từ đâu?', 'Rollback khi cài lỗi thế nào?']),
            $this->day(19, 'video', 'common', 'Viết User Manual một trang và video hướng dẫn', 'Tài liệu', 45,
                'AI soạn bản nháp, học viên kiểm tra từng bước để người khác dùng tool mà không cần hỏi lại.',
                ['Manual có yêu cầu, cài đặt, thao tác, output, lỗi và giới hạn.', 'Video 1–2 phút theo đúng manual.', 'Tên tool/version khớp bộ cài.'],
                ['Có bộ cài ngày 18.'],
                ['Dán prompt yêu cầu AI đọc README, Tool Brief và UI hiện tại.', 'AI tạo HUONG_DAN_SU_DUNG.md/PDF bản nháp.', 'Học viên cài từ dist và làm theo từng bước, đánh dấu bước sai.', 'Sửa manual bằng kết quả thực tế, không giữ hướng dẫn AI đoán.', 'Quay video ngắn từ cài/ mở đến output.'],
                ['PDF/Markdown manual.', 'Video 1–2 phút.', 'Một người khác có thể đọc và chạy theo.'],
                ['Manual có lệnh/file không tồn tại.', 'Video và bộ cài khác version.', 'Không nêu giới hạn hoặc tình huống lỗi.'],
                'Tài liệu được kiểm chứng bởi một lần cài và chạy thật',
                ['Bước nào người mới dễ sai?', 'Output nằm ở đâu?', 'Không dùng được khi nào?']),
            $this->day(20, 'video', 'common', 'UAT trên model gần thực tế', 'UAT', 75,
                'Kiểm thử cả đường đi bình thường, dữ liệu thiếu và input người dùng sai trước khi showcase.',
                ['Tool pass ít nhất 3 tình huống.', 'Có bảng test case pass/fail.', 'Không dùng dữ liệu mật của công ty.'],
                ['Có bộ cài và manual.'],
                ['Chọn model mô phỏng công việc hoặc bản sao đã anonymize.', 'Tạo bảng test: happy path, empty input, missing Parameter/invalid selection.', 'Chạy từ bộ cài đã nộp, không chạy từ bin/Debug.', 'Ghi screenshot/video có tên tool và kết quả.', 'Gửi FAIL cho AI theo đúng log; cập nhật version nếu sửa.', 'Chốt danh sách giới hạn còn lại.'],
                ['Video demo A–Z 2–3 phút.', 'Bảng test case có pass/fail.', 'Ảnh kết quả của 3 tình huống.'],
                ['Chỉ test happy path.', 'Dùng model mật.', 'Sửa sau test nhưng không đổi version/dist.'],
                'Có bằng chứng tool chịu được input đúng, thiếu dữ liệu và lỗi người dùng',
                ['Test chạy từ bộ cài nào?', 'Case lỗi hiển thị gì?', 'Giới hạn còn lại được nói ở đâu?']),
            $this->day(21, 'live', 'common', 'Showcase tool và tổng kết hành trình', 'Grand Finale', 120,
                'Trình bày bài toán, cách dùng AI Agent, demo thật, kết quả trước/sau và giới hạn của tool.',
                ['Demo chạy thật trên Revit.', 'Có bộ cài và manual.', 'Nói rõ AI làm gì, học viên kiểm chứng gì và tool chưa làm được gì.'],
                ['Ngày 20 UAT đạt.'],
                ['Chuẩn bị 3 phút bài toán, 3 phút demo, 2 phút Q&A và 1 phút nhận feedback.', 'Mở Revit từ trạng thái sạch, cài hoặc bật add-in từ dist.', 'Demo input, output và một trường hợp lỗi.', 'Nộp bài tổng kết lên DSCons, kèm link bộ cài/video/manual.', 'Nếu vắng live, quay video theo đúng format và nộp thay thế.'],
                ['Video showcase hoặc trình bày trực tiếp.', 'Link/file dist, manual và Tool Brief cuối.', 'Bài tổng kết có before/after và giới hạn.'],
                ['Demo bằng ảnh không chạy.', 'Bộ cài không cài lại.', 'Không nêu giới hạn hoặc dùng dữ liệu dự án mật.'],
                'Một tool Revit có thể demo, cài lại, hướng dẫn và đánh giá giới hạn',
                ['Vấn đề ban đầu là gì?', 'Kết quả đo được?', 'Bước tiếp theo sau khóa học?']),
        ];
    }

    /** @return array<string, mixed> */
    private function day(int $day, string $modality, string $track, string $title, string $label, int $minutes, string $description, array $objectives, array $prerequisites, array $sop, array $evidence, array $errors, string $outcome, array $questions): array
    {
        return compact('day', 'modality', 'track', 'title', 'label', 'minutes', 'description', 'objectives', 'prerequisites', 'sop', 'evidence', 'errors', 'outcome', 'questions');
    }

    /** @return array<string, mixed> */
    private function payload(array $day): array
    {
        $reviewMode = $day['modality'] === 'live' ? 'live' : ($day['day'] >= 7 ? 'mentor' : 'auto');
        return [
            'modality' => $day['modality'],
            'estimated_minutes' => $day['minutes'],
            'learning_objectives' => $day['objectives'],
            'prerequisites' => $day['prerequisites'],
            'ai_actions' => [
                'Đọc file hướng dẫn và workspace trước khi hành động.',
                'Lập kế hoạch, liệt kê file sẽ thay đổi và chờ học viên xác nhận.',
                'Thực hiện/build theo version Revit được khai báo.',
                'Báo cáo file đã đổi, lệnh đã chạy, lỗi còn lại và cách kiểm thử.',
            ],
            'student_actions' => [
                'Mở đúng workspace/model bản sao và kiểm tra version.',
                'Đọc kế hoạch, trả lời câu hỏi của AI và xác nhận phạm vi.',
                'Chạy kết quả thật trong Revit, cá nhân hóa tên tool và lưu bằng chứng.',
                'Viết 3 câu: yêu cầu AI làm gì, AI sửa file nào, kiểm chứng ra sao.',
            ],
            'student_does_not_need' => [
                'Không cần tự viết C#/WPF bằng tay.',
                'Không cần upload model hoặc dữ liệu dự án mật lên AI.',
            ],
            'sop_steps' => $day['sop'],
            'verification_checklist' => array_merge($day['evidence'], [
                'Bằng chứng là kết quả chạy thật, không chỉ là ảnh code.',
                'Có tên/version cá nhân hóa khi bài yêu cầu.',
                'Đã kiểm tra trên model bản sao và ghi rõ giới hạn.',
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
            'why' => 'Kỹ thuật này giúp kỹ sư BIM/MEP giảm thao tác lặp lại nhưng vẫn kiểm soát được model, output và rủi ro.',
            'required_outcome' => $day['outcome'],
            'minimum_knowledge' => 'Biết mở Revit và đọc một phần tử/Properties; phần code do AI Agent thực hiện theo kế hoạch đã duyệt.',
            'ai_prompt' => $this->prompt($day),
            'error_prompts' => [
                'Prompt sửa build: “Đọc đúng log lỗi, giải thích nguyên nhân, chỉ sửa file liên quan, build lại và nêu test regression.”',
                'Prompt sửa Revit API: “Kiểm tra version/API thật trong solution trước khi sửa; không đoán method; nêu fallback nếu API không hỗ trợ.”',
                'Prompt giải thích: “Giải thích đoạn code vừa sửa bằng ngôn ngữ kỹ sư MEP và chỉ ra cách tôi kiểm chứng trong Revit.”',
            ],
            'safety_constraints' => [
                'Không xóa file hoặc sửa model gốc.',
                'Tạo/copy backup trước Transaction hoặc batch operation.',
                'Đọc file hiện có và lập kế hoạch trước khi sửa.',
                'Không đưa model/dữ liệu mật của công ty lên AI Agent.',
            ],
            'mentor_questions' => $day['questions'],
        ];
    }

    private function prompt(array $day): string
    {
        return "BỐI CẢNH\n- Revit version: tôi sẽ khai báo trước khi bạn code\n- Tên học viên: tôi sẽ cung cấp khi cần cá nhân hóa\n- Tên tool: theo TOOL_BRIEF.md\n- Nhánh capstone: {$day['track']}\n- Workspace: thư mục hiện tại\n\nMỤC TIÊU\n{$day['outcome']}\n\nRÀNG BUỘC AN TOÀN\n1. Không xóa file hoặc sửa model gốc.\n2. Phải tạo/copy backup trước Transaction.\n3. Phải đọc file hiện có trước khi sửa.\n4. Lập kế hoạch và liệt kê file sẽ thay đổi; chờ tôi xác nhận.\n5. Không giả định Revit API nếu chưa kiểm tra version.\n6. Không gửi dữ liệu dự án mật lên AI.\n\nYÊU CẦU AI\n1. Phân tích workspace và tài liệu liên quan.\n2. Đề xuất kế hoạch, file ảnh hưởng, lệnh build và cách rollback.\n3. Chờ tôi xác nhận kế hoạch.\n4. Tạo/sửa code hoặc tài liệu theo mục tiêu ngày {$day['day']}.\n5. Build Release hoặc chạy kiểm tra phù hợp.\n6. Giải thích file đã thay đổi và lỗi còn tồn tại.\n7. Viết checklist kiểm thử trong Revit.\n\nĐẦU RA BẮT BUỘC\n- File đã thay đổi.\n- Lệnh build/chạy.\n- Cách cài và cách rollback.\n- Cách kiểm tra kết quả trong Revit.\n- Các giới hạn hiện tại.\n\nLưu ý: học viên không cần tự viết C#/WPF; học viên phải chạy, cá nhân hóa, kiểm chứng và nộp bằng chứng thật.";
    }

    private function sopMarkdown(array $day, array $payload): string
    {
        $steps = collect($payload['sop_steps'])->values()->map(fn ($step, $i) => ($i + 1).'. '.$step)->implode("\n");
        $checks = collect($payload['verification_checklist'])->map(fn ($item) => '- [ ] '.$item)->implode("\n");
        $evidence = collect($payload['evidence_requirements'])->map(fn ($item) => '- '.$item)->implode("\n");

        return "## {$day['title']}\n\n"
            . "### Hôm nay học gì?\n{$day['description']}\n\n"
            . "### Kết quả bắt buộc\n{$day['outcome']}\n\n"
            . "### SOP thực hiện\n{$steps}\n\n"
            . "### Checklist học viên tự kiểm tra\n{$checks}\n\n"
            . "### Bài tập và bằng chứng cần nộp\n{$evidence}\n\n"
            . "### Prompt copy vào AI Agent\n```text\n{$payload['ai_prompt']}\n```\n\n"
            . "### Lỗi thường gặp\n".collect($day['errors'])->map(fn ($item) => '- '.$item)->implode("\n")."\n\n"
            . "### Chấm đạt\nĐạt từ {$payload['pass_score']}/100, đủ bằng chứng, kết quả kiểm chứng được và không có Critical Fail. Nếu chưa đạt, sửa đúng lý do mentor ghi và nộp lại; không bị trừ XP vì nộp lại.";
    }
}
