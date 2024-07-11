<?php declare(strict_types=1);

namespace Niebvelungen\RestBundleDoctrine\Metadata\Common;

enum CrudOperation: string
{
    case LIST = 'LIST';
    case CREATE = 'CREATE';
    case READ = 'READ';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';

    /** @return list<CrudOperation> */
    public static function all(): array
    {
        return [...self::allRead(), ...self::allWrite()];
    }

    /** @return list<CrudOperation> */
    public static function allRead(): array
    {
        return [self::LIST, self::READ];
    }

    /** @return list<CrudOperation> */
    public static function allWrite(): array
    {
        return [self::CREATE, self::UPDATE, self::DELETE];
    }
}