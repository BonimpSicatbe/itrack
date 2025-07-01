<fieldset class="fieldset w-full">
    <legend class="fieldset-legend w-full">{{ $label }}</legend>
    <select
        {{ $attributes->merge(['class' => 'select w-full']) }}>
        <option value="" disabled selected>{{ $label }}</option>
        {{ $slot }}
    </select>
    @error($name)
        <span class="label w-full text-red-500">{{ $message }}</span>
    @enderror
</fieldset>
