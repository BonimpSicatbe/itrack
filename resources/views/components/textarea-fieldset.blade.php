<fieldset class="fieldset w-full">
    <legend class="fieldset-legend w-full capitalize">{{ $label }}</legend>
    <textarea {{ $attributes->merge(['class' => 'textarea w-full']) }} placeholder="Enter {{ $label }}" required
        autofocus></textarea>
    @error($name)
        <p class="label w-full truncate">{{ $message }}</p>
    @enderror
</fieldset>
