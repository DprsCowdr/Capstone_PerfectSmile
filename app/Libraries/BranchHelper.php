<?php
namespace App\Libraries;

class BranchHelper
{
    public static function formatAddress(array $branch)
    {
        $parts = [];
        if (!empty($branch['address'])) $parts[] = $branch['address'];
        if (!empty($branch['contact_number'])) $parts[] = 'Tel: ' . $branch['contact_number'];
        if (!empty($branch['contact_email'])) $parts[] = $branch['contact_email'];
        return implode(' | ', $parts);
    }
}
