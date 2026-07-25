<?php
/**
 * Convert XHTML string from Trac RSS to markdown.
 *
 * Uses the classic ext/dom DOMDocument so interleaved text nodes between
 * element children are preserved — SimpleXML hides text nodes, which
 * caused widespread silent content loss in comment bodies.
 *
 * LOCAL DIVERGENCE from upstream sirreal/agent-skills: upstream uses
 * Dom\HTMLDocument, which requires PHP 8.4+. This port targets PHP 8.3,
 * so it uses DOMDocument::loadHTML() instead. Two consequences are handled
 * explicitly below:
 *   - loadHTML() assumes ISO-8859-1 for input without a charset declaration,
 *     so non-ASCII is pre-encoded as numeric entities and decoded back on the
 *     way out. Without this, every multibyte character is mojibake.
 *   - loadHTML() is the HTML4 parser and emits warnings on HTML5 constructs;
 *     it returns false rather than throwing on a hard parse failure, so the
 *     failure path degrades to plain text instead of fataling.
 *
 * <pre> bodies are snapshotted via regex before HTML parsing because
 * the parser turns literal <?php ... ?> inside <pre> into comment/PI nodes
 * and <input>/<script> into real elements, which loses code-block content.
 */

function convertXHTMLToMarkdown(string $html): string {
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $preBlocks = [];
    $html = preg_replace_callback(
        '#<pre\b([^>]*)>(.*?)</pre>#is',
        function ($m) use (&$preBlocks) {
            $idx = count($preBlocks);
            $preBlocks[] = $m[2];
            return "<pre{$m[1]}>__WP_TRAC_PRE_{$idx}__</pre>";
        },
        $html
    );

    $wrapped = "<div>{$html}</div>";
    // DOMDocument::loadHTML() has no encoding parameter and defaults to
    // ISO-8859-1 when the markup carries no charset declaration. Escaping every
    // non-ASCII codepoint to a numeric entity first makes the input pure ASCII,
    // so the parser cannot mis-guess; textContent then yields correct UTF-8.
    $wrapped = mb_encode_numericentity(
        $wrapped,
        [0x80, 0x10FFFF, 0, 0x1FFFFF],
        'UTF-8'
    );

    $doc = new DOMDocument();
    // @-suppress non-fatal HTML4-parser warnings (HTML5 tags, Trac's
    // self-closing <br/>). A hard parse failure returns false and is handled
    // below rather than throwing.
    $ok = @$doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    // The wrapper <div> is the outermost element, so it is first in document
    // order even when the body contains divs of its own.
    $root = $ok ? $doc->getElementsByTagName('div')->item(0) : null;
    if ($root === null) {
        // Degrade to tag-stripped text rather than losing the comment entirely.
        return trim(html_entity_decode(
            strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'
        ));
    }

    $out = convertDomNode($root, $preBlocks);
    return preg_replace('/\n{3,}/', "\n\n", trim($out, " \t\n\r\f"));
}

function convertDomNode(DOMNode $node, array $preBlocks): string {
    $result = '';

    foreach ($node->childNodes as $child) {
        if ($child->nodeType === XML_TEXT_NODE) {
            $result .= $child->textContent;
            continue;
        }
        if ($child->nodeType !== XML_ELEMENT_NODE) {
            continue;
        }

        /** @var DOMElement $child */
        $name = strtolower($child->localName);

        switch ($name) {
            case 'br':
                $result .= "\n";
                break;
            case 'p':
                $result .= "\n\n" . convertDomNode($child, $preBlocks) . "\n\n";
                break;
            case 'code':
                $result .= '`' . $child->textContent . '`';
                break;
            case 'pre':
                $class = $child->getAttribute('class');
                $lang = '';
                if ($class !== '' && preg_match('/\bwiki-code-(\w+)\b/', $class, $matches)) {
                    $lang = $matches[1];
                }
                $placeholder = $child->textContent;
                if (preg_match('/__WP_TRAC_PRE_(\d+)__/', $placeholder, $pm)
                    && isset($preBlocks[(int)$pm[1]])
                ) {
                    $raw = $preBlocks[(int)$pm[1]];
                    $raw = preg_replace('#<br\s*/?>#i', "\n", $raw);
                    $raw = preg_replace('#<(a|span)\b[^>]*>(.*?)</\1>#is', '$2', $raw);
                    $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                } else {
                    $raw = $child->textContent;
                }
                $result .= "\n\n```{$lang}\n" . trim($raw) . "\n```\n\n";
                break;
            case 'a':
                $href = $child->getAttribute('href');
                $text = convertDomNode($child, $preBlocks);
                if ($href !== '' && str_starts_with($href, '/')) {
                    $href = "https://core.trac.wordpress.org{$href}";
                }
                if ($href !== '' && $text !== '') {
                    $result .= "[{$text}]({$href})";
                } else {
                    $result .= $text;
                }
                break;
            case 'strong':
            case 'b':
                $result .= '**' . convertDomNode($child, $preBlocks) . '**';
                break;
            case 'em':
            case 'i':
                $result .= '_' . convertDomNode($child, $preBlocks) . '_';
                break;
            case 'ul':
            case 'ol':
                $result .= "\n" . convertDomNode($child, $preBlocks) . "\n";
                break;
            case 'li':
                $result .= '- ' . trim(convertDomNode($child, $preBlocks)) . "\n";
                break;
            case 'blockquote':
                $inner = trim(convertDomNode($child, $preBlocks));
                $quoted = preg_replace('/^/m', '> ', $inner);
                $result .= "\n" . $quoted . "\n";
                break;
            default:
                $result .= convertDomNode($child, $preBlocks);
                break;
        }
    }

    return $result;
}
