<fieldset class="fieldset w-full">
    <legend class="fieldset-legend w-full capitalize">{{ $label }}</legend>
    <textarea {{ $attributes->merge(['class' => 'textarea w-full rounded-xl']) }} placeholder="Enter {{ $label }}" required
        autofocus></textarea>
    @error($name)
        <p class="label w-full truncate text-red-500">{{ $message }}</p>
    @enderror
</fieldset>
