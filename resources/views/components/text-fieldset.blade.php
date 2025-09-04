{{--
    label
    type
    name
--}}

<fieldset class="fieldset w-full">
    @if (!empty($label))
        <legend class="fieldset-legend w-full capitalize">{{ $label }}</legend>
    @endif

    <input type="{{ $type }}" {{ $attributes->merge(['class' => 'input w-full rounded-xl']) }}
        placeholder="Enter {{ $label }}"
        @if ($type === 'date') min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
        @elseif($type === 'datetime-local') min="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}" @endif
        required autofocus />

    @error($name)
        <p class="label w-full truncate text-red-500">{{ $message }}</p>
    @enderror
</fieldset>
