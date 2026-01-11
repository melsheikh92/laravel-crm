<?php

namespace Webkul\Marketplace\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoProfanity implements Rule
{
    /**
     * List of profane words to check against.
     *
     * @var array
     */
    protected $profaneWords = [
        'damn', 'hell', 'crap', 'shit', 'fuck', 'ass', 'bitch', 'bastard',
        'dick', 'cock', 'pussy', 'whore', 'slut', 'fag', 'nigger', 'chink',
        'kike', 'spic', 'wetback', 'retard', 'motherfucker', 'asshole',
    ];

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!is_string($value)) {
            return true;
        }

        // Convert to lowercase for case-insensitive matching
        $lowerValue = strtolower($value);

        // Check for exact word matches using word boundaries
        foreach ($this->profaneWords as $word) {
            // Use word boundaries to match whole words only
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $lowerValue)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute contains inappropriate language. Please keep your review professional and respectful.';
    }
}
