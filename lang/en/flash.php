<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin flash error messages (cause + how to resolve)
    |--------------------------------------------------------------------------
    | Used by the friendly_error() helper (exception-mapped) and by explicit
    | guards in the admin controllers. Each message tells the admin WHY it
    | failed and WHAT to do next.
    */

    // friendly_error() — mapped from the caught database/runtime exception
    'error_fk' => 'This cannot be completed because the record is still linked to other data. Remove or reassign the related records first, then try again.',
    'error_duplicate' => 'A record with the same value (such as name or slug) already exists. Please use a different value and try again.',
    'error_too_long' => 'One of the values is too long for its field. Please shorten it and try again.',
    'error_db' => 'A database error occurred while processing your request. Please try again — if the problem persists, contact the administrator.',
    'error_generic' => 'Something went wrong while processing your request. Please try again — if the problem persists, contact the administrator.',

    // explicit guards
    'none_selected' => 'No items are selected. Please tick at least one row first, then try again.',
    'in_use' => 'This cannot be deleted because it is still used by other records (including any in Trash). Remove or permanently delete those related records first, then try again.',
    'last_super_admin' => 'This is the last Super Admin account, so it cannot be deleted. Promote another admin to Super Admin first, then try again.',

];
