<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchNotificationModel extends Model
{
    protected $table = 'branch_notifications';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['branch_id','appointment_id','payload','sent','sent_at','created_at','updated_at'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // We'll normalize payload ourselves to avoid relying on the framework JsonCast
    // (which can return scalars and cause strict return-type errors). Keep 'sent'
    // casted to boolean for convenience.
    protected array $casts = [
        'sent' => 'boolean',
    ];

    // Normalize payload after fetch so application code always receives an array
    // (or empty array) instead of a scalar/string which can break callers or
    // the framework strict typing.
    protected $afterFind = ['normalizePayload'];

    /**
     * Model callback to safely decode JSON-like payloads and ensure a consistent
     * PHP array shape is returned to callers. This avoids touching vendor code.
     *
     * @param array $data
     * @return array
     */
    protected function normalizePayload(array $data): array
    {
        // $data['data'] may be a single row or array of rows depending on call
        if (empty($data['data'])) {
            return $data;
        }

        $rows =& $data['data'];

        // When single row, CI provides associative array; when multiple, numeric index array
        if (array_keys($rows) !== range(0, count($rows) - 1)) {
            // single row
            $rows = [$rows];
            $wasSingle = true;
        } else {
            $wasSingle = false;
        }

        foreach ($rows as &$row) {
            if (!isset($row['payload'])) continue;

            $payload = $row['payload'];

            // If already an array/iterable, leave as-is
            if (is_array($payload)) {
                continue;
            }

            // If null/empty, ensure empty array
            if ($payload === null || $payload === '') {
                $row['payload'] = [];
                continue;
            }

            // If it's a string, attempt json decode; if decoding fails, wrap scalar
            if (is_string($payload)) {
                try {
                    $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    // Not valid JSON; return it as a wrapped value so callers have a consistent shape
                    $row['payload'] = ['value' => $payload];
                    continue;
                }

                if (is_array($decoded)) {
                    $row['payload'] = $decoded;
                } elseif (is_scalar($decoded) || $decoded === null) {
                    // JSON valid but decoded to scalar (e.g. "some string" or 123)
                    $row['payload'] = ['value' => $decoded];
                } else {
                    // Fallback: ensure array
                    $row['payload'] = [];
                }
            }
        }
        unset($row);

        if ($wasSingle) {
            $data['data'] = $rows[0];
        } else {
            $data['data'] = $rows;
        }

        return $data;
    }
}
