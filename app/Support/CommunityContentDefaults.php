<?php

namespace App\Support;

final class CommunityContentDefaults
{
    public static function resolve(?string $content, string $default): string
    {
        $content = trim((string) $content);

        // Nội dung cũ có thể chứa ký tự lỗi hoặc bị chuyển mã sai. Khi đó
        // dùng bản mặc định rõ ràng để thành viên không nhìn thấy chữ hỏng.
        if ($content === '' || str_contains($content, "\u{FFFD}") || self::looksMojibake($content)) {
            return $default;
        }

        return self::normalizeUtf8($content);
    }

    public static function normalizeUtf8(string $content): string
    {
        if (function_exists('mb_check_encoding') && mb_check_encoding($content, 'UTF-8')) {
            return $content;
        }

        $normalized = iconv('UTF-8', 'UTF-8//IGNORE', $content);

        return $normalized === false ? $content : $normalized;
    }

    private static function looksMojibake(string $content): bool
    {
        return preg_match('/(?:Ã.|Â.|Ä.|Å.|á»|áº|â€|ï¿½)/u', $content) === 1;
    }

    public static function guide(): string
    {
        return <<<'TEXT'
Bảng tin
Mục đích: Nơi cập nhật các bài viết và trao đổi mới trong cộng đồng. Bạn có thể đặt câu hỏi, chia sẻ kinh nghiệm hoặc đọc bài từ các kỹ sư khác.
Cách dùng: Chọn Chủ đề và Loại nội dung để lọc bài. Mở một bài viết để đọc đầy đủ, thích, bình luận, trả lời, lưu hoặc chia sẻ.
Mẹo: Muốn nhận được hỗ trợ nhanh, hãy nêu rõ vấn đề, phiên bản phần mềm, bối cảnh và kết quả bạn mong muốn.

CỐT
Mục đích: Khu vực lưu các bài viết có giá trị thực tế để thành viên dễ tìm lại và học hỏi.
Cách dùng: Đọc bài CỐT như tài liệu tham khảo. Bạn có thể lưu bài hoặc bổ sung ví dụ ở phần bình luận.
Mẹo: Hãy tập trung vào quy trình, lỗi thực tế và cách xử lý. CỐT không phải nơi để đăng quảng cáo.

Tín hiệu
Mục đích: Nơi lưu các bài hoặc chủ đề đang được cộng đồng quan tâm.
Cách dùng: Mở Tín hiệu để xem các nội dung liên quan trong Bảng tin. Dữ liệu cũ vẫn được giữ để không mất lịch sử.
Mẹo: Khi chia sẻ một vấn đề đáng chú ý, hãy nêu rõ dấu hiệu, ảnh hưởng và câu hỏi cần cộng đồng cùng kiểm chứng.

Hỏi đáp kỹ thuật
Mục đích: Nơi hỏi và trả lời các vấn đề BIM và MEP, giúp thành viên dễ theo dõi và tìm lại cách giải quyết.
Cách dùng: Viết câu hỏi có đủ bối cảnh, chọn đúng Chủ đề và theo dõi các câu trả lời. Khi vấn đề đã được giải quyết, bạn có thể đánh dấu câu trả lời phù hợp nhất.
Mẹo: Đừng chỉ viết “Revit bị lỗi”. Hãy nói lỗi xuất hiện lúc nào, bạn đã thử cách nào và muốn đạt kết quả gì.

Khóa học
Mục đích: Các bài học có lộ trình, tài liệu và tiến độ rõ ràng, tập trung vào một năng lực cụ thể.
Cách dùng: Mở khóa học để xem mục tiêu, nội dung, điều kiện tham gia và tiến độ. Nếu chưa có Membership, bạn vẫn có thể mua khóa học riêng.
Mẹo: Học theo thứ tự, ghi lại câu hỏi và hoàn thành phần thực hành trước khi chuyển sang bài tiếp theo.

Challenge
Mục đích: Chương trình thực hành theo ngày, giúp biến kiến thức thành sản phẩm hoặc quy trình có thể dùng trong công việc.
Cách dùng: Xem mục tiêu, hướng dẫn, bài tập và bằng chứng cần nộp của từng ngày. Membership cho phép mua mọi Challenge trong cộng đồng; không có Membership vẫn có thể mua riêng từng Challenge.
Mẹo: Hãy nộp bằng chứng từ chính model, bản vẽ hoặc workspace của bạn thay vì chỉ nộp ảnh phần code.

Sự kiện
Mục đích: Nơi theo dõi livestream, workshop, buổi chữa bài và các hoạt động kết nối của cộng đồng.
Cách dùng: Mở sự kiện để xem thời gian, nội dung và trạng thái đăng ký. Hãy kiểm tra lịch trước khi tham gia.
Mẹo: Chuẩn bị câu hỏi trước buổi live và ghi lại việc cần làm sau chương trình.

Bảng xếp hạng
Mục đích: Ghi nhận quá trình học, đóng góp, câu trả lời hữu ích và hoạt động tích cực trong cộng đồng.
Cách dùng: Xem thứ hạng theo thời gian hoặc theo cộng đồng. Điểm số chỉ mang tính tham khảo, không phải lý do để cạnh tranh thiếu lành mạnh.
Mẹo: Hãy tạo giá trị đều đặn bằng cách hoàn thành bài, chia sẻ kinh nghiệm và hỗ trợ người khác.

Marketplace
Mục đích: Nơi tập trung khóa học, Challenge, tài nguyên và sản phẩm được cung cấp trong cộng đồng.
Cách dùng: Đọc kỹ mô tả, đối tượng phù hợp, giá và quyền truy cập trước khi đặt hàng. Sản phẩm ngoài khóa học và Challenge được mua riêng.
Mẹo: Nếu chưa biết bắt đầu từ đâu, hãy chọn sản phẩm gần nhất với công việc hiện tại của bạn.

Gói và đơn hàng
Mục đích: Nơi theo dõi Membership, khóa học, Challenge và các sản phẩm bạn đã mua hoặc đang chờ thanh toán.
Cách dùng: Mở từng đơn hàng để xem trạng thái, sản phẩm, quyền truy cập và hướng dẫn thanh toán.
Mẹo: Nếu đã thanh toán nhưng chưa được kích hoạt, hãy giữ mã giao dịch và gửi Góp ý và Khiếu nại để được kiểm tra.

Affiliate
Mục đích: Chương trình giới thiệu cộng đồng hoặc sản phẩm phù hợp cho người khác.
Cách dùng: Đọc điều kiện, lấy đường dẫn giới thiệu và theo dõi kết quả trong trang Affiliate.
Mẹo: Chỉ giới thiệu nội dung bạn đã hiểu và thấy phù hợp với người nhận. Không gửi tin nhắn hàng loạt.

CV của tôi
Mục đích: Nơi kỹ sư tạo và quản lý CV riêng cho từng cộng đồng, chọn mẫu và kiểm soát thông tin được công khai.
Cách dùng: Điền giới thiệu, chuyên môn, kỹ năng, kinh nghiệm, dự án, chứng chỉ và học vấn. Lưu bản nháp trước khi bật trạng thái công khai.
Mẹo: Mô tả thành tích bằng kết quả cụ thể, công cụ đã dùng và phần việc bạn thực sự đảm nhiệm.

Yêu cầu tuyển dụng
Mục đích: Nơi xem các yêu cầu liên hệ từ nhà tuyển dụng trong đúng cộng đồng.
Cách dùng: Đọc thông tin ẩn danh, sau đó chấp thuận hoặc từ chối. Thông tin liên hệ và cuộc trò chuyện chỉ mở sau khi bạn chấp thuận.
Mẹo: Chỉ công khai thông tin phù hợp và không đưa dữ liệu mật của dự án vào CV.

Góp ý và Khiếu nại
Mục đích: Nơi gửi góp ý, khiếu nại, báo lỗi, vấn đề thanh toán hoặc yêu cầu hỗ trợ đến quản trị viên.
Cách dùng: Chọn đúng loại phiếu, đặt tiêu đề ngắn gọn, mô tả sự việc theo trình tự và đính kèm ảnh nếu cần.
Mẹo: Mỗi phiếu nên tập trung vào một vấn đề để quản trị viên xử lý nhanh và phản hồi chính xác.

Tìm kiếm, thông báo và hồ sơ
Mục đích: Ba công cụ giúp bạn tìm nội dung, theo dõi hoạt động và cập nhật thông tin tài khoản.
Cách dùng: Dùng Tìm kiếm khi nhớ từ khóa. Mở Thông báo để xem lượt thích, bình luận và trả lời. Mở Hồ sơ để cập nhật thông tin cá nhân.
Mẹo: Kiểm tra thông báo sau khi đặt câu hỏi hoặc tham gia một hoạt động để không bỏ lỡ phản hồi.
TEXT;
    }

