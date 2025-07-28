<fieldset class="fieldset w-full">
    @if(isset($label) && $label)
        <legend class="fieldset-legend w-full capitalize">{{ $label }}</legend>
    @endif
    <select
        {{ $attributes->merge(['class' => 'select w-full']) }}>
        <option value="" disabled selected>select {{ $label }}</option>
        {{ $slot }}
    </select>
    @error($name)
        <span class="label w-full text-red-500">{{ $message }}</span>
    @enderror
</fieldset>
