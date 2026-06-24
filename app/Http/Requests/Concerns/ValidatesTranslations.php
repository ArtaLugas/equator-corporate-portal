<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Contracts\Validation\Validator;

/**
 * Shared mechanics for per-locale (i18n) validation on admin FormRequests.
 *
 * The using request declares ONLY its own field spec via translatableSpecs()
 * (base field => max length, null = unbounded text/longText). This trait owns
 * the module-agnostic behaviour, identical across modules:
 *
 *   - translatableRules(): per-locale rules — the default-locale anchor is
 *     required, every other locale column is nullable, each with its max length.
 *   - withValidator(): ALL-OR-NOTHING enforcement for non-default locales. Once
 *     a locale is started, every field present in the source must be translated;
 *     each missing field is marked and one locale-level summary is added under
 *     "translation_{locale}".
 *   - attributes(): friendly "<field> (<LOCALE>)" labels for messages.
 *
 * Behaviour is intentionally identical to the previous inline implementations in
 * ServiceRequest / ProjectRequest — this trait only removes the duplication.
 */
trait ValidatesTranslations
{
    /** Translatable base field => max length (null = unbounded text/longText). */
    abstract protected function translatableSpecs(): array;

    /**
     * Fields required in the default locale. Defaults to the anchor (the first
     * declared field, e.g. name/title); a request may override if it needs more.
     */
    protected function requiredInDefaultLocale(): array
    {
        return [array_key_first($this->translatableSpecs())];
    }

    /**
     * Per-locale field rules. Default locale gets the required anchor(s); every
     * locale column is otherwise nullable with its max length.
     */
    protected function translatableRules(): array
    {
        $default = config('locales.default');
        $locales = array_keys(config('locales.supported', []));
        $required = $this->requiredInDefaultLocale();
        $rules = [];

        foreach ($this->translatableSpecs() as $field => $max) {
            foreach ($locales as $locale) {
                $rule = $locale === $default && in_array($field, $required, true)
                    ? ['required', 'string']
                    : ['nullable', 'string'];

                if ($max !== null) {
                    $rule[] = "max:{$max}";
                }

                $rules["{$field}_{$locale}"] = $rule;
            }
        }

        return $rules;
    }

    /**
     * All-or-nothing enforcement for non-default locales.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $default = config('locales.default');
            $fields = array_keys($this->translatableSpecs());

            foreach (array_keys(config('locales.supported', [])) as $locale) {
                if ($locale === $default) {
                    continue;
                }

                // Has the editor begun translating this locale at all?
                $started = collect($fields)->contains(
                    fn ($field) => filled($this->input("{$field}_{$locale}"))
                );

                if (! $started) {
                    continue; // locale left empty → perfectly valid
                }

                // Started → every field that exists in the source must be translated.
                // Mark each missing field (for highlighting) and collect the count.
                $missing = 0;

                foreach ($fields as $field) {
                    if (filled($this->input("{$field}_{$default}")) && blank($this->input("{$field}_{$locale}"))) {
                        $missing++;
                        $validator->errors()->add(
                            "{$field}_{$locale}",
                            'Required to complete the '.strtoupper($locale).' translation.'
                        );
                    }
                }

                // One clear, locale-level summary message shown above the tabs.
                if ($missing > 0) {
                    $name = config("locales.supported.{$locale}.name", strtoupper($locale));

                    $validator->errors()->add(
                        "translation_{$locale}",
                        "{$name} (".strtoupper($locale).') has been started, so all '.strtoupper($locale).
                        " fields must be completed before saving — {$missing} highlighted field(s) still empty."
                    );
                }
            }
        });
    }

    /**
     * Friendly attribute labels: "<field with spaces> (<LOCALE>)".
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach (array_keys($this->translatableSpecs()) as $field) {
            $label = str_replace('_', ' ', $field);

            foreach (array_keys(config('locales.supported', [])) as $locale) {
                $attributes["{$field}_{$locale}"] = "{$label} (".strtoupper($locale).')';
            }
        }

        return $attributes;
    }
}
