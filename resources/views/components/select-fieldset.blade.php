<fieldset class="fieldset w-full">
    <legend class="fieldset-legend w-full">{{ $label }}</legend>
    <select wire:model="{{ $name }}" id="{{ $name }}" class="select w-full">
        <option value="" disabled>{{ $label }}</option>
        {{ $slot }}
    </select>
    @error($name)
        <span class="label w-full">{{ $message }}</span>
    @enderror
</fieldset>
