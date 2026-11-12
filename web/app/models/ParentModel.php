<?php
/**
 * Parent (guardian) model.
 */
class ParentModel extends Model
{
    protected string $table = 'parents';

    /**
     * Create a parent record. The official ID (PAR-0001...) is generated
     * automatically unless one was explicitly provided.
     */
    public function create(array $data): int
    {
        if (!isset($data['official_id']) || $data['official_id'] === '') {
            $data['official_id'] = OfficialId::next('PAR');
        }
        return parent::create($data);
    }

    /**
     * Find a parent linked to a user account.
     */
    public function findByUserId(int $userId): ?array
    {
        return $this->firstWhere('user_id', $userId);
    }
}
