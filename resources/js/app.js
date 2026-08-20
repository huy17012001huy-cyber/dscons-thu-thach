import './bootstrap';

/**
 * Convert pasted HTML (from Claude AI, Google Docs, etc.) to Markdown.
 * Used on textareas via: @paste="window.pasteAsMarkdown($event)"
 */
window.pasteAsMarkdown = function (e) {
    const html = e.clipboardData?.getData('text/html');
    if (!html) return; // plain text paste — let browser handle

    e.preventDefault();

    const doc = new DOMParser().parseFromString(html, 'text/html');
    const md = htmlToMd(doc.body).replace(/\n{3,}/g, '\n\n').trim();

    // Insert at cursor position
    const el = e.target;
    const start = el.selectionStart;
    const end = el.selectionEnd;
    el.value = el.value.slice(0, start) + md + el.value.slice(end);
    el.selectionStart = el.selectionEnd = start + md.length;
    el.dispatchEvent(new Event('input', { bubbles: true }));
};

function htmlToMd(node) {
    let result = '';
    for (const child of node.childNodes) {
        if (child.nodeType === Node.TEXT_NODE) {
            result += child.textContent;
        } else if (child.nodeType === Node.ELEMENT_NODE) {
            const tag = child.tagName.toLowerCase();
            const inner = htmlToMd(child);
            switch (tag) {
                case 'strong': case 'b':
                    result += `**${inner.trim()}** `; break;
                case 'em': case 'i':
                    result += `*${inner.trim()}* `; break;
                case 'h1': result += `\n# ${inner}\n`; break;
                case 'h2': result += `\n## ${inner}\n`; break;
                case 'h3': result += `\n### ${inner}\n`; break;
                case 'p': result += `\n${inner}\n`; break;
                case 'br': result += '\n'; break;
                case 'ul': case 'ol':
                    result += '\n' + inner + '\n'; break;
                case 'li':
                    const isOl = child.parentElement?.tagName.toLowerCase() === 'ol';
                    const idx = Array.from(child.parentElement.children).indexOf(child) + 1;
                    result += isOl ? `${idx}. ${inner.trim()}\n` : `- ${inner.trim()}\n`;
                    break;
                case 'a':
                    result += `[${inner}](${child.getAttribute('href') || ''})`; break;
                case 'blockquote':
                    result += '\n' + inner.trim().split('\n').map(l => '> ' + l).join('\n') + '\n'; break;
                case 'code':
                    result += child.parentElement?.tagName.toLowerCase() === 'pre'
                        ? inner : `\`${inner}\``; break;
                case 'pre':
                    result += `\n\`\`\`\n${inner.trim()}\n\`\`\`\n`; break;
                default:
                    result += inner;
            }
        }
    }
    return result;
}

/**
 * Lightweight Markdown editor used by the post modal.
 * It keeps the Livewire payload as plain Markdown while providing familiar
 * formatting controls without introducing a third-party editor dependency.
 */
window.postEditor = function () {
    return {
        textarea: null,
        history: [],
        historyIndex: -1,
        restoring: false,

        init() {
            this.textarea = this.$refs.editor;
            this.remember(this.textarea?.value || '');
            this.resize();
        },

        remember(value) {
            if (this.restoring || this.history[this.historyIndex] === value) return;
            this.history = this.history.slice(0, this.historyIndex + 1);
            this.history.push(value);
            if (this.history.length > 50) this.history.shift();
            this.historyIndex = this.history.length - 1;
        },

        onInput() {
            this.remember(this.textarea.value);
            this.resize();
        },

        resize() {
            if (!this.textarea) return;
            this.textarea.style.height = 'auto';
            this.textarea.style.height = Math.max(180, this.textarea.scrollHeight) + 'px';
        },

        sync() {
            this.textarea.dispatchEvent(new Event('input', { bubbles: true }));
            this.resize();
        },

        replaceSelection(before, after = '', fallback = 'Nội dung') {
            const value = this.textarea.value;
            const start = this.textarea.selectionStart;
            const end = this.textarea.selectionEnd;
            const selected = value.slice(start, end) || fallback;
            const replacement = before + selected + after;
            this.textarea.setRangeText(replacement, start, end, 'select');
            this.sync();
            this.textarea.focus();
        },

        prefixLines(prefix) {
            const value = this.textarea.value;
            const start = this.textarea.selectionStart;
            const end = this.textarea.selectionEnd;
            const lineStart = value.lastIndexOf('\n', start - 1) + 1;
            const lineEndIndex = value.indexOf('\n', end);
            const lineEnd = lineEndIndex === -1 ? value.length : lineEndIndex;
            const selectedLines = value.slice(lineStart, lineEnd);
            const replacement = selectedLines.split('\n').map(line => prefix + line).join('\n');
            this.textarea.setRangeText(replacement, lineStart, lineEnd, 'select');
            this.sync();
            this.textarea.focus();
        },

        insertLink() {
            const selected = this.textarea.value.slice(this.textarea.selectionStart, this.textarea.selectionEnd) || 'Nội dung link';
            const url = window.prompt('Nhập URL https://');
            if (!url || !/^https?:\/\//i.test(url)) return;
            this.replaceSelection('[', `](${url})`, selected);
        },

        insertVideo() {
            const url = window.prompt('Dán link YouTube hoặc video https://');
            if (!url || !/^https?:\/\//i.test(url)) return;
            this.replaceSelection('', '', url);
        },

        insertEmoji(emoji) {
            const start = this.textarea.selectionStart;
            const end = this.textarea.selectionEnd;
            this.textarea.setRangeText(emoji, start, end, 'end');
            this.sync();
            this.textarea.focus();
        },

        undo() {
            if (this.historyIndex <= 0) return;
            this.historyIndex--;
            this.restore(this.history[this.historyIndex]);
        },

        redo() {
            if (this.historyIndex >= this.history.length - 1) return;
            this.historyIndex++;
            this.restore(this.history[this.historyIndex]);
        },

        restore(value) {
            this.restoring = true;
            this.textarea.value = value;
            this.sync();
            this.restoring = false;
        },
    };
};
