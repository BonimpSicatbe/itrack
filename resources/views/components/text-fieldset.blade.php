{{--
    label
    type
    name
--}}

<fieldset class="fieldset w-full">
    @if (!empty($label))
        <legend class="fieldset-legend w-full tracking-wide font-semibold uppercase text-xs text-gray-700">
            {{ $label }}
        </legend>
    @endif

    <input
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name) }}"
        {{ $attributes->merge(['class' => 'input w-full rounded-xl text-gray-500']) }}
        placeholder="Enter {{ $label }}"
    />

    @error($name)
        <p class="label w-full truncate text-red-500">{{ $message }}</p>
    @enderror
</fieldset>

