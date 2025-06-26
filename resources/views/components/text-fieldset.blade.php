{{--
    label
    type
    name
--}}

<fieldset class="fieldset w-full">
    <legend class="fieldset-legend w-full capitalize">{{ $label }}</legend>

    <input
        type="{{ $type }}"
        {{ $attributes->merge(['class' => 'input w-full']) }}
        placeholder="Enter {{ $label }}"
        @if($type === 'date') min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" @endif
        required
        autofocus
    />

    @error($name)
        <p class="label w-full truncate text-red-500">{{ $message }}</p>
    @enderror
</fieldset>
