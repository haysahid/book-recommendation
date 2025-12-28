<?php

namespace App\Repositories;

class TfIdfRepository
{


    public static function cleanText(string $text): string
    {
        // Remove extra spaces and special characters, convert to lowercase
        $cleanedText = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $cleanedText = strtolower(trim($cleanedText));
        return $cleanedText;
    }

    public static function stopwordRemoval(string $text): string
    {
        $stopwordFactory = new \Sastrawi\StopWordRemover\StopWordRemoverFactory();
        $stopwordRemover = $stopwordFactory->createStopWordRemover();
        $textWithoutStopwords = $stopwordRemover->remove($text);
        return $textWithoutStopwords;
    }

    public static function stemming(string $text): string
    {
        $stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
        $stemmer = $stemmerFactory->createStemmer();
        $stemmedText = $stemmer->stem($text);
        return $stemmedText;
    }

    public static function tokenize(string $text): array
    {
        // Simple whitespace tokenization
        $tokens = preg_split('/\s+/', $text);
        return $tokens;
    }

    public static function preprocess(string $text): array
    {
        $cleanedText = self::cleanText($text);
        $textWithoutStopwords = self::stopwordRemoval($cleanedText);
        $stemmedText = self::stemming($textWithoutStopwords);
        $tokens = self::tokenize($stemmedText);

        return $tokens;
    }

    public static function computeDf(array $docsTokens)
    {
        $N = count($docsTokens);

        // DF
        $df = [];
        foreach ($docsTokens as $term) {
            $uniqueTerms = array_unique($term);
            foreach ($uniqueTerms as $t) {
                if (!isset($df[$t])) {
                    $df[$t] = 0;
                }
                $df[$t]++;
            }
        }

        return [$df, $N];
    }

    public static function buildTfIdfVector(array $tokens, array $df, int $N): array
    {
        // TF
        $tf = [];
        foreach ($tokens as $term) {
            if (!isset($tf[$term])) {
                $tf[$term] = 0;
            }
            $tf[$term]++;
        }

        // TF-IDF
        $vector = [];
        foreach ($tokens as $term) {
            $termTf = $tf[$term];
            $termDf = $df[$term] ?? 0;
            $idf = $termDf > 0 ? log10(($N + 1) / ($termDf + 1)) + 1 : log10($N + 1);
            $vector[$term] = $termTf * $idf;
        }

        return $vector;
    }

    public static function cosineSimilarity(array $vecA, array $vecB): float
    {
        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        $allTerms = array_unique(array_merge(array_keys($vecA), array_keys($vecB)));

        foreach ($allTerms as $term) {
            $a = $vecA[$term] ?? 0.0;
            $b = $vecB[$term] ?? 0.0;

            $dotProduct += $a * $b;
            $magnitudeA += $a * $a;
            $magnitudeB += $b * $b;
        }

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($magnitudeA) * sqrt($magnitudeB));
    }
}
