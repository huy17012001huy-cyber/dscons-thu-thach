<?php

namespace App\Support;

final class CommunityContentDefaults
{
    public static function resolve(?string $content, string $default): string
    {
        $content = trim((string) $content);

        // A replacement character means the text was already decoded with the
        // wrong charset before it reached the database. Rendering it again
        // cannot recover the missing Vietnamese characters, so use the clean
        // default until an administrator saves corrected content.
        if ($content === '' || str_contains($content, "\u{FFFD}")) {
            return $default;
        }

        return self::normalizeUtf8($content);
    }

    /**
     * Keep administrator-authored guidance renderable even when it contains
     * a stray invalid byte from a copied document or legacy encoding.
     */
    public static function normalizeUtf8(string $content): string
    {
        if (function_exists('mb_check_encoding') && mb_check_encoding($content, 'UTF-8')) {
            return $content;
        }

        $normalized = iconv('UTF-8', 'UTF-8//IGNORE', $content);

        return $normalized === false ? $content : $normalized;
    }

    public static function guide(): string
    {
        return <<<'TEXT'
Bảng tin
Mục đích: Đây là nơi cập nhật chính của cộng đồng. Bạn có thể đăng bài, đọc chia sẻ, đặt câu hỏi và trao đổi với các kỹ sư khác.
Cách dùng: Dùng bộ lọc Chủ đề và Loại nội dung để tìm đúng bài. Mở một bài để đọc đầy đủ, thích, bình luận, trả lời, lưu hoặc chia sẻ đường dẫn.
Mẹo: Một bài viết dễ nhận được hỗ trợ nên có tiêu đề rõ, bối cảnh cụ thể, phiên bản phần mềm và ảnh minh họa nếu vấn đề nằm trên màn hình.

CỐT
Mục đích: CỐT là khu vực dành cho những bài viết có giá trị thực tiễn, được chọn để thành viên dễ tìm lại và học từ kinh nghiệm đã được kiểm chứng.
Cách dùng: Đọc các bài CỐT như tài liệu tham khảo. Nếu thấy một bài hữu ích, hãy lưu lại hoặc bổ sung thêm ví dụ trong phần bình luận.
Mẹo: CỐT không phải nơi để quảng cáo. Hãy tập trung vào quy trình, lỗi thực tế, cách xử lý và bài học rút ra.

Tín hiệu
Mục đích: Tín hiệu dùng để đánh dấu các chủ đề hoặc cập nhật đáng chú ý trong lịch sử cộng đồng.
Cách dùng: Khi mở Tín hiệu, bạn sẽ được đưa về dòng nội dung liên quan trong Bảng tin. Dữ liệu Tín hiệu cũ vẫn được giữ để không làm mất lịch sử.
Mẹo: Nếu muốn chia sẻ một xu hướng hoặc vấn đề đang được nhiều người quan tâm, hãy viết rõ dấu hiệu, tác động và câu hỏi bạn muốn cộng đồng cùng kiểm chứng.

Hỏi đáp kỹ thuật
Mục đích: Đây là nơi hỏi và trả lời các vấn đề BIM/MEP một cách tập trung, dễ tra cứu hơn bài đăng thông thường.
Cách dùng: Viết câu hỏi có đủ bối cảnh, chọn chủ đề phù hợp và theo dõi các câu trả lời. Bạn có thể đánh dấu câu trả lời tốt nhất khi vấn đề đã được giải quyết.
Mẹo: Tránh hỏi quá chung như “Revit bị lỗi”. Hãy ghi lỗi xuất hiện lúc nào, bạn đã thử gì và kết quả mong muốn là gì.

Khóa học
Mục đích: Khóa học là lộ trình học có bài, tài liệu và tiến độ. Mỗi khóa học tập trung vào một năng lực cụ thể.
Cách dùng: Mở trang khóa học để xem mục tiêu, nội dung, điều kiện truy cập và trạng thái đã học. Bạn có thể mua lẻ khóa học nếu chưa có Membership.
Mẹo: Học theo thứ tự bài, ghi lại câu hỏi và hoàn thành phần thực hành trước khi chuyển sang bài tiếp theo.

Challenge
Mục đích: Challenge là chương trình thực hành theo ngày, giúp biến kiến thức thành sản phẩm hoặc quy trình có thể dùng trong công việc.
Cách dùng: Xem mục tiêu, SOP, bài tập và bằng chứng cần nộp của từng ngày. Membership cho phép mua các Challenge trong cộng đồng; không có Membership vẫn có thể mua từng Challenge.
Mẹo: Đừng chỉ đọc hướng dẫn. Hãy nộp bằng chứng từ chính model, bản vẽ hoặc workspace của bạn để kết quả có giá trị thật.

Sự kiện
Mục đích: Theo dõi các buổi livestream, workshop, review bài và hoạt động kết nối của cộng đồng.
Cách dùng: Mở sự kiện để xem thời gian, nội dung và trạng thái đăng ký. Hãy kiểm tra múi giờ và lịch trước khi tham gia.
Mẹo: Chuẩn bị câu hỏi trước buổi live và ghi lại phần hành động cần làm sau buổi học.

Bảng xếp hạng
Mục đích: Ghi nhận quá trình học, đóng góp, câu trả lời hữu ích và hoạt động tích cực trong cộng đồng.
Cách dùng: Xem thứ hạng theo giai đoạn hoặc theo cộng đồng. Điểm số là tín hiệu tham khảo, không phải lý do để cạnh tranh thiếu lành mạnh.
Mẹo: Tập trung tạo giá trị đều đặn: hoàn thành bài, chia sẻ kinh nghiệm và hỗ trợ người khác.

Marketplace
Mục đích: Nơi tập trung các khóa học, Challenge, tài nguyên và sản phẩm được bán trong cộng đồng.
Cách dùng: Đọc kỹ mô tả, đối tượng phù hợp, giá và quyền truy cập trước khi đặt hàng. Các sản phẩm ngoài khóa học và Challenge được mua riêng.
Mẹo: Nếu chưa biết bắt đầu từ đâu, hãy chọn sản phẩm có mục tiêu gần nhất với công việc hiện tại của bạn.

Gói & Đơn hàng
Mục đích: Theo dõi Membership, khóa học, Challenge và các sản phẩm bạn đã đặt hoặc đang chờ thanh toán.
Cách dùng: Mở từng đơn để xem trạng thái, nội dung, quyền truy cập và hướng dẫn thanh toán.
Mẹo: Nếu đã thanh toán nhưng chưa được kích hoạt, hãy giữ mã giao dịch và gửi Góp ý & Khiếu nại để được kiểm tra.

Affiliate
Mục đích: Giới thiệu cộng đồng hoặc sản phẩm phù hợp cho người khác theo chương trình của nền tảng.
Cách dùng: Đọc điều kiện, lấy đường dẫn giới thiệu và theo dõi kết quả ngay trong trang Affiliate.
Mẹo: Chỉ giới thiệu nội dung bạn đã hiểu và phù hợp với người nhận; không gửi spam.

CV của tôi
Mục đích: Kỹ sư tạo và quản lý một CV riêng cho từng community, chọn mẫu, thông tin hiển thị và trạng thái công khai.
Cách dùng: Điền headline, chuyên môn, kỹ năng, kinh nghiệm, dự án, chứng chỉ và học vấn. Lưu nháp trước khi công khai tìm kiếm.
Mẹo: Viết thành tích bằng kết quả cụ thể, ví dụ loại dự án, quy mô, công cụ và phần việc bạn thực sự đảm nhiệm.

Yêu cầu tuyển dụng
Mục đích: Xem các yêu cầu liên hệ từ nhà tuyển dụng trong đúng community.
Cách dùng: Đọc thông tin ẩn danh, sau đó chấp thuận hoặc từ chối. Chỉ khi chấp thuận, thông tin liên hệ và cuộc trò chuyện mới được mở.
Mẹo: Chỉ công khai những thông tin bạn thấy phù hợp và không đưa dữ liệu dự án mật vào CV.

Góp ý & Khiếu nại
Mục đích: Gửi góp ý, khiếu nại, báo lỗi, vấn đề thanh toán hoặc nội dung khác đến ban quản trị.
Cách dùng: Chọn đúng loại phiếu, đặt tiêu đề ngắn gọn, mô tả sự việc theo thời gian và đính kèm ảnh nếu cần.
Mẹo: Một phiếu chỉ nên tập trung vào một vấn đề để quản trị viên xử lý nhanh và phản hồi chính xác.

Tìm kiếm, thông báo và hồ sơ
Mục đích: Tìm bài viết, theo dõi tương tác và cập nhật thông tin tài khoản.
Cách dùng: Dùng Tìm kiếm khi nhớ từ khóa; mở Thông báo để xem lượt thích, bình luận, trả lời và hoạt động liên quan đến bạn; mở Hồ sơ để cập nhật thông tin cá nhân.
Mẹo: Kiểm tra thông báo sau khi đăng câu hỏi hoặc tham gia một hoạt động để không bỏ lỡ phản hồi.
TEXT;
    }

