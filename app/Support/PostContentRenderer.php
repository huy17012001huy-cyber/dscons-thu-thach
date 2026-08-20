<?php

namespace App\Support;

use Illuminate\Support\Str;

class PostContentRenderer
{
    public function render(string $content, bool $truncate = false, int $limit = 500): string
    {
        $content = $truncate && mb_strlen($content) > $limit
            ? Str::limit($content, $limit)
            : $content;

        $embeds = [];
        $prepared = preg_replace_callback(
            '#https?://(?:www\.)?(?:youtube\.com/watch\?v=|youtu\.be/)([a-zA-Z0-9_-]{11})(?:[^\s<]*)#i',
            function (array $match) use (&$embeds): string {
                $token = '__DSCONS_YOUTUBE_'.count($embeds).'__';
                $embeds[$token] = $this->youtubeEmbed($match[1]);
                return "\n\n{$token}\n\n";
            },
            $content
        ) ?? $content;

        $html = Str::markdown($prepared, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        foreach ($embeds as $token => $embed) {
            $html = str_replace('<p>'.$token.'</p>', $embed, $html);
            $html = str_replace($token, $embed, $html);
        }

        // Markdown has already stripped unsafe HTML and links. Add safe external-link behavior.
        $html = preg_replace_callback(
            '/<a\s+href="([^"]+)"([^>]*)>/i',
            static fn (array $match): string => '<a href="'.e($match[1]).'" target="_blank" rel="noopener noreferrer nofollow"'.$match[2].'>',
            $html
        ) ?? $html;

        // League CommonMark escapes an already entity-escaped query string a
        // second time. Collapse only that harmless duplicate entity so URLs
        // render and serialize as `&amp;` while remaining HTML-safe.
        $html = str_replace('&amp;amp;', '&amp;', $html);

        return $html;
    }

    public function excerpt(string $content, int $limit = 180): string
    {
        $plainText = trim(preg_replace('/\s+/', ' ', strip_tags($this->render($content))) ?? '');
        return Str::limit($plainText, $limit);
    }

    private function youtubeEmbed(string $videoId): string
    {
        return '<div class="post-video-embed">'
            .'<iframe src="https://www.youtube.com/embed/'.e($videoId).'" '
            .'title="YouTube video" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'
            .'</div>';
    }
}
