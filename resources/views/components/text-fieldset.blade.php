{{--
    label
    type
    name
--}}

<fieldset class="fieldset w-full">
    @if (!empty($label))
        <legend class="fieldset-legend w-full tracking-wide font-semibold uppercase text-xs text-gray-700">{{ $label }}</legend>
    @endif

    <input type="{{ $type }}" {{ $attributes->merge(['class' => 'input w-full rounded-xl text-gray-500']) }}
        placeholder="Enter {{ $label }}"
        @if ($type === 'date') min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
        @elseif($type === 'datetime-local') min="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}" @endif
        required autofocus />

    @error($name)
        <p class="label w-full truncate text-red-500">{{ $message }}</p>
    @enderror
</fieldset>
