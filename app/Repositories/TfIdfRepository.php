<?php

namespace App\Repositories;

class TfIdfRepository
{
    static public function tokenize(string $text): array
    {
        // Simple whitespace tokenization
        $tokens = preg_split('/\s+/', $text);
        return $tokens;
    }

    static public function preprocess(string $text): array
    {
        // Remove extra spaces, special characters, and convert to lowercase
        $cleanedText = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $cleanedText = strtolower(trim($cleanedText));

        // Remove stopwords
        $stopwordFactory = new \Sastrawi\StopWordRemover\StopWordRemoverFactory();
        $stopwordRemover = $stopwordFactory->createStopWordRemover();
        $textWithoutStopwords = $stopwordRemover->remove($cleanedText);

        // Perform stemming
        $stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
        $stemmer = $stemmerFactory->createStemmer();
        $stemmedText = $stemmer->stem($textWithoutStopwords);

        // Tokenization
        $tokens = self::tokenize($stemmedText);

        return $tokens;
    }

    static public function computeDf(array $docsTokens)
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

    static public function buildTfIdfVector(array $tokens, array $df, int $N): array
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

    static public function cosineSimilarity(array $vecA, array $vecB): float
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
