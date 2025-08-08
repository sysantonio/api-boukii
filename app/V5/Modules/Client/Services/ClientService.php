<?php

namespace App\V5\Modules\Client\Services;

use App\V5\Modules\Client\Models\Client;
use App\V5\Modules\Client\Repositories\ClientRepository;
use App\V5\Exceptions\ClientValidationException;
use App\V5\Exceptions\ClientNotFoundException;
use App\V5\Logging\V5Logger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * V5 Client Service
 * 
 * Main business logic service for client operations.
 * Coordinates between repositories and handles complex business rules.
 */
class ClientService
{
    public function __construct(
        private ClientRepository $clientRepository,
        private ClientProfilingService $profilingService,
        private ClientDuplicateDetectionService $duplicateService
    ) {}

    /**
     * Create a new client
     */
    public function createClient(array $data, int $schoolId): Client
    {
        V5Logger::info('Creating new client', [
            'school_id' => $schoolId,
            'email' => $data['email'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
        ]);

        // Validate required fields
        $this->validateClientData($data);

        // Check for duplicates
        $duplicates = $this->duplicateService->findDuplicates(
            $schoolId,
            $data['first_name'],
            $data['last_name'],
            $data['email'] ?? null,
            $data['phone'] ?? null
        );

        if ($duplicates->isNotEmpty()) {
            V5Logger::warning('Potential duplicate client detected', [
                'school_id' => $schoolId,
                'email' => $data['email'] ?? null,
                'duplicates_count' => $duplicates->count(),
            ]);

            throw new ClientValidationException(
                'Potential duplicate client found. Please verify client information.',
                ['duplicates' => $duplicates->toArray()]
            );
        }

        // Add school context
        $data['school_id'] = $schoolId;

        // Set default values
        $data['status'] = $data['status'] ?? Client::STATUS_ACTIVE;
        $data['profile_type'] = Client::PROFILE_NEW;
        $data['loyalty_tier'] = Client::TIER_BRONZE;
        $data['total_spent'] = 0;
        $data['total_bookings'] = 0;
        $data['average_rating'] = 0;

        try {
            $client = $this->clientRepository->create($data);

            V5Logger::info('Client created successfully', [
                'client_id' => $client->id,
                'client_reference' => $client->client_reference,
                'school_id' => $schoolId,
            ]);

            return $client;

        } catch (\Exception $e) {
            V5Logger::error('Failed to create client', [
                'school_id' => $schoolId,
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new ClientValidationException('Failed to create client: ' . $e->getMessage());
        }
    }

    /**
     * Find client by ID
     */
    public function findClientById(int $id, int $schoolId): Client
    {
        $client = $this->clientRepository->findById($id, $schoolId);

        if (!$client) {
            V5Logger::warning('Client not found', [
                'client_id' => $id,
                'school_id' => $schoolId,
            ]);

            throw new ClientNotFoundException("Client not found with ID: {$id}");
        }

        return $client;
    }

    /**
     * Find client by reference
     */
    public function findClientByReference(string $reference, int $schoolId): Client
    {
        $client = $this->clientRepository->findByReference($reference, $schoolId);

        if (!$client) {
            throw new ClientNotFoundException("Client not found with reference: {$reference}");
        }

        return $client;
    }

    /**
     * Update client
     */
    public function updateClient(int $id, array $data, int $schoolId): Client
    {
        $client = $this->findClientById($id, $schoolId);

        V5Logger::info('Updating client', [
            'client_id' => $id,
            'school_id' => $schoolId,
            'fields' => array_keys($data),
        ]);

        // Validate update data
        $this->validateClientUpdateData($data);

        // Check for email/phone duplicates if they're being changed
        if (isset($data['email']) && $data['email'] !== $client->email) {
            $duplicates = $this->duplicateService->findDuplicates(
                $schoolId,
                $data['first_name'] ?? $client->first_name,
                $data['last_name'] ?? $client->last_name,
                $data['email'],
                null,
                $client->id
            );

            if ($duplicates->isNotEmpty()) {
                throw new ClientValidationException(
                    'Email already exists for another client.',
                    ['duplicates' => $duplicates->toArray()]
                );
            }
        }

        try {
            $updatedClient = $this->clientRepository->update($client, $data);

            // Update profile and loyalty tier if relevant data changed
            if (isset($data['total_spent']) || isset($data['total_bookings'])) {
                $updatedClient->updateProfileType();
                $updatedClient->updateLoyaltyTier();
            }

            V5Logger::info('Client updated successfully', [
                'client_id' => $id,
                'school_id' => $schoolId,
            ]);

            return $updatedClient;

        } catch (\Exception $e) {
            V5Logger::error('Failed to update client', [
                'client_id' => $id,
                'school_id' => $schoolId,
                'error' => $e->getMessage(),
            ]);

            throw new ClientValidationException('Failed to update client: ' . $e->getMessage());
        }
    }

    /**
     * Delete client
     */
    public function deleteClient(int $id, int $schoolId, ?string $reason = null): bool
    {
        $client = $this->findClientById($id, $schoolId);

        // Check if client can be deleted
        $activeBookings = $client->activeBookings()->count();
        if ($activeBookings > 0) {
            throw new ClientValidationException(
                "Cannot delete client with {$activeBookings} active bookings. Please cancel or complete bookings first."
            );
        }

        V5Logger::info('Deleting client', [
            'client_id' => $id,
            'school_id' => $schoolId,
            'reason' => $reason,
        ]);

        try {
            $result = $this->clientRepository->delete($client);

            V5Logger::info('Client deleted successfully', [
                'client_id' => $id,
                'school_id' => $schoolId,
            ]);

            return $result;

        } catch (\Exception $e) {
            V5Logger::error('Failed to delete client', [
                'client_id' => $id,
                'school_id' => $schoolId,
                'error' => $e->getMessage(),
            ]);

            throw new ClientValidationException('Failed to delete client: ' . $e->getMessage());
        }
    }

    /**
     * Get clients with filters and pagination
     */
    public function getClients(
        int $schoolId,
        array $filters = [],
        int $page = 1,
        int $limit = 20
    ): LengthAwarePaginator {
        V5Logger::debug('Retrieving clients list', [
            'school_id' => $schoolId,
            'filters' => $filters,
            'page' => $page,
            'limit' => $limit,
        ]);

        return $this->clientRepository->getClients($schoolId, $filters, $page, $limit);
    }

    /**
     * Search clients
     */
    public function searchClients(string $query, int $schoolId, int $limit = 20): Collection
    {
        V5Logger::debug('Searching clients', [
            'school_id' => $schoolId,
            'query' => $query,
            'limit' => $limit,
        ]);

        if (strlen($query) < 2) {
            throw new ClientValidationException('Search query must be at least 2 characters long.');
        }

        return $this->clientRepository->searchClients($query, $schoolId, $limit);
    }

    /**
     * Get client bookings
     */
    public function getClientBookings(
        int $clientId,
        int $schoolId,
        ?int $seasonId = null,
        int $limit = 20
    ): Collection {
        // Verify client exists
        $this->findClientById($clientId, $schoolId);

        V5Logger::debug('Retrieving client bookings', [
            'client_id' => $clientId,
            'school_id' => $schoolId,
            'season_id' => $seasonId,
            'limit' => $limit,
        ]);

        return $this->clientRepository->getClientBookings($clientId, $schoolId, $seasonId, $limit);
    }

    /**
     * Get client statistics
     */
    public function getClientStats(int $schoolId): array
    {
        V5Logger::debug('Retrieving client statistics', [
            'school_id' => $schoolId,
        ]);

        return $this->clientRepository->getClientStats($schoolId);
    }

    /**
     * Get clients requiring attention
     */
    public function getClientsRequiringAttention(int $schoolId): array
    {
        V5Logger::debug('Retrieving clients requiring attention', [
            'school_id' => $schoolId,
        ]);

        return $this->clientRepository->getClientsRequiringAttention($schoolId);
    }

    /**
     * Update client profile and loyalty tier
     */
    public function updateClientProfile(int $clientId, int $schoolId): Client
    {
        $client = $this->findClientById($clientId, $schoolId);

        V5Logger::info('Updating client profile', [
            'client_id' => $clientId,
            'current_profile' => $client->profile_type,
            'current_tier' => $client->loyalty_tier,
        ]);

        // Update booking statistics first
        $client->updateBookingStats();

        // Then update profile and tier
        $client->updateProfileType();
        $client->updateLoyaltyTier();

        V5Logger::info('Client profile updated', [
            'client_id' => $clientId,
            'new_profile' => $client->profile_type,
            'new_tier' => $client->loyalty_tier,
        ]);

        return $client;
    }

    /**
     * Add tag to client
     */
    public function addClientTag(int $clientId, int $schoolId, string $tag): Client
    {
        $client = $this->findClientById($clientId, $schoolId);

        V5Logger::info('Adding tag to client', [
            'client_id' => $clientId,
            'tag' => $tag,
        ]);

        $client->addTag($tag);

        return $client;
    }

    /**
     * Remove tag from client
     */
    public function removeClientTag(int $clientId, int $schoolId, string $tag): Client
    {
        $client = $this->findClientById($clientId, $schoolId);

        V5Logger::info('Removing tag from client', [
            'client_id' => $clientId,
            'tag' => $tag,
        ]);

        $client->removeTag($tag);

        return $client;
    }

    /**
     * Bulk update client profiles
     */
    public function bulkUpdateProfiles(int $schoolId): array
    {
        V5Logger::info('Starting bulk profile update', [
            'school_id' => $schoolId,
        ]);

        $profilesUpdated = $this->clientRepository->bulkUpdateProfiles($schoolId);
        $tiersUpdated = $this->clientRepository->bulkUpdateLoyaltyTiers($schoolId);

        V5Logger::info('Bulk profile update completed', [
            'school_id' => $schoolId,
            'profiles_updated' => $profilesUpdated,
            'tiers_updated' => $tiersUpdated,
        ]);

        return [
            'profiles_updated' => $profilesUpdated,
            'tiers_updated' => $tiersUpdated,
        ];
    }

    /**
     * Get client ranking
     */
    public function getClientRanking(
        int $schoolId,
        string $period = 'all',
        int $limit = 100
    ): Collection {
        V5Logger::debug('Retrieving client ranking', [
            'school_id' => $schoolId,
            'period' => $period,
            'limit' => $limit,
        ]);

        return $this->clientRepository->getClientRanking($schoolId, $period, $limit);
    }

    /**
     * Validate client data
     */
    private function validateClientData(array $data): void
    {
        $required = ['first_name', 'last_name'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new ClientValidationException("Field {$field} is required");
            }
        }

        // Validate email format if provided
        if (isset($data['email']) && !empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new ClientValidationException('Invalid email format');
            }
        }

        // Validate date of birth if provided
        if (isset($data['date_of_birth']) && !empty($data['date_of_birth'])) {
            try {
                $birthDate = \Carbon\Carbon::parse($data['date_of_birth']);
                if ($birthDate->isFuture()) {
                    throw new ClientValidationException('Date of birth cannot be in the future');
                }
                if ($birthDate->diffInYears(now()) > 120) {
                    throw new ClientValidationException('Invalid date of birth');
                }
            } catch (\Exception $e) {
                throw new ClientValidationException('Invalid date of birth format');
            }
        }

        // Validate status if provided
        if (isset($data['status']) && !in_array($data['status'], Client::getValidStatuses())) {
            throw new ClientValidationException('Invalid client status');
        }
    }

    /**
     * Validate client update data
     */
    private function validateClientUpdateData(array $data): void
    {
        // Don't allow changing school_id or client_reference
        unset($data['school_id'], $data['client_reference']);

        // Validate the remaining data using the same rules
        $this->validateClientData($data);
    }
}