    public static function rules(): string
    {
        return <<<'TEXT'
Chào mừng bạn đến với cộng đồng
Đây là không gian để học tập, thực hành và kết nối. Nội quy không phải những luật lệ cứng nhắc; đây là kim chỉ nam để mỗi thành viên cảm thấy được chào đón, tôn trọng và được truyền cảm hứng mỗi ngày.

1. Tôn trọng người khác
Trao đổi với thái độ lịch sự, tập trung vào vấn đề thay vì công kích con người. Không miệt thị, đe dọa, quấy rối hoặc tạo cảm giác không an toàn cho thành viên khác.

2. Chia sẻ có trách nhiệm
Nói rõ đâu là kinh nghiệm cá nhân, đâu là thông tin đã kiểm chứng. Không cố ý lan truyền thông tin sai, nội dung gây hiểu lầm hoặc lời hứa không có cơ sở.

3. Đăng đúng nơi, đúng chủ đề
Chọn đúng Chủ đề và Loại nội dung khi đăng bài. Một bài đúng nơi sẽ giúp người phù hợp nhìn thấy và giúp cộng đồng giữ được trật tự tự nhiên.

4. Không spam và quảng cáo gây phiền
Không đăng lặp lại cùng một nội dung, kéo tương tác giả hoặc gửi quảng cáo không liên quan. Nếu muốn giới thiệu sản phẩm, hãy tuân thủ khu vực và quy định dành cho việc đó.

5. Bảo vệ dữ liệu riêng tư
Không đăng thông tin cá nhân, email, số điện thoại, tài liệu nội bộ, bản vẽ mật hoặc dữ liệu dự án của người khác khi chưa được cho phép. Hãy che các thông tin nhạy cảm trước khi tải ảnh lên.

6. Tôn trọng bản quyền và công sức
Ghi nguồn khi sử dụng tài liệu, hình ảnh, quy trình hoặc nội dung của người khác. Không nhận công sức của người khác là của mình và không chia sẻ tài liệu có bản quyền trái phép.

7. Góp ý bằng tinh thần xây dựng
Khi gặp lỗi hoặc không đồng ý, hãy mô tả sự việc cụ thể và đề xuất hướng cải thiện. Bạn có thể dùng mục Góp ý & Khiếu nại; quản trị viên sẽ tiếp nhận mà không đánh giá cá nhân bạn.

8. Khuyến khích người mới
Không phải ai cũng biết thuật ngữ hoặc công cụ ngay từ đầu. Hãy giải thích dễ hiểu, đưa ví dụ và chỉ ra bước tiếp theo thay vì làm người mới ngại đặt câu hỏi.

9. Giữ an toàn cho cộng đồng
Nếu thấy nội dung lừa đảo, quấy rối, vi phạm quyền riêng tư hoặc có nguy cơ gây hại, hãy báo ngay cho quản trị viên. Không tự tổ chức công kích hoặc công khai thông tin của người bị báo cáo.

10. Cùng tạo ra giá trị
Một câu trả lời hữu ích, một lời động viên đúng lúc và một chia sẻ thật đều làm cộng đồng tốt hơn. Hãy để lại giá trị sau mỗi lần tham gia và sẵn sàng học hỏi từ người khác.

Khi có vấn đề
Quản trị viên có thể nhắc nhở, ẩn nội dung hoặc giới hạn quyền tương tác khi cần để bảo vệ cộng đồng. Mọi quyết định đều hướng tới việc khôi phục một không gian học tập tử tế, minh bạch và có cơ hội sửa sai.
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
