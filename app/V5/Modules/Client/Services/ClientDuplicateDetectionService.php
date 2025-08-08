<?php

namespace App\V5\Modules\Client\Services;

use App\V5\Modules\Client\Models\Client;
use App\V5\Modules\Client\Repositories\ClientRepository;
use App\V5\Logging\V5Logger;
use Illuminate\Database\Eloquent\Collection;

/**
 * V5 Client Duplicate Detection Service
 * 
 * Handles detection and management of potential duplicate clients.
 * Provides algorithms for similarity matching and merge suggestions.
 */
class ClientDuplicateDetectionService
{
    public function __construct(
        private ClientRepository $clientRepository
    ) {}

    /**
     * Find potential duplicate clients
     */
    public function findDuplicates(
        int $schoolId,
        string $firstName,
        string $lastName,
        ?string $email = null,
        ?string $phone = null,
        ?int $excludeId = null
    ): Collection {
        V5Logger::debug('Searching for duplicate clients', [
            'school_id' => $schoolId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'exclude_id' => $excludeId,
        ]);

        $duplicates = $this->clientRepository->findPotentialDuplicates(
            $schoolId,
            $firstName,
            $lastName,
            $email,
            $phone,
            $excludeId
        );

        // Calculate similarity scores for ranking
        $rankedDuplicates = $duplicates->map(function ($client) use ($firstName, $lastName, $email, $phone) {
            $similarity = $this->calculateSimilarity($client, $firstName, $lastName, $email, $phone);
            $client->similarity_score = $similarity;
            return $client;
        })->sortByDesc('similarity_score');

        // Filter out low similarity matches
        $filteredDuplicates = $rankedDuplicates->filter(function ($client) {
            return $client->similarity_score >= 0.6; // 60% similarity threshold
        });

        if ($filteredDuplicates->isNotEmpty()) {
            V5Logger::info('Potential duplicates found', [
                'school_id' => $schoolId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'duplicates_count' => $filteredDuplicates->count(),
                'highest_similarity' => $filteredDuplicates->first()->similarity_score ?? 0,
            ]);
        }

        return $filteredDuplicates;
    }

    /**
     * Calculate similarity score between client and provided data
     */
    private function calculateSimilarity(
        Client $client,
        string $firstName,
        string $lastName,
        ?string $email = null,
        ?string $phone = null
    ): float {
        $weights = [
            'name' => 0.4,
            'email' => 0.3,
            'phone' => 0.2,
            'birth_date' => 0.1,
        ];

        $scores = [];

        // Name similarity (most important)
        $nameScore = $this->calculateNameSimilarity(
            $client->first_name . ' ' . $client->last_name,
            $firstName . ' ' . $lastName
        );
        $scores['name'] = $nameScore;

        // Email similarity
        if ($email && $client->email) {
            $scores['email'] = $email === $client->email ? 1.0 : 0.0;
        } else {
            $scores['email'] = 0.5; // Neutral if one is missing
        }

        // Phone similarity
        if ($phone && $client->phone) {
            $scores['phone'] = $this->calculatePhoneSimilarity($client->phone, $phone);
        } else {
            $scores['phone'] = 0.5; // Neutral if one is missing
        }

        // Birth date similarity (if available)
        $scores['birth_date'] = 0.5; // Neutral for now, would need birth date comparison

        // Calculate weighted average
        $totalScore = 0;
        $totalWeight = 0;

        foreach ($weights as $factor => $weight) {
            if (isset($scores[$factor])) {
                $totalScore += $scores[$factor] * $weight;
                $totalWeight += $weight;
            }
        }

        return $totalWeight > 0 ? $totalScore / $totalWeight : 0;
    }

    /**
     * Calculate name similarity using multiple algorithms
     */
    private function calculateNameSimilarity(string $name1, string $name2): float
    {
        // Normalize names
        $name1 = $this->normalizeName($name1);
        $name2 = $this->normalizeName($name2);

        // Exact match
        if ($name1 === $name2) {
            return 1.0;
        }

        // Calculate different similarity metrics
        $levenshtein = $this->calculateLevenshteinSimilarity($name1, $name2);
        $jaro = $this->calculateJaroSimilarity($name1, $name2);
        $metaphone = $this->calculateMetaphoneSimilarity($name1, $name2);

        // Weighted combination
        return ($levenshtein * 0.4) + ($jaro * 0.4) + ($metaphone * 0.2);
    }

    /**
     * Normalize name for comparison
     */
    private function normalizeName(string $name): string
    {
        // Convert to lowercase, remove accents, extra spaces
        $name = strtolower($name);
        $name = $this->removeAccents($name);
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }

    /**
     * Remove accents from string
     */
    private function removeAccents(string $string): string
    {
        $unwanted_array = [
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
        ];
        
        return strtr($string, $unwanted_array);
    }

