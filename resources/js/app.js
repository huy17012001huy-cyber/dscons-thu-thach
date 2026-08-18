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
