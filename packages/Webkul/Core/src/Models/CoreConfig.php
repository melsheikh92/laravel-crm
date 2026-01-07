<?php

namespace Webkul\Core\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Webkul\Core\Contracts\CoreConfig as CoreConfigContract;

class CoreConfig extends Model implements CoreConfigContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'core_config';

    protected $fillable = [
        'code',
        'value',
        'locale',
        'encrypted',
    ];

    protected $hidden = ['token'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'encrypted' => 'boolean',
    ];

    /**
     * Get the value attribute with decryption if needed.
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (! $this->encrypted || empty($value) || ! is_string($value)) {
                    return $value;
                }

                try {
                    return Crypt::decryptString($value);
                } catch (\Exception $e) {
                    return $value;
                }
            },
        );
    }
}
