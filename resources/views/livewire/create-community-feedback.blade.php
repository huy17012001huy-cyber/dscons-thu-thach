<div class="community-feedback-form-page">
    <style>
        .community-feedback-form-page { min-height:100%; margin:-1.5rem -2rem; padding:0 24px 34px; background:#EAF4FC; color:#102A3B; }
        .feedback-form-shell { max-width:820px; margin:0 auto; }
        .feedback-form-title { margin:0; padding:1rem 0 1.45rem; color:#102A3B; font-size:1.08rem; font-weight:800; }
        .feedback-form-label { display:block; margin:0 0 .45rem; color:#102A3B; font-size:.82rem; font-weight:800; }
        .feedback-form-field { width:100%; border:1px solid #AFC7D5; border-radius:7px; padding:.78rem .85rem; color:#173B56; background:#fff; font:inherit; font-size:.86rem; outline:none; box-shadow:0 1px 2px rgba(18,59,89,.05); transition:border-color .15s ease,box-shadow .15s ease; }
        .feedback-form-field:hover { border-color:#7FA9C0; }
        .feedback-form-field:focus { border-color:#1F77BE; box-shadow:0 0 0 3px rgba(31,119,190,.16); }
        .feedback-form-field::placeholder { color:#7A8792; }
        .feedback-form-select { appearance:auto; }
        .feedback-form-group { margin-bottom:1.05rem; }
        .feedback-form-content { min-height:250px; resize:vertical; line-height:1.55; }
        .feedback-upload-row { display:flex; align-items:flex-start; gap:.55rem; flex-wrap:wrap; }
        .feedback-upload-box { display:grid; place-items:center; width:96px; height:96px; border:2px dashed #8EADBD; border-radius:10px; background:#F5FAFC; color:#102A3B; cursor:pointer; text-align:center; box-shadow:0 1px 2px rgba(18,59,89,.04); }
        .feedback-upload-box:hover { border-color:#1F77BE; background:#fff; }
        .feedback-upload-box strong { display:block; margin-top:.25rem; font-size:.72rem; font-weight:500; }
        .feedback-upload-preview { display:flex; flex-wrap:wrap; gap:.5rem; }
        .feedback-upload-item { position:relative; }
        .feedback-upload-item img { width:96px; height:96px; border-radius:9px; object-fit:cover; }
        .feedback-remove { position:absolute; top:-7px; right:-7px; display:grid; place-items:center; width:22px; height:22px; border:1px solid #fff; border-radius:50%; background:#125A96; color:#fff; cursor:pointer; }
        .feedback-form-hint { margin:.45rem 0 0; color:#335A73; font-size:.68rem; }
        .feedback-form-actions { display:flex; align-items:center; gap:.7rem; margin-top:1.05rem; }
        .feedback-submit { min-height:47px; padding:.7rem 1.5rem; border:0; border-radius:10px; background:#1F77BE; color:#fff; font:inherit; font-size:.88rem; font-weight:800; cursor:pointer; }
        .feedback-submit:hover { background:#125A96; }
        .feedback-cancel { min-height:44px; padding:.65rem .85rem; border:0; background:transparent; color:#102A3B; font:inherit; font-size:.86rem; cursor:pointer; text-decoration:none; }
        .feedback-back { display:inline-flex; align-items:center; gap:.25rem; margin-top:1rem; color:#125A96; font-size:.72rem; text-decoration:none; }
        .feedback-form-footnote { margin:2.05rem auto 0; color:#47708F; font-size:.7rem; text-align:center; }
        @media(max-width:640px){ .community-feedback-form-page { margin:-.75rem -.65rem; padding:0 14px 30px; } .feedback-form-title { padding-top:.9rem; } .feedback-form-content { min-height:210px; } .feedback-form-actions { align-items:stretch; flex-direction:column; } .feedback-submit,.feedback-cancel { width:100%; text-align:center; } }
    </style>

    <div class="feedback-form-shell">
        <h1 class="feedback-form-title">Gửi góp ý / khiếu nại</h1>

        <form wire:submit="submit">
            <div class="feedback-form-group">
                <label class="feedback-form-label" for="feedback-type">Loại</label>
                <select id="feedback-type" class="feedback-form-field feedback-form-select" wire:model="type">
                    <option value="gop_y">Góp ý</option>
                    <option value="khieu_nai">Khiếu nại</option>
                    <option value="bao_loi">Báo lỗi</option>
                    <option value="thanh_toan">Thanh toán</option>
                    <option value="khac">Khác</option>
                </select>
                @error('type')<p role="alert" style="margin:.3rem 0 0;color:#BE123C;font-size:.72rem;">{{ $message }}</p>@enderror
            </div>

            <div class="feedback-form-group">
                <label class="feedback-form-label" for="feedback-subject">Tiêu đề</label>
                <input id="feedback-subject" class="feedback-form-field" type="text" wire:model="subject" placeholder="Nói ngắn gọn chuyện gì" @error('subject') aria-invalid="true" @enderror>
                @error('subject')<p role="alert" style="margin:.3rem 0 0;color:#BE123C;font-size:.72rem;">{{ $message }}</p>@enderror
            </div>

            <div class="feedback-form-group">
                <label class="feedback-form-label" for="feedback-content">Nội dung</label>
                <textarea id="feedback-content" class="feedback-form-field feedback-form-content" wire:model="content" placeholder="Kể rõ chuyện gì đã xảy ra, ở đâu, lúc nào. Càng cụ thể càng xử lý nhanh." @error('content') aria-invalid="true" @enderror></textarea>
                @error('content')<p role="alert" style="margin:.3rem 0 0;color:#BE123C;font-size:.72rem;">{{ $message }}</p>@enderror
            </div>

            <div class="feedback-form-group">
                <span class="feedback-form-label">Ảnh kèm (tùy chọn)</span>
                <div class="feedback-upload-row">
                    <label class="feedback-upload-box" for="feedback-attachments">
                        <span><x-icon name="plus" size="17" color="#102A3B" /><strong>Thêm ảnh</strong></span>
                        <input id="feedback-attachments" type="file" wire:model="attachments" multiple accept="image/png,image/jpeg,image/webp" style="display:none;">
                    </label>
                    @if(count($attachments))
                        <div class="feedback-upload-preview" aria-label="Ảnh đã chọn">
                            @foreach($attachments as $index => $attachment)
                                <div class="feedback-upload-item">
                                    @if($attachment->isPreviewable())
                                        <img src="{{ $attachment->temporaryUrl() }}" alt="Ảnh đã chọn {{ $index + 1 }}">
                                    @else
                                        <div style="display:grid;place-items:center;width:96px;height:96px;border:1px solid #F6C8C8;border-radius:9px;background:#FFF5F5;color:#BE123C;font-size:.68rem;text-align:center;">Không phải ảnh</div>
                                    @endif
                                    <button type="button" class="feedback-remove" wire:click="removeAttachment({{ $index }})" aria-label="Xóa ảnh">×</button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <p class="feedback-form-hint">JPG, PNG, WebP · Tối đa 5MB/ảnh · tối đa 3 ảnh</p>
                @error('attachments')<p role="alert" style="margin:.3rem 0 0;color:#BE123C;font-size:.72rem;">{{ $message }}</p>@enderror
                @error('attachments.*')<p role="alert" style="margin:.3rem 0 0;color:#BE123C;font-size:.72rem;">{{ $message }}</p>@enderror
            </div>

            <div class="feedback-form-actions">
                <button type="submit" class="feedback-submit" wire:loading.attr="disabled" wire:target="submit,attachments">
                    <span wire:loading.remove wire:target="submit">Gửi phiếu</span>
                    <span wire:loading wire:target="submit">Đang gửi...</span>
                </button>
                <a href="{{ community_route('feedbacks') }}" class="feedback-cancel">Hủy</a>
            </div>
        </form>

        <a href="{{ community_route('feedbacks') }}" class="feedback-back">← Về danh sách phiếu</a>
        <p class="feedback-form-footnote">Website đang trong quá trình xin giấy phép.</p>
    </div>
</div>
