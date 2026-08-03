<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    /**
     * Atributos que pueden ser asignados masivamente.
     */
    protected $fillable = [
        'user_id',
        'key',
        'value'
    ];

    /**
     * Obtener un valor de configuración para un usuario.
     *
     * @param int $userId
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($userId, $key, $default = null)
    {
        $setting = self::where('user_id', $userId)
                     ->where('key', $key)
                     ->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Guardar o actualizar un valor de configuración para un usuario.
     *
     * @param int $userId
     * @param string $key
     * @param mixed $value
     * @return \App\Models\UserSetting
     */
    public static function set($userId, $key, $value)
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'key' => $key
            ],
            [
                'value' => $value
            ]
        );
    }
}