<?php

declare(strict_types=1);

namespace Crmleaf\Payroll\Tools\ComplianceCalendar\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the wire input for Compliance Calendar and turns it into named arguments
 * for Crmleaf\Payroll\Calculators\ComplianceCalendar::forFinancialYear().
 *
 * Optional fields that were not sent are left out of the payload entirely
 * rather than passed as null, so the calculator's own documented defaults apply
 * and there is exactly one place each default is written down.
 */
final class ComplianceCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        if (!$this->submitted()) {
            return [];
        }

        return [
            'financial_year' => ['required', 'string', 'regex:/^\\d{4}-\\d{2}$/'],
            'only' => ['nullable', 'array'],
            'qrmp' => ['nullable', 'boolean'],
            'as_of' => ['nullable', 'date'],
        ];
    }

    /**
     * Named arguments for ComplianceCalendar::forFinancialYear().
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        /** @var array<string, mixed> $input */
        $input = $this->validated();

        $payload = [
            'financialYear' => (string) $input['financial_year'],
        ];

        if (array_key_exists('only', $input) && $input['only'] !== null) {
            $payload['only'] = (array) $input['only'];
        }

        if (array_key_exists('qrmp', $input) && $input['qrmp'] !== null) {
            $payload['qrmp'] = (bool) $input['qrmp'];
        }

        if (array_key_exists('as_of', $input) && $input['as_of'] !== null) {
            $payload['asOf'] = new \DateTimeImmutable((string) $input['as_of']);
        }

        return $payload;
    }

    /**
     * A bare GET renders an empty form; everything else is a submission.
     */
    public function submitted(): bool
    {
        return $this->isMethod('post') || $this->expectsJson() || $this->query->count() > 0;
    }

    /**
     * The HTML form posts these as JSON text in a textarea, the JSON API sends
     * them as real arrays, and both have to reach the same validator.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'only' => self::decodeJson($this->input('only')),
        ]);
    }

    private static function decodeJson(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $decoded = json_decode($trimmed, true);

        return is_array($decoded) ? $decoded : $value;
    }
}