    /**
     * Calculate Levenshtein similarity
     */
    private function calculateLevenshteinSimilarity(string $str1, string $str2): float
    {
        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen === 0) return 1.0;

        $distance = levenshtein($str1, $str2);
        return 1 - ($distance / $maxLen);
    }

    /**
     * Calculate Jaro similarity (simplified)
     */
    private function calculateJaroSimilarity(string $str1, string $str2): float
    {
        $len1 = strlen($str1);
        $len2 = strlen($str2);

        if ($len1 === 0 && $len2 === 0) return 1.0;
        if ($len1 === 0 || $len2 === 0) return 0.0;

        $matchWindow = max($len1, $len2) / 2 - 1;
        if ($matchWindow < 0) $matchWindow = 0;

        $str1Matches = array_fill(0, $len1, false);
        $str2Matches = array_fill(0, $len2, false);

        $matches = 0;
        $transpositions = 0;

        // Find matches
        for ($i = 0; $i < $len1; $i++) {
            $start = max(0, $i - $matchWindow);
            $end = min($i + $matchWindow + 1, $len2);

            for ($j = $start; $j < $end; $j++) {
                if ($str2Matches[$j] || $str1[$i] !== $str2[$j]) continue;
                
                $str1Matches[$i] = true;
                $str2Matches[$j] = true;
                $matches++;
                break;
            }
        }

        if ($matches === 0) return 0.0;

        // Find transpositions
        $k = 0;
        for ($i = 0; $i < $len1; $i++) {
            if (!$str1Matches[$i]) continue;
            
            while (!$str2Matches[$k]) $k++;
            
            if ($str1[$i] !== $str2[$k]) $transpositions++;
            $k++;
        }

        return (($matches / $len1) + ($matches / $len2) + (($matches - $transpositions / 2) / $matches)) / 3;
    }

    /**
     * Calculate Metaphone similarity
     */
    private function calculateMetaphoneSimilarity(string $str1, string $str2): float
    {
        $metaphone1 = metaphone($str1);
        $metaphone2 = metaphone($str2);

        return $metaphone1 === $metaphone2 ? 1.0 : 0.0;
    }

    /**
     * Calculate phone similarity
     */
    private function calculatePhoneSimilarity(string $phone1, string $phone2): float
    {
        // Normalize phone numbers (remove spaces, dashes, etc.)
        $normalized1 = preg_replace('/\D/', '', $phone1);
        $normalized2 = preg_replace('/\D/', '', $phone2);

        // Exact match
        if ($normalized1 === $normalized2) {
            return 1.0;
        }

        // Check if one is a subset of the other (different formats)
        if (strlen($normalized1) > strlen($normalized2)) {
            $longer = $normalized1;
            $shorter = $normalized2;
        } else {
            $longer = $normalized2;
            $shorter = $normalized1;
        }

        // Check if shorter number is at the end of longer (international vs local)
        if (str_ends_with($longer, $shorter)) {
            return 0.9;
        }

        // Calculate similarity using Levenshtein
        return $this->calculateLevenshteinSimilarity($normalized1, $normalized2);
    }

    /**
     * Find all duplicate groups in a school
     */
    public function findAllDuplicateGroups(int $schoolId): array
    {
        V5Logger::info('Finding all duplicate groups', [
            'school_id' => $schoolId,
        ]);

        $clients = $this->clientRepository->getClients($schoolId, [], 1, 1000)->items();
        $duplicateGroups = [];
        $processedIds = [];

        foreach ($clients as $client) {
            if (in_array($client->id, $processedIds)) {
                continue;
            }

            $duplicates = $this->findDuplicates(
                $schoolId,
                $client->first_name,
                $client->last_name,
                $client->email,
                $client->phone,
                $client->id
            );

            if ($duplicates->isNotEmpty()) {
                $group = collect([$client])->merge($duplicates);
                $duplicateGroups[] = [
                    'master_client' => $client,
                    'duplicates' => $duplicates,
                    'group_size' => $group->count(),
                    'confidence' => $duplicates->first()->similarity_score ?? 0,
                ];

                // Mark all clients in this group as processed
                foreach ($group as $groupClient) {
                    $processedIds[] = $groupClient->id;
                }
            }

            $processedIds[] = $client->id;
        }

        // Sort by confidence and group size
        usort($duplicateGroups, function ($a, $b) {
            if ($a['confidence'] === $b['confidence']) {
                return $b['group_size'] <=> $a['group_size'];
            }
            return $b['confidence'] <=> $a['confidence'];
        });

        V5Logger::info('Duplicate groups found', [
            'school_id' => $schoolId,
            'groups_count' => count($duplicateGroups),
        ]);

        return $duplicateGroups;
    }

    /**
     * Generate merge suggestions for duplicate clients
     */
    public function generateMergeSuggestions(Collection $duplicates): array
    {
        if ($duplicates->count() < 2) {
            return [];
        }

        // Sort by various criteria to determine the best master record
        $candidates = $duplicates->sortByDesc(function ($client) {
            $score = 0;
            
            // More bookings = higher score
            $score += $client->total_bookings * 10;
            
            // More complete profile = higher score
            if ($client->email) $score += 5;
            if ($client->phone) $score += 5;
            if ($client->date_of_birth) $score += 3;
            if ($client->address) $score += 2;
            
            // More recent activity = higher score
            if ($client->last_activity_at) {
                $daysSinceActivity = $client->last_activity_at->diffInDays(now());
                $score += max(0, 30 - $daysSinceActivity);
            }
            
            // Higher spending = higher score
            $score += $client->total_spent / 10;
            
            return $score;
        });

        $masterClient = $candidates->first();
        $clientsToMerge = $candidates->skip(1);

        return [
            'master_client' => $masterClient,
            'clients_to_merge' => $clientsToMerge,
            'merge_strategy' => $this->generateMergeStrategy($masterClient, $clientsToMerge),
            'data_conflicts' => $this->identifyDataConflicts($candidates),
            'estimated_impact' => $this->calculateMergeImpact($candidates),
        ];
    }

    /**
     * Generate merge strategy
     */
    private function generateMergeStrategy(Client $master, Collection $duplicates): array
    {
        $strategy = [
            'keep_master_data' => [],
            'merge_from_duplicates' => [],
            'manual_review_required' => [],
        ];

        $fields = ['email', 'phone', 'date_of_birth', 'address', 'emergency_contact', 'medical_conditions', 'preferences'];

        foreach ($fields as $field) {
            if ($master->$field) {
                $strategy['keep_master_data'][] = $field;
            } else {
                // Find first duplicate with this field populated
                $duplicate = $duplicates->first(fn($client) => $client->$field);
                if ($duplicate) {
                    $strategy['merge_from_duplicates'][] = [
                        'field' => $field,
                        'source_client_id' => $duplicate->id,
                        'value' => $duplicate->$field,
                    ];
                }
            }
        }

        // Always merge financial data
        $strategy['financial_merge'] = [
            'total_spent' => $duplicates->sum('total_spent') + $master->total_spent,
            'total_bookings' => $duplicates->sum('total_bookings') + $master->total_bookings,
        ];

        return $strategy;
    }

    /**
     * Identify data conflicts between duplicate clients
     */
    private function identifyDataConflicts(Collection $clients): array
    {
        $conflicts = [];
        $fields = ['email', 'phone', 'date_of_birth', 'first_name', 'last_name'];

        foreach ($fields as $field) {
            $values = $clients->pluck($field)->filter()->unique();
            
            if ($values->count() > 1) {
                $conflicts[$field] = $values->map(function ($value) use ($clients, $field) {
                    return [
                        'value' => $value,
                        'clients' => $clients->where($field, $value)->pluck('id')->toArray(),
                    ];
                })->values()->toArray();
            }
        }

        return $conflicts;
    }

    /**
     * Calculate merge impact
     */
    private function calculateMergeImpact(Collection $clients): array
    {
        return [
            'clients_to_remove' => $clients->count() - 1,
            'bookings_to_reassign' => $clients->skip(1)->sum('total_bookings'),
            'total_value_consolidated' => $clients->sum('total_spent'),
            'data_completeness_improvement' => $this->calculateDataCompletenessImprovement($clients),
        ];
    }

    /**
     * Calculate data completeness improvement
     */
    private function calculateDataCompletenessImprovement(Collection $clients): float
    {
        $fields = ['email', 'phone', 'date_of_birth', 'address'];
        $master = $clients->first();
        
        $beforeCompleteness = 0;
        $afterCompleteness = 0;
        
        foreach ($fields as $field) {
            if ($master->$field) {
                $beforeCompleteness++;
                $afterCompleteness++;
            } else {
                // Check if any duplicate has this field
                if ($clients->first(fn($client) => $client->$field)) {
                    $afterCompleteness++;
                }
            }
        }

        return $afterCompleteness - $beforeCompleteness;
    }

    /**
     * Execute client merge (would need careful implementation)
     */
    public function executeClientMerge(int $masterId, array $duplicateIds, int $schoolId): array
    {
        V5Logger::info('Executing client merge', [
            'master_id' => $masterId,
            'duplicate_ids' => $duplicateIds,
            'school_id' => $schoolId,
        ]);

        // This would need to be implemented carefully with database transactions
        // and proper handling of all related data (bookings, payments, etc.)
        
        throw new \Exception('Client merge functionality not yet implemented for safety reasons');
    }
}