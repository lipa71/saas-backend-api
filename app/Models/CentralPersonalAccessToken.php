<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class CentralPersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'mysql';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    // We explicitly tell Laravel to look for the default sanctum table name
    protected $table = 'personal_access_tokens';
}