    public static function rules(): string
    {
        return <<<'TEXT'
Chào mừng bạn đến với cộng đồng
Đây là không gian để học tập, thực hành và kết nối. Nội quy không nhằm tạo ra những quy định cứng nhắc. Đây là kim chỉ nam để mọi thành viên cảm thấy được chào đón, tôn trọng và có động lực đóng góp mỗi ngày.

1. Tôn trọng người khác
Trao đổi lịch sự và tập trung vào vấn đề, không công kích con người. Không miệt thị, đe dọa, quấy rối hoặc khiến thành viên khác cảm thấy không an toàn.

2. Chia sẻ có trách nhiệm
Nói rõ đâu là kinh nghiệm cá nhân và đâu là thông tin đã được kiểm chứng. Không cố ý lan truyền thông tin sai hoặc nội dung dễ gây hiểu lầm.

3. Đăng đúng nơi, đúng chủ đề
Chọn đúng Chủ đề và Loại nội dung khi đăng bài. Đăng đúng nơi giúp người phù hợp dễ nhìn thấy bài viết và giúp cộng đồng giữ được trật tự.

4. Không spam và quảng cáo làm phiền
Không đăng lặp lại cùng một nội dung, kéo tương tác giả hoặc gửi quảng cáo không liên quan. Nếu muốn giới thiệu sản phẩm, hãy dùng đúng khu vực dành cho việc đó.

5. Bảo vệ thông tin riêng tư
Không đăng email, số điện thoại, tài liệu nội bộ, bản vẽ mật hoặc dữ liệu dự án của người khác khi chưa được cho phép. Hãy che thông tin nhạy cảm trước khi tải ảnh lên.

6. Tôn trọng bản quyền và công sức
Ghi nguồn khi sử dụng tài liệu, hình ảnh, quy trình hoặc nội dung của người khác. Không nhận công sức của người khác là của mình và không chia sẻ tài liệu có bản quyền trái phép.

7. Góp ý với tinh thần xây dựng
Khi gặp lỗi hoặc không đồng ý, hãy mô tả sự việc cụ thể và đề xuất hướng cải thiện. Bạn có thể dùng mục Góp ý và Khiếu nại; quản trị viên sẽ tiếp nhận và xem xét công bằng.

8. Khuyến khích người mới
Không phải ai cũng biết thuật ngữ hoặc công cụ ngay từ đầu. Hãy giải thích dễ hiểu, đưa ví dụ và chỉ ra bước tiếp theo thay vì làm người mới ngại đặt câu hỏi.

9. Giữ an toàn cho cộng đồng
Nếu thấy nội dung lừa đảo, quấy rối, vi phạm quyền riêng tư hoặc có nguy cơ gây hại, hãy báo cho quản trị viên. Không tự tổ chức công kích hoặc công khai thông tin của người bị báo cáo.

10. Cùng tạo ra giá trị
Một câu trả lời hữu ích, một lời động viên đúng lúc và một chia sẻ có kinh nghiệm đều giúp cộng đồng tốt hơn. Hãy để lại giá trị sau mỗi lần tham gia và sẵn sàng học hỏi từ người khác.

Khi có vấn đề
Quản trị viên có thể nhắc nhở, ẩn nội dung hoặc giới hạn quyền tương tác khi cần để bảo vệ cộng đồng. Mọi quyết định đều hướng đến một không gian học tập tử tế, minh bạch và có cơ hội sửa sai.
TEXT;
    }

    /** @return array<int, array{title:string, body:string}> */
    public static function sections(string $content): array
    {
        $content = self::normalizeUtf8($content);

        return collect(preg_split('/\R\s*\R/', trim($content)) ?: [])
            ->map(function (string $block): array {
                $lines = preg_split('/\R/', trim(self::normalizeUtf8($block))) ?: [];
                $title = trim((string) array_shift($lines));

                return [
                    'title' => $title,
                    'body' => trim(implode("\n", $lines)),
                ];
            })
            ->filter(fn (array $section) => $section['title'] !== '')
            ->values()
            ->all();
    }
}
