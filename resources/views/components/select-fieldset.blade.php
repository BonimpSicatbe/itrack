<fieldset class="fieldset w-full">
    @if(isset($label) && $label)
        <legend class="fieldset-legend w-full uppercase tracking-wide text-gray-700">{{ $label }}</legend>
    @endif
    <select
        {{ $attributes->merge(['class' => 'select w-full rounded-xl text-gray-500']) }}>
        <option value="" disabled selected>Select {{ $label }}</option>
        {{ $slot }}
    </select>
    @error($name)
        <span class="label w-full text-red-500">{{ $message }}</span>
    @enderror
</fieldset>
