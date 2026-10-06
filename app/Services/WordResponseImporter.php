<?php

namespace App\Services;

use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\ListItem;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;

class WordResponseImporter
{
    public function parse(string $path): array
    {
        $word = IOFactory::load($path);
        $items = [];
        $current = null;

        foreach ($word->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $row = $this->readElement($element);
                if ($row === null || trim($row['text']) === '') continue;

                if ($row['is_title']) {
                    if ($current && trim($current['content']) !== '') $items[] = $current;
                    $current = ['title' => trim($row['text']), 'content' => ''];
                    continue;
                }

                if ($current) {
                    $current['content'] .= ($current['content'] === '' ? '' : "\n") . $row['text'];
                }
            }
        }

        if ($current && trim($current['content']) !== '') $items[] = $current;
        return $items;
    }

    private function readElement(mixed $element): ?array
    {
        if ($element instanceof Text) {
            return ['text' => $element->getText(), 'is_title' => $this->fontIsTitle($element)];
        }

        if ($element instanceof TextRun) {
            $parts = [];
            $formatted = [];
            foreach ($element->getElements() as $child) {
                if ($child instanceof Text) {
                    $text = $child->getText();
                    if ($text !== '') {
                        $parts[] = $text;
                        $formatted[] = $this->fontIsTitle($child);
                    }
                } elseif ($child instanceof TextBreak) {
                    $parts[] = "\n";
                }
            }
            $text = trim(implode('', $parts));
            return $text === '' ? null : ['text' => $text, 'is_title' => count($formatted) > 0 && !in_array(false, $formatted, true)];
        }

        if ($element instanceof ListItem) {
            $text = method_exists($element, 'getTextObject') ? $element->getTextObject()->getText() : '';
            return trim($text) === '' ? null : ['text' => '• ' . trim($text), 'is_title' => false];
        }

        if ($element instanceof AbstractContainer) {
            $parts = [];
            foreach ($element->getElements() as $child) {
                $row = $this->readElement($child);
                if ($row && trim($row['text']) !== '') $parts[] = $row['text'];
            }
            if ($parts) return ['text' => implode("\n", $parts), 'is_title' => false];
        }

        return null;
    }

    private function fontIsTitle(Text $text): bool
    {
        $font = $text->getFontStyle();
        if (!is_object($font)) return false;
        $underline = method_exists($font, 'getUnderline') ? $font->getUnderline() : null;
        return (bool) $font->isBold() && !empty($underline) && $underline !== 'none';
    }
}
