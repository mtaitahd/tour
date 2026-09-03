<?php

namespace App\Http\Requests\Admin;

use App\Pricing\PricingValidationException;
use App\Services\PricingService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Server-side price calculator preview. The raw calculator payload is validated
 * through the full PricingInput pipeline; human-readable PricingValidationException
 * messages are surfaced as a form error on the `calculator_payload` field so the
 * calculator panel can display them inline. The resolved PricingInput is stashed
 * on the validated request for the controller.
 */
class TourPriceCalculatorPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isSuperAdmin();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'calculator_payload' => ['required', 'array'],
        ];
    }

    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Contracts\Validation\Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $payload = $this->input('calculator_payload');

            if (! is_array($payload)) {
                $validator->errors()->add('calculator_payload', 'Calculator payload must be an object.');

                return;
            }

            try {
                (new PricingService())->fromRaw($payload);
            } catch (PricingValidationException $e) {
                $validator->errors()->add('calculator_payload', $e->getMessage());
            }
        });
    }

    /** @return array<string, mixed> */
    public function validatedPayload(): array
    {
        return $this->validated()['calculator_payload'];
    }
}