<?php
/**
 * error_codes.inc.php
 * 
 * This file is where all error codes are defined.
 * All error code are named after the class and function they occur in.
 */

/**
 * Error number category defines.
 */
define( 'ARGUMENT_COSMOS_BASE_ERRNO',   120000 );
define( 'DATABASE_COSMOS_BASE_ERRNO',   220000 );
define( 'LDAP_COSMOS_BASE_ERRNO',       320000 );
define( 'NOTICE_COSMOS_BASE_ERRNO',     420000 );
define( 'PERMISSION_COSMOS_BASE_ERRNO', 520000 );
define( 'RUNTIME_COSMOS_BASE_ERRNO',    620000 );
define( 'SYSTEM_COSMOS_BASE_ERRNO',     720000 );

/**
 * "argument" error codes
 */

/**
 * "database" error codes
 * 
 * Since database errors already have codes this list is likely to stay empty.
 */

/**
 * "ldap" error codes
 * 
 * Since ldap errors already have codes this list is likely to stay empty.
 */

/**
 * "notice" error codes
 */

/**
 * "permission" error codes
 */

/**
 * "runtime" error codes
 */
define( 'RUNTIME__COSMOS_DATABASE_INTERVIEW__UPDATE_INTERVIEW_LIST__ERRNO',
        RUNTIME_COSMOS_BASE_ERRNO + 1 );

/**
 * "system" error codes
 * 
 * Since system errors already have codes this list is likely to stay empty.
 * Note the following PHP error codes:
 *      1: error,
 *      2: warning,
 *      4: parse,
 *      8: notice,
 *     16: core error,
 *     32: core warning,
 *     64: compile error,
 *    128: compile warning,
 *    256: user error,
 *    512: user warning,
 *   1024: user notice
 */

