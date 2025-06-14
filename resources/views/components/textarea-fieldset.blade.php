<fieldset class="fieldset w-full">
    <legend class="fieldset-legend w-full capitalize">{{ $label }}</legend>
    <textarea wire:model="{{ $name }}" id="{{ $name }}" class="textarea w-full lowercase"
        placeholder="Enter {{ $label }}" value="{{ $value ?? old($name) }}" required autofocus
        autocomplete="{{ $name }}"></textarea>
    @error($name)
        <p class="label w-full truncate">{{ $message }}</p>
    @enderror
</fieldset>
