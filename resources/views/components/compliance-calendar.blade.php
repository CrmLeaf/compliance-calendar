@props([
    'action' => null,
    'method' => 'post',
    'defaults' => [],
    'input' => [],
    'result' => null,
    'error' => null,
    'heading' => 'Compliance Calendar',
    'tagline' => 'Month-by-month due dates for PF, ESI, TDS, GST, PT and Form 16.',
    'showWorking' => true,
])

<section class="crmleaf-tool crmleaf-tool--compliance-calendar" data-crmleaf-tool="compliance-calendar">
    <header class="crmleaf-tool__header">
        <h2 class="crmleaf-tool__heading">{{ $heading }}</h2>
        <p class="crmleaf-tool__tagline">{{ $tagline }}</p>
    </header>

    @if ($error)
        <p class="crmleaf-tool__error" role="alert">{{ $error }}</p>
    @endif

    <form class="crmleaf-tool__form"
          method="{{ strtolower($method) === 'get' ? 'get' : 'post' }}"
          action="{{ $action }}"
          data-crmleaf-form>
        @if (strtolower($method) !== 'get')
            @csrf
        @endif

        <label class="crmleaf-field">
            <span>Financial year</span>
            <input type="text" name="financial_year" value="{{ old('financial_year', $input['financial_year'] ?? ($defaults['financial_year'] ?? '')) }}" required>
            <small>Written as 2025-26, not 2025-2026.</small>
        </label>

        <label class="crmleaf-field">
            <span>Obligations</span>
            <textarea name="only" rows="4" spellcheck="false">{{ old('only', is_array($input['only'] ?? null) ? json_encode($input['only']) : ($defaults['only'] ?? '')) }}</textarea>
            <small>Codes to keep, for example PF_ECR or TDS_PAY. Leave empty for the full calendar.</small>
        </label>

        <label class="crmleaf-field crmleaf-field--bool">
            <input type="hidden" name="qrmp" value="0">
            <input type="checkbox" name="qrmp" value="1" @checked(old('qrmp', $input['qrmp'] ?? ($defaults['qrmp'] ?? false)))>
            <span>QRMP filer</span>
            <small>Swaps the monthly GST obligations for their quarterly variants.</small>
        </label>

        <label class="crmleaf-field">
            <span>Rules as on</span>
            <input type="date" name="as_of" value="{{ old('as_of', $input['as_of'] ?? ($defaults['as_of'] ?? '')) }}">
        </label>

        <input type="hidden" name="tool" value="compliance-calendar">

        <div class="crmleaf-tool__actions">
            <button type="submit" class="crmleaf-tool__submit">Calculate</button>
        </div>
    </form>

    {{-- The client-side path writes its answer here; the server-side path fills it below. --}}
    <div class="crmleaf-tool__output" data-crmleaf-output hidden></div>

    @if ($result)
        <div class="crmleaf-tool__result">
            <p class="crmleaf-tool__explain"><code>{{ $result->explain() }}</code></p>

            <table class="crmleaf-tool__figures">
                <tbody>
                @foreach ($result->toArray() as $key => $value)
                    @continue(is_array($value) || str_ends_with((string) $key, '_formatted'))
                    <tr>
                        <th scope="row">{{ ucfirst(str_replace('_', ' ', (string) $key)) }}</th>
                        <td>{{ $result->toArray()[$key.'_formatted'] ?? (is_bool($value) ? ($value ? 'Yes' : 'No') : $value) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            @if ($showWorking && count($result->steps()))
                <details class="crmleaf-tool__working" open>
                    <summary>How this was worked out</summary>
                    <ol>
                        @foreach ($result->steps() as $step)
                            <li>
                                <span class="crmleaf-step__label">{{ $step->label }}</span>
                                @if ($step->amount)
                                    <span class="crmleaf-step__amount">{{ $step->amount->format() }}</span>
                                @endif
                                @if ($step->formula)
                                    <code class="crmleaf-step__formula">{{ $step->formula }}</code>
                                @endif
                                @if ($step->citation)
                                    <small class="crmleaf-step__citation">{{ $step->citation }}</small>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </details>
            @endif

            @if (count($result->citations()))
                <ul class="crmleaf-tool__citations">
                    @foreach ($result->citations() as $citation)
                        <li>{{ $citation }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</section>
