<?php

namespace App\Support;

use DOMDocument;
use DOMElement;

class PostHtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 's', 'u', 'a',
        'ul', 'ol', 'li', 'blockquote', 'h2', 'h3', 'code', 'pre',
    ];

    public function sanitize(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        if (! class_exists(DOMDocument::class)) {
            return strip_tags($html, '<p><br><strong><b><em><i><s><u><a><ul><ol><li><blockquote><h2><h3><code><pre>');
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="post-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $document->getElementById('post-root');
        if (! $root) {
            return '';
        }

        $this->sanitizeChildren($root);

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    private function sanitizeChildren(DOMElement $parent): void
    {
        for ($index = $parent->childNodes->length - 1; $index >= 0; $index--) {
            $node = $parent->childNodes->item($index);
            if (! $node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->tagName);
            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                $this->replaceWithText($node);

                continue;
            }

            foreach (iterator_to_array($node->attributes) as $attribute) {
                $name = strtolower($attribute->name);
                if ($tag === 'a' && $name === 'href' && preg_match('/^https?:\/\//i', $attribute->value)) {
                    continue;
                }
                $node->removeAttribute($attribute->name);
            }

            if ($tag === 'a' && $node->hasAttribute('href')) {
                $node->setAttribute('target', '_blank');
                $node->setAttribute('rel', 'noopener noreferrer nofollow');
            }

            $this->sanitizeChildren($node);
        }
    }

    private function replaceWithText(DOMElement $node): void
    {
        $parent = $node->parentNode;
        if (! $parent) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }
        $parent->removeChild($node);
    }
}
